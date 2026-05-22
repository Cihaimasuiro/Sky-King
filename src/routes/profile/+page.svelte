<script>
  import { onMount } from 'svelte';

  let user = null;

  onMount(async () => {
    // This is not a secure way to check for authentication.
    // We will implement a proper session-based authentication later.
    const response = await fetch('/api/profile');
    if (response.ok) {
      user = await response.json();
    } else {
      window.location.href = '/login';
    }
  });
</script>

{#if user}
  <h1>Welcome, {user.name}</h1>
  <p>Email: {user.email}</p>
{:else}
  <p>Loading...</p>
{/if}
