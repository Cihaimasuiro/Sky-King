<script lang="ts">
	import type { Flight as PrismaFlight, Airport } from '@prisma/client';

	type Flight = Omit<PrismaFlight, 'departureTime' | 'arrivalTime'> & {
		departureTime: string;
		arrivalTime: string;
		departure: Airport;
		arrival: Airport;
	};

	let { flight } = $props<{
		flight: Flight;
	}>();

	const currency = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
		maximumFractionDigits: 0
	});

	const time = new Intl.DateTimeFormat('en-US', {
		month: 'short',
		day: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	});
</script>

<article
	class="rounded-lg border border-white/10 bg-white/[0.04] p-5 transition hover:border-white/25"
>
	<div class="flex items-start justify-between gap-4">
		<div>
			<p class="text-sm text-slate-400">{flight.departure.city} to {flight.arrival.city}</p>
			<h2 class="mt-2 text-2xl font-semibold text-white">
				{flight.departure.code} <span class="text-slate-500">/</span>
				{flight.arrival.code}
			</h2>
		</div>
		<p class="text-lg font-semibold text-sky-200">{currency.format(flight.price)}</p>
	</div>

	<div class="mt-5 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
		<p>
			<span class="text-slate-500">Departs</span><br />{time.format(new Date(flight.departureTime))}
		</p>
		<p>
			<span class="text-slate-500">Arrives</span><br />{time.format(new Date(flight.arrivalTime))}
		</p>
	</div>
	<slot />
</article>
