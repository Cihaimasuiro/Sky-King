import { WebSocketServer } from 'ws';

const wss = new WebSocketServer({ port: 8080 });

wss.on('connection', function connection(ws) {
	console.log('Client connected');

	ws.on('message', function message(data) {
		console.log('received: %s', data);
		// Broadcast to all clients
		wss.clients.forEach(function each(client) {
			if (client !== ws && client.readyState === 1) {
				client.send(data.toString());
			}
		});
	});

	ws.on('close', () => {
		console.log('Client disconnected');
	});
});

console.log('WebSocket server started on port 8080');
