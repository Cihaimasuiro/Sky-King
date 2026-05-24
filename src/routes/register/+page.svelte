<script lang="ts">
	let name = $state('');
	let email = $state('');
	let password = $state('');
	let message = $state('');
	let pending = $state(false);

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();
		pending = true;
		message = '';

		const response = await fetch('/api/register', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ name, email, password })
		});

		const data = await response.json();
		pending = false;

		if (response.ok) {
			window.location.href = '/login';
		} else {
			message = data.message;
		}
	}
</script>

<section class="mx-auto max-w-md px-4 py-16">
	<div class="rounded-lg border border-white/10 bg-white/[0.04] p-6 shadow-2xl shadow-black/20">
		<h1 class="text-2xl font-semibold text-white">Create Account</h1>
		<p class="mt-2 text-sm text-slate-400">Start tracking flights, spend, and bookings.</p>

		<form class="mt-6 space-y-4" onsubmit={handleSubmit}>
			<label class="block text-sm font-medium text-slate-200" for="name">Name</label>
			<input
				class="w-full rounded-md border border-white/10 bg-slate-900 px-3 py-2 text-white outline-none focus:border-sky-300"
				type="text"
				id="name"
				bind:value={name}
				required
			/>

			<label class="block text-sm font-medium text-slate-200" for="email">Email</label>
			<input
				class="w-full rounded-md border border-white/10 bg-slate-900 px-3 py-2 text-white outline-none focus:border-sky-300"
				type="email"
				id="email"
				bind:value={email}
				required
			/>

			<label class="block text-sm font-medium text-slate-200" for="password">Password</label>
			<input
				class="w-full rounded-md border border-white/10 bg-slate-900 px-3 py-2 text-white outline-none focus:border-sky-300"
				type="password"
				id="password"
				bind:value={password}
				minlength="8"
				required
			/>

			{#if message}
				<p class="rounded-md border border-red-400/30 bg-red-400/10 px-3 py-2 text-sm text-red-100">
					{message}
				</p>
			{/if}

			<button
				class="w-full rounded-md bg-sky-400 px-4 py-2 font-semibold text-slate-950 hover:bg-sky-300 disabled:opacity-60"
				type="submit"
				disabled={pending}>{pending ? 'Creating...' : 'Register'}</button
			>
		</form>
	</div>
</section>
