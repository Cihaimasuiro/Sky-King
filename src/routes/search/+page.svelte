<script lang="ts">
	import RecommendationCard from '$lib/components/RecommendationCard.svelte';

	let { data } = $props();
</script>

<div class="mx-auto max-w-2xl">
	<h1 class="text-2xl font-bold text-white">Search Flights</h1>
	<form class="mt-4 space-y-4">
		<div>
			<label for="departure" class="block text-sm font-medium text-slate-300">Departure Airport</label>
			<input
				type="text"
				id="departure"
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div>
			<label for="arrival" class="block text-sm font-medium text-slate-300">Arrival Airport</label>
			<input
				type="text"
				id="arrival"
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div>
			<label for="date" class="block text-sm font-medium text-slate-300">Date</label>
			<input
				type="date"
				id="date"
				class="mt-1 block w-full rounded-md border-white/10 bg-slate-800 px-3 py-2 text-white focus:border-sky-300 focus:ring-sky-300"
			/>
		</div>
		<div class="pt-2">
			<button
				type="submit"
				class="w-full rounded-md bg-sky-400 px-4 py-2 font-semibold text-slate-950 hover:bg-sky-300"
			>
				Search
			</button>
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
</div>
