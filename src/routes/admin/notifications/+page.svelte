<script lang="ts">
	let message = $state('');
	let status = $state('');

	async function sendMessage(event: Event) {
		event.preventDefault();
		if (!message) return;
		status = 'Sending...';
		try {
			// In a real app, this would be an API call to the backend,
			// which would then send the message to the WebSocket server.
			// For this demo, we'll just send it directly from the client.
			const socket = new WebSocket('ws://localhost:8080');
			socket.onopen = () => {
				socket.send(message);
				socket.close();
				status = `Message sent: "${message}"`;
				message = '';
			};
			socket.onerror = () => {
				status = 'Error sending message.';
			};
			// eslint-disable-next-line @typescript-eslint/no-unused-vars
		} catch (err) {
			status = 'Error sending message.';
		}
	}
</script>

<div class="mx-auto max-w-2xl">
	<h1 class="text-2xl font-bold text-white">Send Notification</h1>
	<p class="mt-2 text-slate-400">
		This form sends a message to all connected clients via the WebSocket server.
	</p>
	<form onsubmit={sendMessage} class="mt-4 space-y-4">
		<div>
			<label for="message" class="block text-sm font-medium text-slate-300">Message</label>
			<input
				type="text"
				id="message"
				bind:value={message}
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div class="pt-2">
			<button
				type="submit"
				class="w-full rounded-md bg-sky-400 px-4 py-2 font-semibold text-slate-950 hover:bg-sky-300"
			>
				Send Notification
			</button>
		</div>
	</form>
	{#if status}
		<p class="mt-4 text-slate-400">{status}</p>
	{/if}
</div>
