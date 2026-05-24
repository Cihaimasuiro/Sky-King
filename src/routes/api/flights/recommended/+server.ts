import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';

export async function GET({ locals }) {
	if (!locals.user) {
		return json({ error: 'Authentication required', code: 401 }, { status: 401 });
	}

	const bookings = await prisma.booking.findMany({
		where: {
			userId: locals.user.id
		},
		include: {
			flight: true
		}
	});

	let recommendations = [];

	if (bookings.length > 0) {
		const destinationCounts = bookings.reduce((acc, booking) => {
			const city = booking.flight.arrivalAirportId;
			acc[city] = (acc[city] || 0) + 1;
			return acc;
		}, {} as Record<number, number>);

		const sortedDestinations = Object.entries(destinationCounts).sort((a, b) => b[1] - a[1]);
		const favoriteDestinationId = parseInt(sortedDestinations[0][0]);

		const onwardFlights = await prisma.flight.findMany({
			where: {
				departureAirportId: favoriteDestinationId,
				id: {
					notIn: bookings.map((b) => b.flightId)
				}
			},
			include: {
				departure: true,
				arrival: true
			},
			take: 4
		});

		recommendations = onwardFlights.map((flight) => ({
			...flight,
			reason: `You've flown to ${flight.departure.city} before.`
		}));
	}

	if (recommendations.length < 4) {
		const popularFlights = await prisma.flight.findMany({
			orderBy: {
				bookings: {
					_count: 'desc'
				}
			},
			include: {
				departure: true,
				arrival: true
			},
			take: 4 - recommendations.length
		});

		const popularRecs = popularFlights
			.filter((flight) => !recommendations.some((r) => r.id === flight.id))
			.map((flight) => ({
				...flight,
				reason: 'This is a popular flight.'
			}));

		recommendations.push(...popularRecs);
	}

	return json({
		recommendations: recommendations.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		}))
	});
}
