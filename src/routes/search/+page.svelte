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
</div>
