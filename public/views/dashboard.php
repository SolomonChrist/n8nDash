<div class="d-flex align-items-center justify-content-between">
  <h3 id="dashTitle">Dashboard</h3>
  <div class="d-flex gap-2">
    <button class="btn btn-success" id="mainBtn">Main</button>
    <a class="btn btn-outline-secondary" href="/editor/<?php echo htmlspecialchars($slug); ?>">Edit</a>
  </div>
</div>
<div id="grid" class="mt-3 row g-3"></div>
<script>
const slug = <?php echo json_encode($slug); ?>;
let widgets = [];
(async ()=>{
  const me = await fetch('/api/me').then(r=>r.json()); if(!me.ok){ location.href='/login'; return; }
  const res = await fetch(`/api/dashboards/${slug}`).then(r=>r.json());
  if(!res.ok){ alert('Dashboard not found'); return; }
  document.querySelector('#dashTitle').textContent = res.dashboard.name;
  widgets = res.widgets||[];
  const grid = document.querySelector('#grid'); grid.innerHTML='';
  widgets.forEach(w=>{
    const col = document.createElement('div'); col.className = 'col-md-'+Math.max(2, Math.min(12, w.width*2));
    col.innerHTML = `<div class='card h-100'>
      <div class='card-body'>
        <div class='d-flex justify-content-between align-items-center'>
          <h5 class='card-title mb-0'>${w.title}</h5>
          <div>
            ${w.type==='app' ? `<button class='btn btn-sm btn-primary' data-run='${w.id}'>Run</button>` : ''}
          </div>
        </div>
        <div class='small text-muted mt-1'>${w.type.toUpperCase()}</div>
        <div class='mt-3' id='wout-${w.id}'></div>
      </div>
    </div>`;
    grid.appendChild(col);
  });
  // Autorun data widgets
  widgets.filter(w=>w.type!=='app' && w.autorun_on_load).forEach(runWidget);
})();

document.addEventListener('click', (e)=>{
  const id = e.target?.dataset?.run;
  if(id) runWidget(widgets.find(x=>x.id==id));
});
document.querySelector('#mainBtn').addEventListener('click', ()=>{
  widgets.filter(w=>w.include_in_main).forEach(runWidget);
});
</script>
