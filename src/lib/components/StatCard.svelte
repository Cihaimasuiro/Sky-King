<script lang="ts">
	let { label, value, detail } = $props<{
		label: string;
		value: string;
		detail: string;
	}>();
</script>

<article class="rounded-lg border border-white/10 bg-white/[0.04] p-5">
	<p class="text-sm text-slate-400">{label}</p>
	<p class="mt-3 text-3xl font-semibold text-white">{value}</p>
	<p class="mt-2 text-sm text-slate-500">{detail}</p>
</article>
