import prisma from '$lib/prisma';
import { getRecommendations } from '$lib/recommendations';

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

	let recommendations: Awaited<ReturnType<typeof getRecommendations>> = [];
	if (locals.user) {
		recommendations = await getRecommendations(locals.user.id);
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
		recommendations: recommendations,
		stats: {
			totalSpend,
			bookingCount: bookings.length,
			destinationCount
		}
	};
}
