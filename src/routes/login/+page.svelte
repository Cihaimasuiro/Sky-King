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

<h1>Login</h1>

<form on:submit|preventDefault={handleSubmit}>
  <label>
    Email
    <input type="email" bind:value={email} />
  </label>
  <label>
    Password
    <input type="password" bind:value={password} />
  </label>
  <button type="submit">Log in</button>
</form>
