<script lang="ts">
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

<nav>
  <a href="/">Home</a>
  {#if data.user}
    <a href="/profile">Profile</a>
    <button on:click={logout}>Logout</button>
  {:else}
    <a href="/login">Login</a>
    <a href="/register">Register</a>
  {/if}
</nav>

{@render children()}
