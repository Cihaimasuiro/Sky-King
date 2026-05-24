import prisma from '$lib/prisma';
import { getRecommendations } from '$lib/recommendations';

export async function load({ locals }) {
	let recommendations: Awaited<ReturnType<typeof getRecommendations>> = [];
	if (locals.user) {
		recommendations = await getRecommendations(locals.user.id);
	}

	const flights = await prisma.flight.findMany({
		include: {
			departure: true,
			arrival: true
		},
		orderBy: {
			departureTime: 'asc'
		}
	});

	return {
		flights: flights.map((flight) => ({
			...flight,
			departureTime: flight.departureTime.toISOString(),
			arrivalTime: flight.arrivalTime.toISOString()
		})),
		recommendations: recommendations
	};
}
