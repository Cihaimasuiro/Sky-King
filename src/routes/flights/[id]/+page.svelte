<script lang="ts">
	import FlightCard from '$lib/components/FlightCard.svelte';
	import { invalidateAll } from '$app/navigation';

	let { data } = $props();

	let passengerNames = $state<string[]>(['']);
	let bookingError = $state<string | null>(null);
	let bookingSuccess = $state<boolean>(false);

	function addPassenger() {
		passengerNames = [...passengerNames, ''];
	}

	function removePassenger(index: number) {
		passengerNames = passengerNames.filter((_, i) => i !== index);
	}

	async function handleBookingSubmit(event: Event) {
		event.preventDefault();
		bookingError = null;
		bookingSuccess = false;

		const response = await fetch('/api/bookings', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				flightId: data.flight.id,
				passengerNames: passengerNames.filter((_name) => _name.trim() !== '')
			})
		});

		if (response.ok) {
			bookingSuccess = true;
			passengerNames = ['']; // Reset form
			invalidateAll(); // Invalidate data to refresh dashboard bookings
		} else {
			const errorData = await response.json();
			bookingError = errorData.error || 'Failed to create booking.';
		}
	}
</script>

<div class="container mx-auto px-4 py-8">
	<h1 class="text-3xl font-bold text-white mb-6">Flight Details</h1>

	{#if data.flight}
		<div class="bg-slate-800 p-6 rounded-lg shadow-lg mb-8">
			<FlightCard flight={data.flight} showBookButton={false} />
		</div>

		<h2 class="text-2xl font-bold text-white mb-4">Book this Flight</h2>

		{#if bookingSuccess}
			<div class="bg-green-500 text-white p-4 rounded-md mb-4">
				Booking successful! Redirecting to dashboard...
			</div>
		{/if}

		{#if bookingError}
			<div class="bg-red-500 text-white p-4 rounded-md mb-4">
				{bookingError}
			</div>
		{/if}

		<form onsubmit={handleBookingSubmit} class="bg-slate-800 p-6 rounded-lg shadow-lg">
			<h3 class="text-xl font-semibold text-white mb-4">Passenger Information</h3>
			<!-- eslint-disable-next-line @typescript-eslint/no-unused-vars -->
			{#each passengerNames as name, i (i)}
				<div class="flex items-center mb-4">
					<input
						type="text"
						bind:value={passengerNames[i]}
						placeholder="Passenger Name {i + 1}"
						class="flex-grow bg-slate-700 text-white border border-slate-600 rounded-md p-2 mr-2 focus:outline-none focus:ring-2 focus:ring-sky-400"
						required
					/>
					{#if passengerNames.length > 1}
						<button
							type="button"
							onclick={() => removePassenger(i)}
							class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-200"
						>
							Remove
						</button>
					{/if}
				</div>
			{/each}
			<button
				type="button"
				onclick={addPassenger}
				class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-md transition duration-200 mb-6"
			>
				Add Passenger
			</button>

			<button
				type="submit"
				class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md transition duration-200"
			>
				Book Flight
			</button>
		</form>
	{:else}
		<p class="text-white text-lg">Flight not found.</p>
	{/if}
</div>
