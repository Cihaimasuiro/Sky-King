<script lang="ts">
	import { resolve } from '$app/paths';
	import '../app.css';
	import favicon from '$lib/assets/favicon.svg';

	let { children, data } = $props();

	async function logout() {
		await fetch('/api/logout', { method: 'POST' });
		window.location.href = '/';
	}
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

<main class="min-h-screen bg-slate-950 text-slate-100">
	<nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
		<a class="text-lg font-semibold tracking-wide text-white" href={resolve('/')}>Sky King</a>
		<div class="flex items-center gap-3 text-sm">
			{#if data.user}
				<a class="rounded-md px-3 py-2 text-slate-200 hover:bg-white/10" href={resolve('/profile')}
					>Profile</a
				>
				<button
					class="rounded-md border border-white/20 px-3 py-2 text-slate-100 hover:bg-white/10"
					onclick={logout}
					type="button">Logout</button
				>
			{:else}
				<a class="rounded-md px-3 py-2 text-slate-200 hover:bg-white/10" href={resolve('/login')}
					>Login</a
				>
				<a
					class="rounded-md bg-sky-400 px-3 py-2 font-semibold text-slate-950 hover:bg-sky-300"
					href={resolve('/register')}>Register</a
				>
			{/if}
		</div>
	</nav>

	{@render children()}
</main>
