<div class="d-flex align-items-center justify-content-between">
  <h3>Your Dashboards</h3>
  <a class="btn btn-sm btn-outline-secondary" href="/onboarding/default">Onboarding</a>
</div>
<hr>
<div id="dashList" class="row g-3"></div>
<script>
(async ()=>{
  const me = await fetch('/api/me').then(r=>r.json());
  if(!me.ok){ location.href='/login'; return; }
  const res = await fetch('/api/dashboards').then(r=>r.json());
  const list = document.querySelector('#dashList'); list.innerHTML='';
  (res.dashboards||[]).forEach(d=>{
    const col = document.createElement('div'); col.className='col-md-4';
    col.innerHTML = `<div class='card h-100'><div class='card-body'>
      <h5 class='card-title'>${d.name}</h5>
      <p class='card-text text-muted'>${d.description||''}</p>
      <a class='btn btn-primary' href='/dashboard/${d.slug}'>Open</a>
    </div></div>`;
    list.appendChild(col);
  });
})();
</script>
