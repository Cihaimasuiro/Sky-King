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

<main class="container">
  <nav>
    <ul>
      <li><a href="/"><strong>Sky King</strong></a></li>
    </ul>
    <ul>
      {#if data.user}
        <li><a href="/profile">Profile</a></li>
        <li><button on:click={logout}>Logout</button></li>
      {:else}
        <li><a href="/login">Login</a></li>
        <li><a href="/register">Register</a></li>
      {/if}
    </ul>
  </nav>

  {@render children()}
</main>
