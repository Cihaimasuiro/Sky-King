<script lang="ts">
	import { page } from '$app/stores';
	import { resolve } from '$app/paths';

	const navigation = [
		{ name: 'Dashboard', href: '/' },
		{ name: 'Search Flights', href: '/search' },
		{ name: 'Profile', href: '/profile' }
	];
</script>

<aside class="hidden w-64 flex-shrink-0 bg-slate-900 lg:block">
	<div class="flex h-full flex-col">
		<div class="flex h-16 flex-shrink-0 items-center px-4">
			<a href={resolve('/')} class="text-xl font-bold text-white">Sky King</a>
		</div>
		<div class="flex-1 overflow-y-auto">
			<nav class="space-y-1 px-2 py-4">
				{#each navigation as item (item.href)}
					<a
						href={resolve(item.href)}
						class:bg-sky-600={$page.url.pathname === item.href}
						class:text-white={$page.url.pathname === item.href}
						class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
					>
						{item.name}
					</a>
				{/each}
			</nav>
		</div>
	</div>
</aside>
