<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';

	type Notification = {
		id: number;
		message: string;
	};

	type Flight = {
		id: number;
		departureTime: string;
	};

	type Booking = {
		flight: Flight;
	};

	let notifications = $state<Notification[]>([]);
	let socket: WebSocket | null = null;

	onMount(() => {
		socket = new WebSocket('ws://localhost:8080');

		socket.onopen = () => {
			// console.log('WebSocket connection established');
			const upcomingFlightIds = ($page.data.bookings as Booking[])
				?.filter((b: Booking) => new Date(b.flight.departureTime) > new Date())
				.map((b: Booking) => b.flight.id);

			if (upcomingFlightIds?.length > 0) {
				socket?.send(JSON.stringify({ type: 'subscribe', flight_ids: upcomingFlightIds }));
			}
		};

		socket.onmessage = (event) => {
			try {
				const data = JSON.parse(event.data);
				if (data.type === 'flight_status') {
					const newNotification = {
						id: Date.now(),
						message: data.message
					};
					notifications = [...notifications, newNotification];
					setTimeout(() => {
						notifications = notifications.filter((n) => n.id !== newNotification.id);
					}, 5000);
				}
				// eslint-disable-next-line @typescript-eslint/no-unused-vars
			} catch (e) {
				console.error('Failed to parse WebSocket message:', event.data);
			}
		};

		socket.onclose = () => {
			// console.log('WebSocket connection closed');
		};

		return () => {
			socket?.close();
		};
	});
</script>

<div class="fixed bottom-4 right-4 z-50 space-y-2">
	{#each notifications as notification (notification.id)}
		<div class="rounded-md bg-sky-500 px-4 py-3 text-white shadow-lg">
			{notification.message}
		</div>
	{/each}
</div>
