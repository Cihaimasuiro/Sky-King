import { error } from '@sveltejs/kit';
import prisma from '$lib/prisma';

export async function load({ params }) {
	const flight = await prisma.flight.findUnique({
		where: { id: Number(params.id) },
		include: {
			departure: true,
			arrival: true
		}
	});

	if (!flight) {
		throw error(404, 'Flight not found');
	}

	return {
		flight: {
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		}
	};
}
