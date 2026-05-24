import prisma from '$lib/prisma';
import { error } from '@sveltejs/kit';
import PDFDocument from 'pdfkit';

export async function GET({ params, locals }) {
	if (!locals.user) {
		throw error(401, 'Authentication required');
	}

	const bookingId = parseInt(params.id);
	if (isNaN(bookingId)) {
		throw error(400, 'Invalid booking ID');
	}

	const booking = await prisma.booking.findUnique({
		where: {
			id: bookingId,
			userId: locals.user.id
		},
		include: {
			flight: {
				include: {
					departure: true,
					arrival: true
				}
			},
			passengers: true,
			user: true
		}
	});

	if (!booking) {
		throw error(404, 'Booking not found');
	}

	const doc = new PDFDocument({ margin: 50 });
	const buffers: Buffer[] = [];
	doc.on('data', buffers.push.bind(buffers));

	// --- PDF Content ---
	doc.fontSize(25).text('Sky King Boarding Pass', { align: 'center' });
	doc.moveDown();

	doc.fontSize(18).text(`${booking.flight.departure.city} to ${booking.flight.arrival.city}`);
	doc.fontSize(14).text(
		`Flight: ${booking.flight.departure.code} -> ${booking.flight.arrival.code}`
	);
	doc.moveDown();

	const departureTime = new Date(booking.flight.departureTime).toLocaleString();
	const arrivalTime = new Date(booking.flight.arrivalTime).toLocaleString();
	doc.fontSize(12).text(`Departure: ${departureTime}`);
	doc.fontSize(12).text(`Arrival: ${arrivalTime}`);
	doc.moveDown();

	doc.fontSize(16).text('Passengers');
	booking.passengers.forEach((passenger) => {
		doc.fontSize(12).text(`- ${passenger.name} (Seat: ${passenger.seat})`);
	});
	doc.moveDown();

	doc.fontSize(14).text(`Booked by: ${booking.user.name} (${booking.user.email})`);
	doc.fontSize(10).text(`Booking ID: ${booking.id}`);
	doc.fontSize(10).text(
		`Total Price: $${booking.flight.price.toFixed(2)}`
	);
	// --- End PDF Content ---

	return new Promise((resolve) => {
		doc.on('end', () => {
			const pdfData = Buffer.concat(buffers);
			resolve(
				new Response(pdfData, {
					headers: {
						'Content-Type': 'application/pdf',
						'Content-Disposition': `attachment; filename="ticket-${booking.id}.pdf"`
					}
				})
			);
		});
		doc.end();
	});
}
