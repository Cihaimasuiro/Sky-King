<script lang="ts">
	import RecommendationCard from '$lib/components/RecommendationCard.svelte';
	import FlightCard from '$lib/components/FlightCard.svelte';
	import type { Flight as PrismaFlight, Airport } from '@prisma/client';

	type Flight = Omit<PrismaFlight, 'departureTime' | 'arrivalTime'> & {
		departureTime: string;
		arrivalTime: string;
		departure: Airport;
		arrival: Airport;
	};

	let { data } = $props();

	let departure = $state('');
	let arrival = $state('');
	let date = $state('');

	let filteredFlights = $derived(
		data.flights.filter((flight: Flight) => {
			const departureMatch =
				!departure ||
				flight.departure.code.toLowerCase().includes(departure.toLowerCase()) ||
				flight.departure.city.toLowerCase().includes(departure.toLowerCase());
			const arrivalMatch =
				!arrival ||
				flight.arrival.code.toLowerCase().includes(arrival.toLowerCase()) ||
				flight.arrival.city.toLowerCase().includes(arrival.toLowerCase());
			const dateMatch =
				!date || flight.departureTime.startsWith(new Date(date).toISOString().split('T')[0]);

			return departureMatch && arrivalMatch && dateMatch;
		})
	);
</script>

<div class="mx-auto max-w-2xl">
	<h1 class="text-2xl font-bold text-white">Search Flights</h1>
	<form class="mt-4 space-y-4">
		<div>
			<label for="departure" class="block text-sm font-medium text-slate-300"
				>Departure Airport</label
			>
			<input
				type="text"
				id="departure"
				bind:value={departure}
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div>
			<label for="arrival" class="block text-sm font-medium text-slate-300">Arrival Airport</label>
			<input
				type="text"
				id="arrival"
				bind:value={arrival}
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div>
			<label for="date" class="block text-sm font-medium text-slate-300">Date</label>
			<input
				type="date"
				id="date"
				bind:value={date}
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
	</form>

	{#if data.recommendations.length > 0}
		<div class="mt-8 space-y-4">
			<h2 class="text-lg font-medium text-white">Recommended for You</h2>
			<div class="flex space-x-4 overflow-x-auto pb-4">
				{#each data.recommendations as flight (flight.id)}
					<div class="w-80 flex-shrink-0">
						<RecommendationCard {flight}>
							<p class="mt-2 text-xs text-sky-200">{flight.reason}</p>
						</RecommendationCard>
					</div>
				{/each}
			</div>
		</div>
	{/if}

	<div class="mt-8 space-y-4">
		<h2 class="text-lg font-medium text-white">Available Flights</h2>
		<div class="grid gap-4 md:grid-cols-2">
			{#each filteredFlights as flight (flight.id)}
				<FlightCard {flight} />
			{/each}
		</div>
		{#if filteredFlights.length === 0}
			<p class="text-slate-400">No flights found matching your criteria.</p>
		{/if}
	</div>
</div>
