import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';

export async function GET({ locals }) {
	if (!locals.user) {
		return json({ message: 'Authentication required' }, { status: 401 });
	}

	const bookings = await prisma.booking.findMany({
		where: {
			userId: locals.user.id
		},
		include: {
			flight: true
		}
	});

	if (bookings.length === 0) {
		return json({ recommendations: [] });
	}

	const arrivalCounts = bookings.reduce((acc, booking) => {
		const code = booking.flight.arrivalAirportId;
		acc[code] = (acc[code] || 0) + 1;
		return acc;
	}, {} as Record<number, number>);

	const sortedArrivals = Object.entries(arrivalCounts).sort((a, b) => b[1] - a[1]);
	const favoriteArrivalId = parseInt(sortedArrivals[0][0]);

	const bookedFlightIds = new Set(bookings.map((booking) => booking.flightId));

	const recommendations = await prisma.flight.findMany({
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

	return json({
		recommendations: recommendations.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		}))
	});
}
