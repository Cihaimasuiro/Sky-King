<script>
  export let data;
  const { user } = data;
</script>

<article>
  <h1>Welcome, {user.name}</h1>
  <p>Email: {user.email}</p>
</article>
