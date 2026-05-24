<script lang="ts">
	let { data } = $props();
	let user = $derived(data.user);
</script>

<section class="mx-auto max-w-3xl px-4 py-16">
	<div class="rounded-lg border border-white/10 bg-white/[0.04] p-6">
		<p class="text-sm uppercase tracking-[0.2em] text-sky-300">Traveler Profile</p>
		<h1 class="mt-3 text-3xl font-semibold text-white">
			Welcome, {user.name ?? 'Sky King traveler'}
		</h1>
		<p class="mt-2 text-slate-400">{user.email}</p>
	</div>
</section>
