<script>
  let email = '';
  let password = '';

  async function handleSubmit() {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (response.ok) {
      alert('Login successful!');
      // Redirect to home page
      window.location.href = '/';
    } else {
      alert(data.message);
    }
  }
</script>

<article>
  <h1>Login</h1>

  <form on:submit|preventDefault={handleSubmit}>
    <label for="email">Email</label>
    <input type="email" id="email" bind:value={email} />

    <label for="password">Password</label>
    <input type="password" id="password" bind:value={password} />

    <button type="submit">Log in</button>
  </form>
</article>
