import prisma from '$lib/prisma';

export async function load({ locals }) {
	const flights = await prisma.flight.findMany({
		include: {
			departure: true,
			arrival: true
		},
		orderBy: {
			departureTime: 'asc'
		},
		take: 6
	});

	const bookings = locals.user
		? await prisma.booking.findMany({
				where: {
					userId: locals.user.id
				},
				include: {
					flight: {
						include: {
							departure: true,
							arrival: true
						}
					},
					passengers: true
				},
				orderBy: {
					createdAt: 'desc'
				}
			})
		: [];

	let recommendations = [];
	if (bookings.length > 0) {
		const arrivalCounts = bookings.reduce((acc, booking) => {
			const code = booking.flight.arrivalAirportId;
			acc[code] = (acc[code] || 0) + 1;
			return acc;
		}, {} as Record<number, number>);

		const sortedArrivals = Object.entries(arrivalCounts).sort((a, b) => b[1] - a[1]);
		const favoriteArrivalId = parseInt(sortedArrivals[0][0]);

		const bookedFlightIds = new Set(bookings.map((booking) => booking.flightId));

		recommendations = await prisma.flight.findMany({
			where: {
				arrivalAirportId: favoriteArrivalId,
				id: {
					notIn: Array.from(bookedFlightIds)
				}
			},
			include: {
				departure: true,
				arrival: true
			},
			take: 3
		});
	}

	const totalSpend = bookings.reduce((sum, booking) => sum + booking.flight.price, 0);
	const destinationCount = new Set(bookings.map((booking) => booking.flight.arrival.code)).size;

	return {
		user: locals.user,
		flights: flights.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		})),
		bookings: bookings.map((booking) => ({
			...booking,
			createdAt: booking.createdAt.toISOString(),
			flight: {
				...booking.flight,
				departureTime: booking.flight.departureTime.toISOString(),
				arrivalTime: booking.flight.arrivalTime.toISOString()
			}
		})),
		recommendations: recommendations.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		})),
		stats: {
			totalSpend,
			bookingCount: bookings.length,
			destinationCount
		}
	};
}
