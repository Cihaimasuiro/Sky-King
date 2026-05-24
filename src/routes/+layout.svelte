<script lang="ts">
	import '../app.css';
	import favicon from '$lib/assets/favicon.svg';
	import Header from '$lib/components/Header.svelte';
	import Sidebar from '$lib/components/Sidebar.svelte';
	import Notifications from '$lib/components/Notifications.svelte';

	let { children } = $props();
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

<div class="min-h-screen bg-slate-950 text-slate-100">
	<Sidebar />
	<div class="lg:pl-64">
		<Header />
		<main class="py-16">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				{@render children()}
			</div>
		</main>
	</div>
	<Notifications />
</div>
