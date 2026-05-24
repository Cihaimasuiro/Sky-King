<script lang="ts">
	import RecommendationCard from '$lib/components/RecommendationCard.svelte';
	import { resolve } from '$app/paths';
	import FlightCard from '$lib/components/FlightCard.svelte';
	import StatCard from '$lib/components/StatCard.svelte';

	type Flight = {
		id: number;
		departure: {
			code: string;
			city: string;
			country: string;
		};
		arrival: {
			code: string;
			city: string;
			country: string;
		};
		departureTime: string;
		arrivalTime: string;
		price: number;
	};

	type Booking = {
		id: number;
		createdAt: string;
		flight: Flight;
		passengers: {
			id: number;
			name: string;
			seat: string;
		}[];
	};

	let { data } = $props<{
		data: {
			user?: {
				id: number;
				name: string | null;
				email: string;
			};
			flights: Flight[];
			bookings: Booking[];
			recommendations: Flight[];
			stats: {
				totalSpend: number;
				bookingCount: number;
				destinationCount: number;
			};
		};
	}>();

	let selectedFlight = $state<Flight | null>(null);
	let passengers = $state('');
	let message = $state('');
	let pending = $state(false);
	let responseOk = $state(false);

	const currency = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
		maximumFractionDigits: 0
	});

	const shortDate = new Intl.DateTimeFormat('en-US', {
		month: 'short',
		day: 'numeric',
		year: 'numeric'
	});

	$effect(() => {
		if (!selectedFlight && data.flights.length) {
			selectedFlight = data.flights[0];
		}

		if (!passengers && data.user?.name) {
			passengers = data.user.name;
		}
	});

	function passengerList(booking: Booking) {
		return booking.passengers.map((passenger) => passenger.name).join(', ');
	}

	async function createBooking(event: SubmitEvent) {
		event.preventDefault();

		const passengerNames = passengers
			.split('\n')
			.map((name) => name.trim())
			.filter(Boolean);

		if (!selectedFlight) {
			message = 'Select a flight first.';
			return;
		}

		if (passengerNames.length === 0) {
			message = 'Please enter at least one passenger name.';
			return;
		}

		pending = true;
		message = '';

		const response = await fetch('/api/bookings', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				flightId: selectedFlight.id,
				passengerNames
			})
		});

		const result = await response.json();
		pending = false;
		responseOk = response.ok;

		if (response.ok) {
			message = 'Booking created successfully!';
			data.bookings = [result.booking, ...data.bookings];
			data.stats.bookingCount++;
			data.stats.totalSpend += result.booking.flight.price;
			const destinationCodes = new Set(data.bookings.map((b) => b.flight.arrival.code));
			data.stats.destinationCount = destinationCodes.size;
			passengers = data.user?.name ?? '';
			selectedFlight = data.flights.length ? data.flights[0] : null;
		} else {
			message = result.message;
		}
	}
</script>

<section
	class="mx-auto grid max-w-6xl gap-10 px-4 pb-16 pt-10 sm:px-6 lg:px-8"
>
	<div class="flex flex-col justify-center text-center">
		<p class="text-sm font-semibold uppercase tracking-[0.28em] text-sky-300">
			Flight Reservation Platform
		</p>
		<h1 class="mt-5 max-w-3xl text-5xl font-semibold leading-tight text-white mx-auto">
			Sky King
		</h1>
		<p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300 mx-auto">
			A modern SvelteKit rebuild of an airline booking system with live booking history, spend
			tracking, and Prisma-backed API routes.
		</p>
		<div class="mt-8 flex flex-wrap gap-3 justify-center">
			<a
				class="rounded-md bg-sky-400 px-5 py-3 font-semibold text-slate-950 hover:bg-sky-300"
				href={data.user ? '#book' : resolve('/register')}
			>
				{data.user ? 'Book a Flight' : 'Create Account'}
			</a>
			<a
				class="rounded-md border border-white/15 px-5 py-3 font-semibold text-white hover:bg-white/10"
				href={data.user ? resolve('/profile') : resolve('/login')}
			>
				{data.user ? 'View Profile' : 'Login'}
			</a>
		</div>
	</div>

	<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
		<StatCard
			label="Total Expenses"
			value={currency.format(data.stats.totalSpend)}
			detail={data.user ? 'Across all completed bookings' : 'Login to track ticket spend'}
		/>
		<StatCard
			label="Orders Placed"
			value={String(data.stats.bookingCount)}
			detail={data.user ? 'Reactive booking counter' : 'Your orders will appear here'}
		/>
		<StatCard
			label="Destinations"
			value={String(data.stats.destinationCount)}
			detail={data.user ? 'Unique arrival airports visited' : 'Build your travel history'}
		/>
	</div>
</section>

