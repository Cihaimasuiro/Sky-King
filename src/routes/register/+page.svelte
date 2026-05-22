<script>
  let name = '';
  let email = '';
  let password = '';

  async function handleSubmit() {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ name, email, password }),
    });

    const data = await response.json();

    if (response.ok) {
      alert('Registration successful!');
      // Redirect to login page
      window.location.href = '/login';
    } else {
      alert(data.message);
    }
  }
</script>

<article>
  <h1>Register</h1>

  <form on:submit|preventDefault={handleSubmit}>
    <label for="name">Name</label>
    <input type="text" id="name" bind:value={name} />

    <label for="email">Email</label>
    <input type="email" id="email" bind:value={email} />

    <label for="password">Password</label>
    <input type="password" id="password" bind:value={password} />

    <button type="submit">Register</button>
  </form>
</article>
