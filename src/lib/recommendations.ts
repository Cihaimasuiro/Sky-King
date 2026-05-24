import prisma from './prisma';
import type { Flight, Airport } from '@prisma/client';

type FlightWithRelations = Flight & {
	departure: Airport;
	arrival: Airport;
};

export async function getRecommendations(userId: number) {
	// Get user's past booking destinations
	const userBookings = await prisma.booking.findMany({
		where: { userId },
		select: {
			flight: {
				select: {
					arrival: {
						select: {
							id: true
						}
					}
				}
			}
		},
		distinct: ['flightId']
	});

	const pastArrivalAirportIds = userBookings.map((b) => b.flight.arrival.id);

	let recommendedFlights: (FlightWithRelations & { reason: string })[] = [];

	if (pastArrivalAirportIds.length > 0) {
		// Find flights departing from past arrival airports
		const historyBasedFlights = await prisma.flight.findMany({
			where: {
				departureId: {
					in: pastArrivalAirportIds
				},
				departureTime: {
					gt: new Date() // Only future flights
				}
			},
			include: {
				departure: true,
				arrival: true
			},
			take: 3, // Limit to 3 recommendations
			orderBy: {
				departureTime: 'asc'
			}
		});

		// Add a reason for recommendation
		recommendedFlights = historyBasedFlights.map((flight) => ({
			...flight,
			reason: `Because you've flown to ${flight.departure.city} before`
		}));
	}

	// If no history-based recommendations or not enough, fill with popular flights
	if (recommendedFlights.length < 3) {
		const popularFlights = await prisma.flight.findMany({
			where: {
				id: {
					notIn: recommendedFlights.map((f) => f.id) // Exclude already recommended flights
				},
				departureTime: {
					gt: new Date() // Only future flights
				}
			},
			include: {
				departure: true,
				arrival: true
			},
			take: 3 - recommendedFlights.length,
			orderBy: {
				// This is a placeholder for "popularity" - in a real app, this would be based on booking counts
				price: 'asc' // Example: cheaper flights are "popular"
			}
		});

		recommendedFlights = [
			...recommendedFlights,
			...popularFlights.map((flight) => ({
				...flight,
				reason: 'Popular choice'
			}))
		];
	}

	return recommendedFlights.map((flight) => ({
		...flight,
		departureTime: flight.departureTime.toISOString(),
		arrivalTime: flight.arrivalTime.toISOString()
	}));
}
