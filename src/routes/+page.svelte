<script lang="ts">
	import StatCard from '$lib/components/StatCard.svelte';
	import RecommendationCard from '$lib/components/RecommendationCard.svelte';
	import { page } from '$app/stores';
	import type { Booking as PrismaBooking, Passenger, Flight, Airport } from '@prisma/client';

	type BookingWithRelations = Omit<PrismaBooking, 'createdAt'> & {
		createdAt: string;
		passengers: Passenger[];
		flight: Omit<Flight, 'departureTime' | 'arrivalTime'> & {
			departureTime: string;
			arrivalTime: string;
			departure: Airport;
			arrival: Airport;
		};
	};

	let { data } = $props();

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

	function passengerList(booking: BookingWithRelations) {
		return booking.passengers.map((p: Passenger) => p.name).join(', ');
	}
</script>

<div class="space-y-6">
	<div class="rounded-lg bg-gradient-to-r from-sky-500 to-cyan-400 p-6">
		<h1 class="text-2xl font-bold text-white">Welcome, {$page.data.user?.name ?? 'Guest'}!</h1>
		<p class="text-sky-100">Here's a summary of your account.</p>
	</div>

	<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
		<StatCard label="Total Bookings" value={String(data.stats.bookingCount)} detail="All time" />
		<StatCard
			label="Total Spend"
			value={currency.format(data.stats.totalSpend)}
			detail="All time"
		/>
		<StatCard label="Upcoming Flights" value="0" detail="In the next 30 days" />
	</div>

	{#if data.recommendations.length > 0}
		<div class="space-y-4">
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

	<div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
		<div class="lg:col-span-3">
			<h2 class="text-lg font-medium text-white">Recent Bookings</h2>
			<div class="mt-4 overflow-hidden rounded-lg border border-white/10">
				{#if data.bookings.length}
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
											href={resolve(`/api/bookings/${booking.id}/ticket.pdf`)}
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
										href={resolve(`/api/bookings/${booking.id}/ticket.pdf`)}
										class="text-sm text-sky-300 hover:text-sky-200"
										download>Download Ticket</a
									>
								</div>
							</div>
						{/each}
					</div>
				{:else}
					<div class="p-8 text-center text-slate-400">
						<p>No bookings yet.</p>
					</div>
				{/if}
			</div>
		</div>
		<div class="lg:col-span-2">
			<h2 class="text-lg font-medium text-white">Quick Actions</h2>
			<div class="mt-4 space-y-4">
				<div class="rounded-lg border border-white/10 bg-white/[0.04] p-4">
					<h3 class="font-semibold text-white">Book a new flight</h3>
					<p class="text-sm text-slate-400">Find and book a new flight itinerary.</p>
					<a
						href={resolve('/search')}
						class="mt-4 inline-block rounded-md bg-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-sky-300"
						>Search Flights</a
					>
				</div>
				<div class="rounded-lg border border-white/10 bg-white/[0.04] p-4">
					<h3 class="font-semibold text-white">View your profile</h3>
					<p class="text-sm text-slate-400">Update your personal information and settings.</p>
					<a
						href={resolve('/profile')}
						class="mt-4 inline-block rounded-md bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-500"
						>Go to Profile</a
					>
				</div>
			</div>
		</div>
	</div>
</div>