<section id="book" class="border-y border-white/10 bg-slate-900/70">
	<div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_380px] lg:px-8">
		<div>
			<div class="flex items-end justify-between gap-4">
				<div>
					<p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-300">
						Available Flights
					</p>
					<h2 class="mt-2 text-3xl font-semibold text-white">Choose an itinerary</h2>
				</div>
				<a
					class="text-sm font-medium text-sky-200 hover:text-sky-100"
					href={resolve('/api/flights')}>API</a
				>
			</div>

			{#if data.flights.length}
				<div class="mt-6 grid gap-4 md:grid-cols-2">
					{#each data.flights as flight (flight.id)}
						<FlightCard
							{flight}
							selected={selectedFlight?.id === flight.id}
							onselect={(nextFlight) => (selectedFlight = nextFlight)}
						/>
					{/each}
				</div>
			{:else}
				<div class="mt-6 rounded-lg border border-dashed border-white/20 p-8 text-slate-400">
					No flights are loaded yet. Seed the Airport and Flight tables to activate booking.
				</div>
			{/if}
		</div>

		<aside class="h-fit rounded-lg border border-white/10 bg-white/[0.04] p-5">
			<h2 class="text-xl font-semibold text-white">Quick booking</h2>
			<p class="mt-2 text-sm text-slate-400">
				{#if data.user}
					Add one passenger per line. Seats are assigned automatically.
				{:else}
					Login or register to create bookings.
				{/if}
			</p>

			<form class="mt-5 space-y-4" onsubmit={createBooking}>
				<div class="rounded-md border border-white/10 bg-slate-950 p-3 text-sm text-slate-300">
					{#if selectedFlight}
						<span class="font-semibold text-white">{selectedFlight.departure.code}</span> to
						<span class="font-semibold text-white">{selectedFlight.arrival.code}</span>
						<span class="block pt-1 text-slate-500">{currency.format(selectedFlight.price)}</span>
					{:else}
						Select a flight to continue.
					{/if}
				</div>

				<label class="block text-sm font-medium text-slate-200" for="passengers">Passengers</label>
				<textarea
					class="min-h-32 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2 text-white outline-none focus:border-sky-300 disabled:opacity-60"
					id="passengers"
					bind:value={passengers}
					disabled={!data.user}
					required
				></textarea>

				{#if message}
					<p
						class:text-red-100={!responseOk}
						class:border-red-400={!responseOk}
						class:bg-red-400={!responseOk}
						class:text-green-100={responseOk}
						class:border-green-400={responseOk}
						class:bg-green-400={responseOk}
						class="rounded-md border px-3 py-2 text-sm"
					>
						{message}
					</p>
				{/if}

				<button
					class="w-full rounded-md bg-sky-400 px-4 py-2 font-semibold text-slate-950 hover:bg-sky-300 disabled:opacity-50"
					type="submit"
					disabled={!data.user || !selectedFlight || pending}
				>
					{pending ? 'Booking...' : 'Create booking'}
				</button>
			</form>
		</aside>
	</div>
</section>

{#if data.recommendations.length > 0}
	<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
		<div class="flex items-end justify-between gap-4">
			<div>
				<p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-300">
					Recommended for You
				</p>
				<h2 class="mt-2 text-3xl font-semibold text-white">Popular Destinations</h2>
			</div>
			<a
				class="text-sm font-medium text-sky-200 hover:text-sky-100"
				href={resolve('/api/recommendations')}>API</a
			>
		</div>
		<div class="mt-6 grid gap-4 md:grid-cols-3">
			{#each data.recommendations as flight (flight.id)}
				<RecommendationCard {flight} />
			{/each}
		</div>
	</section>
{/if}

<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
	<div class="flex items-end justify-between gap-4">
		<div>
			<p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-300">History</p>
			<h2 class="mt-2 text-3xl font-semibold text-white">Recent itineraries</h2>
		</div>
		<a class="text-sm font-medium text-sky-200 hover:text-sky-100" href={resolve('/api/bookings')}
			>Bookings API</a
		>
	</div>

	{#if data.bookings.length}
		<div class="mt-6 overflow-hidden rounded-lg border border-white/10">
			<table class="hidden w-full border-collapse text-left text-sm md:table">
				<thead class="bg-white/[0.04] text-slate-400">
					<tr>
						<th class="px-4 py-3 font-medium">Date</th>
						<th class="px-4 py-3 font-medium">Route</th>
						<th class="px-4 py-3 font-medium">Passengers</th>
						<th class="px-4 py-3 text-right font-medium">Price</th>
						<th class="px-4 py-3 text-right font-medium"></th>
					</tr>
				</thead>
				<tbody class="divide-y divide-white/10">
					{#each data.bookings as booking (booking.id)}
						<tr class="text-slate-300">
							<td class="px-4 py-3">{shortDate.format(new Date(booking.createdAt))}</td>
							<td class="px-4 py-3"
								>{booking.flight.departure.code} to {booking.flight.arrival.code}</td
							>
							<td class="px-4 py-3">{passengerList(booking)}</td>
							<td class="px-4 py-3 text-right">{currency.format(booking.flight.price)}</td>
							<td class="px-4 py-3 text-right">
								<a
									href={`/api/bookings/${booking.id}/ticket.pdf`}
									class="text-sky-300 hover:text-sky-200"
									download>Download</a
								>
							</td>
						</tr>
					{/each}
				</tbody>
			</table>
			<div class="divide-y divide-white/10 md:hidden">
				{#each data.bookings as booking (booking.id)}
					<div class="p-4 text-slate-300">
						<div class="flex items-center justify-between">
							<p class="font-semibold">
								{booking.flight.departure.code} to {booking.flight.arrival.code}
							</p>
							<p class="text-right font-semibold text-sky-200">
								{currency.format(booking.flight.price)}
							</p>
						</div>
						<p class="mt-2 text-sm text-slate-400">{passengerList(booking)}</p>
						<div class="mt-2 flex items-center justify-between">
							<p class="text-xs text-slate-500">
								{shortDate.format(new Date(booking.createdAt))}
							</p>
							<a
								href={`/api/bookings/${booking.id}/ticket.pdf`}
								class="text-sm text-sky-300 hover:text-sky-200"
								download>Download Ticket</a
							>
						</div>
					</div>
				{/each}
			</div>
		</div>
	{:else}
		<div class="mt-6 rounded-lg border border-dashed border-white/20 p-8 text-slate-400">
			{data.user
				? 'No bookings yet. Choose a flight above to start the history log.'
				: 'Login to view your booking history.'}
		</div>
	{/if}
</section>
