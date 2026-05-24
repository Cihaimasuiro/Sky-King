<script lang="ts">
	import { onMount } from 'svelte';

	type Notification = {
		id: number;
		message: string;
	};

	let notifications = $state<Notification[]>([]);
	let socket: WebSocket | null = null;

	onMount(() => {
		socket = new WebSocket('ws://localhost:8080');

		socket.onopen = () => {
			console.log('WebSocket connection established');
		};

		socket.onmessage = (event) => {
			const newNotification = {
				id: Date.now(),
				message: event.data
			};
			notifications = [...notifications, newNotification];
			setTimeout(() => {
				notifications = notifications.filter((n) => n.id !== newNotification.id);
			}, 5000);
		};

		socket.onclose = () => {
			console.log('WebSocket connection closed');
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
