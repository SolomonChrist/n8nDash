<div class="row justify-content-center">
  <div class="col-md-4">
    <h3>Login</h3>
    <form id="loginForm" class="mt-3">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Sign In</button>
      <div id="loginMsg" class="text-danger small mt-2"></div>
    </form>
  </div>
</div>
<script>
document.querySelector('#loginForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd.entries());
  const r = await fetch('/api/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
  if(r.ok){ location.href = '/dashboards'; } else { document.querySelector('#loginMsg').textContent = 'Invalid credentials'; }
});
</script>
