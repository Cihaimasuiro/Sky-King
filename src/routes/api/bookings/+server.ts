import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';

function seatFor(index: number) {
	const row = Math.floor(index / 6) + 12;
	const column = ['A', 'B', 'C', 'D', 'E', 'F'][index % 6];
	return `${row}${column}`;
}

export async function GET({ locals }) {
	if (!locals.user) {
		return json({ error: 'Authentication required', code: 401 }, { status: 401 });
	}

	const bookings = await prisma.booking.findMany({
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
	});

	return json({
		bookings: bookings.map((booking) => ({
			...booking,
			createdAt: booking.createdAt.toISOString(),
			flight: {
				...booking.flight,
				departureTime: booking.flight.departureTime.toISOString(),
				arrivalTime: booking.flight.arrivalTime.toISOString()
			}
		}))
	});
}

export async function POST({ request, locals }) {
	if (!locals.user) {
		return json({ error: 'Authentication required', code: 401 }, { status: 401 });
	}

	const { flightId, passengerNames } = await request.json();
	const names = Array.isArray(passengerNames)
		? passengerNames.map((name) => String(name).trim()).filter(Boolean)
		: [];

	if (!Number.isInteger(flightId) || names.length === 0) {
		return json(
			{ error: 'Select a flight and add at least one passenger', code: 400 },
			{ status: 400 }
		);
	}

	const flight = await prisma.flight.findUnique({
		where: {
			id: flightId
		}
	});

	if (!flight) {
		return json({ error: 'Flight not found', code: 404 }, { status: 404 });
	}

	const booking = await prisma.booking.create({
		data: {
			userId: locals.user.id,
			flightId,
			passengers: {
				create: names.map((name, index) => ({
					name,
					seat: seatFor(index)
				}))
			}
		},
		include: {
			flight: {
				include: {
					departure: true,
					arrival: true
				}
			},
			passengers: true
		}
	});

	return json(
		{
			message: 'Booking created',
			booking: {
				...booking,
				createdAt: booking.createdAt.toISOString(),
				flight: {
					...booking.flight,
					departureTime: booking.flight.departureTime.toISOString(),
					arrivalTime: booking.flight.arrivalTime.toISOString()
				}
			}
		},
		{ status: 201 }
	);
}
