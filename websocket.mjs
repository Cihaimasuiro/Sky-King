import { WebSocketServer } from 'ws';

const wss = new WebSocketServer({ port: 8080 });
const subscriptions = new Map();

wss.on('connection', function connection(ws) {
	// console.log('Client connected');

	ws.on('message', function message(data) {
		try {
			const message = JSON.parse(data);
			if (message.type === 'subscribe' && Array.isArray(message.flight_ids)) {
				message.flight_ids.forEach((flightId) => {
					if (!subscriptions.has(flightId)) {
						subscriptions.set(flightId, new Set());
					}
					subscriptions.get(flightId).add(ws);
				});
				// console.log(`Client subscribed to flights: ${message.flight_ids.join(', ')}`);
			} else if (message.type === 'flight_status' && message.flight_id) {
				// console.log(`Broadcasting status for flight ${message.flight_id}`);
				const subscribers = subscriptions.get(message.flight_id);
				if (subscribers) {
					subscribers.forEach((client) => {
						if (client.readyState === 1) {
							client.send(JSON.stringify(message));
						}
					});
				}
			}
		} catch (e) {
			console.error('Failed to parse message or invalid message format:', data);
		}
	});

	ws.on('close', () => {
		// console.log('Client disconnected');
		// Clean up subscriptions
		subscriptions.forEach((subscribers, flightId) => {
			if (subscribers.has(ws)) {
				subscribers.delete(ws);
				if (subscribers.size === 0) {
					subscriptions.delete(flightId);
				}
			}
		});
	});
});

// console.log('WebSocket server started on port 8080');
