<script lang="ts">
	import favicon from '$lib/assets/favicon.svg';

	let { children } = $props();
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

<nav>
  <a href="/">Home</a>
  <a href="/login">Login</a>
  <a href="/register">Register</a>
  <a href="/profile">Profile</a>
</nav>

{@render children()}
