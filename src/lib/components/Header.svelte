<script lang="ts">
	import { resolve } from '$app/paths';
	import { page } from '$app/stores';

	async function logout() {
		await fetch('/api/logout', { method: 'POST' });
		window.location.href = '/';
	}
</script>

<header
	class="fixed top-0 z-30 flex h-16 w-full items-center justify-between border-b border-white/10 bg-slate-950/80 px-4 backdrop-blur-md sm:px-6 lg:px-8"
>
	<div class="flex items-center gap-4">
		<a href={resolve('/')} class="text-lg font-semibold tracking-wide text-white">Sky King</a>
	</div>

	<div class="hidden lg:block">
		<!-- Search bar placeholder -->
	</div>

	<div class="flex items-center gap-3 text-sm">
		{#if $page.data.user}
			<button class="i-lucide-bell h-5 w-5 text-slate-400 hover:text-white"></button>
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
</header>
