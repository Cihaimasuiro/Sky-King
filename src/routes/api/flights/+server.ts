import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';

export async function GET() {
	const flights = await prisma.flight.findMany({
		include: {
			departure: true,
			arrival: true
		},
		orderBy: {
			departureTime: 'asc'
		}
	});

	return json({
		flights: flights.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		}))
	});
}
