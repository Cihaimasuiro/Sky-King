/**
 * @module routes/api/bookings/[id]/ticket.pdf/+server
 * @description This module provides an API endpoint to generate and stream a PDF boarding pass/receipt for a specific booking.
 * It uses pdfkit to create the PDF document with booking details.
 */
import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit'; // Import json to return consistent error shape
import PDFDocument from 'pdfkit';

/**
 * Handles GET requests to generate a PDF ticket for a booking.
 * @param {object} params - The request parameters, containing the booking ID.
 * @param {object} locals - The SvelteKit locals object, containing user authentication data.
 * @returns {Promise<Response>} A Promise that resolves to a Response object containing the PDF.
 */
export async function GET({ params, locals }) {
	if (!locals.user) {
		return json({ error: 'Authentication required', code: 401 }, { status: 401 });
	}

	const bookingId = parseInt(params.id);
	if (isNaN(bookingId)) {
		return json({ error: 'Invalid booking ID', code: 400 }, { status: 400 });
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
			user: true,
			payment: true
		}
	});

	if (!booking) {
		return json({ error: 'Booking not found', code: 404 }, { status: 404 });
	}

	const doc = new PDFDocument({ margin: 50 });
	const buffers: Buffer[] = [];
	doc.on('data', buffers.push.bind(buffers));

	// --- PDF Content ---
	doc.fontSize(20).text('Sky King', { align: 'center' });
	doc.fontSize(12).text('Boarding Pass / Receipt', { align: 'center' });
	doc.moveDown(2);

	doc.fontSize(14).text(`Booking Reference: #${booking.id}`);
	doc.moveDown();

	doc.fontSize(16).text(`${booking.flight.departure.code} to ${booking.flight.arrival.code}`);
	doc.fontSize(12).text(`Flight: ${booking.flight.id}`);
	doc.moveDown();

	const departureTime = new Date(booking.flight.departureTime).toLocaleString();
	const arrivalTime = new Date(booking.flight.arrivalTime).toLocaleString();
	doc.text(`Departure: ${departureTime}`);
	doc.text(`Arrival: ${arrivalTime}`);
	doc.moveDown();

	doc.fontSize(14).text('Passengers');
	booking.passengers.forEach((passenger) => {
		doc.text(`- ${passenger.name}`);
	});
	doc.moveDown();

	doc.fontSize(14).text('Payment Details');
	doc.text(`Total Price: ${booking.payment?.amount.toFixed(2)}`);
	doc.text(`Payment Method: ${booking.payment?.paymentMethod}`);
	doc.moveDown();

	// QR Code Placeholder
	doc.rect(doc.page.width - 150, 50, 100, 100).stroke();
	doc.fontSize(10).text('Scan for details', doc.page.width - 150, 155, { width: 100, align: 'center' });
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

