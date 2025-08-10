<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>n8nDash v2 — Frontend Prototype</title>
  <!-- Fonts & CSS Framework -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons & Charts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    // Chart.js fallback: if CDN fails, create a stub that avoids JS errors
    window.addEventListener('error', () => { if (!window.Chart) { window.Chart = function(){ return { destroy(){} }; }; } }, { once:true });
  </script>
  <!-- Drag/Resize -->
  <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
  <style>
    :root{
      --bg: #0b1020;            /* deep slate */
      --surface: #0f162b;       /* shell */
      --card: #121a2f;          /* panels */
      --card-2: #0f1728;        /* alt */
      --text: #e6edf6;
      --muted: #9aa6b2;
      --line: rgba(255,255,255,.08);
      --shadow: 0 8px 40px rgba(2,8,23,.35);
      --radius: 18px;
      --accent: #0ea5e9;        /* Ocean default */
      --accent-2: #22c55e;      /* Emerald */
      --accent-3: #a855f7;      /* Orchid */
      --accent-4: #f59e0b;      /* Citrus */
    }
    *{box-sizing:border-box}
    html,body{height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
    a{color:#9ec1ff}
    .app{
      display:grid;grid-template-columns:240px 1fr;grid-template-rows:auto 1fr;min-height:100vh;
      grid-template-areas:"sidebar header" "sidebar main";
    }
    .sidebar{grid-area:sidebar;background:linear-gradient(180deg,#0f1530,#0b1020);border-right:1px solid var(--line);padding:20px 14px;position:sticky;top:0;height:100vh}
    .brand{display:flex;align-items:center;gap:10px;font-weight:800;letter-spacing:.3px}
    .brand .logo{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#04b7ff);display:grid;place-items:center;font-weight:900;color:#00131d;box-shadow:0 8px 20px rgba(14,165,233,.35)}
    .side-nav{margin-top:18px}
    .side-nav a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;color:var(--text);text-decoration:none;border:1px solid transparent}
    .side-nav a:hover{background:rgba(255,255,255,.05);border-color:var(--line)}
    .header{grid-area:header;display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:var(--surface);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:5}
    .header .right{display:flex;align-items:center;gap:10px}
    .chip{border:1px solid var(--line);padding:8px 10px;border-radius:999px;background:rgba(255,255,255,.03)}
    .btn-soft{border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--text);border-radius:12px}
    .btn-soft:hover{background:rgba(255,255,255,.06)}
    .theme-dot{width:14px;height:14px;border-radius:999px;display:inline-block;margin-right:6px}

    .main{grid-area:main;padding:18px;}
    .toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-bottom:14px}

    .canvas{position:relative;border:1px dashed var(--line);border-radius:16px;background:linear-gradient(180deg,#0c1226,#0a0f20);min-height:70vh;padding:14px;overflow:hidden}
    .grid-bg{position:absolute;inset:0;background-image:linear-gradient(transparent 31px,var(--line) 32px), linear-gradient(90deg, transparent 31px,var(--line) 32px);background-size:32px 32px;opacity:.35;pointer-events:none}

    .panel{position:absolute;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
    .panel .head{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid var(--line);background:linear-gradient(180deg,rgba(255,255,255,.02),transparent)}
    .title{display:flex;align-items:center;gap:10px;font-weight:700}
    .title .icon{width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,.07);display:grid;place-items:center}
    .panel .body{padding:12px;}
    .kpi{font-size:32px;font-weight:800}
    .delta.up{color:#22c55e}.delta.down{color:#ef4444}

    .handle{cursor:move;}
    .resize-corner{position:absolute;right:8px;bottom:8px;width:14px;height:14px;border:2px solid var(--line);border-bottom:none;border-right:none;transform:rotate(45deg);opacity:.7}
    .ghost{opacity:.65}

    .badge-main{border:1px solid rgba(255,255,255,.15);padding:2px 8px;border-radius:999px;font-size:12px;background:rgba(14,165,233,.15);color:#7dd3fc}
    .toast-float{position:fixed;right:18px;bottom:18px;z-index:9999}

    /* Theme presets (accent only) */
    .theme-ocean{--accent:#0ea5e9}
    .theme-emerald{--accent:#22c55e}
    .theme-orchid{--accent:#a855f7}
    .theme-citrus{--accent:#f59e0b}
    .accent{color:var(--accent)}
    .btn-accent{background:var(--accent);border:none;color:#051018}
    .btn-accent:hover{filter:brightness(1.05)}

    .divider{height:1px;background:var(--line);margin:10px 0}
    .table-darkish{--bs-table-bg:transparent;--bs-table-color:var(--text);--bs-table-border-color:var(--line)}

    @media (max-width: 1080px){
      .app{grid-template-columns:80px 1fr}
      .brand .text{display:none}
      .side-nav a span{display:none}
    }
  </style>
</head>
<body class="theme-ocean">
<div class="app">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand mb-3">
      <div class="logo">⚡</div>
      <div class="text">n8nDash v2</div>
    </div>
    <div class="side-nav vstack gap-1">
      <a href="#"><i data-lucide="layout"></i> <span>Dashboards</span></a>
      <a href="#"><i data-lucide="blocks"></i> <span>Widget Library</span></a>
      <a href="#"><i data-lucide="settings"></i> <span>Settings</span></a>
    </div>
    <div class="mt-4 small text-secondary">Drag panels in **Edit Mode**. Your layout is saved to your browser (localStorage).</div>
  </aside>

  <!-- Header -->
  <header class="header">
    <div class="d-flex align-items-center gap-2">
      <span class="chip">Demo — Frontend‑only</span>
    </div>
    <div class="right">
      <div class="dropdown">
        <button class="btn btn-soft dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="theme-dot" style="background:var(--accent)"></span> Theme
        </button>
        <ul class="dropdown-menu dropdown-menu-dark">
          <li><a class="dropdown-item" data-theme="ocean" href="#"><span class="theme-dot" style="background:#0ea5e9"></span> Ocean</a></li>
          <li><a class="dropdown-item" data-theme="emerald" href="#"><span class="theme-dot" style="background:#22c55e"></span> Emerald</a></li>
          <li><a class="dropdown-item" data-theme="orchid" href="#"><span class="theme-dot" style="background:#a855f7"></span> Orchid</a></li>
          <li><a class="dropdown-item" data-theme="citrus" href="#"><span class="theme-dot" style="background:#f59e0b"></span> Citrus</a></li>
        </ul>
      </div>
      <button id="btn-edit" class="btn btn-accent">Edit Mode</button>
      <button id="btn-main" class="btn btn-soft"><i data-lucide="refresh-ccw"></i> Run Selected</button>
      <button id="btn-save" class="btn btn-soft"><i data-lucide="save"></i> Save Layout</button>
      <button id="btn-reset" class="btn btn-soft"><i data-lucide="undo2"></i> Reset</button>
    </div>
  </header>

  <!-- Main -->
  <main class="main">
    <div class="toolbar">
      <div>
        <h3 class="mb-0">Executive Metrics — Demo</h3>
        <div class="text-secondary small">High‑contrast dark UI with neon accents • Drag/resize in Edit Mode • Charts powered by Chart.js</div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge-main">Main refresh targets: Data widgets</span>
      </div>
    </div>

    <section class="canvas" id="canvas">
      <div class="grid-bg"></div>

      <!-- KPI: Revenue -->
      <section class="panel" data-id="kpi-revenue" data-type="data" data-main="1" style="left:16px; top:16px; width:360px; height:160px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="dollar-sign"></i></div> Revenue (MRR)</div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge-main">Main</span>
            <span class="handle"><i data-lucide="move"></i></span>
          </div>
        </div>
        <div class="body">
          <div class="kpi">$<span id="revenue">82,440</span> <span class="delta up">+4.3%</span></div>
          <div class="text-secondary small">Updated 2 min ago</div>
        </div>
        <div class="resize-corner"></div>
      </section>

      <!-- KPI: YouTube Subs -->
      <section class="panel" data-id="kpi-subs" data-type="data" data-main="1" style="left:396px; top:16px; width:320px; height:160px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="users"></i></div> YouTube Subscribers</div>
          <div class="d-flex align-items-center gap-2"><span class="badge-main">Main</span><span class="handle"><i data-lucide="move"></i></span></div>
        </div>
        <div class="body">
          <div class="kpi"><span id="subs">12,873</span> <span class="delta up">+142</span></div>
          <div class="text-secondary small">Last 24h</div>
        </div>
        <div class="resize-corner"></div>
      </section>

      <!-- Line Chart: Revenue (30d) -->
      <section class="panel" data-id="chart-revenue" data-type="data" data-main="1" style="left:736px; top:16px; width:560px; height:320px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="activity"></i></div> Revenue (30 days)</div>
          <div class="d-flex align-items-center gap-2"><span class="badge-main">Main</span><span class="handle"><i data-lucide="move"></i></span></div>
        </div>
        <div class="body"><canvas id="revChart" height="110"></canvas></div>
        <div class="resize-corner"></div>
      </section>

      <!-- Bar Chart: Traffic by Day -->
      <section class="panel" data-id="chart-traffic" data-type="data" data-main="1" style="left:16px; top:192px; width:700px; height:320px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="trending-up"></i></div> Traffic (7 days)</div>
          <div class="d-flex align-items-center gap-2"><span class="badge-main">Main</span><span class="handle"><i data-lucide="move"></i></span></div>
        </div>
        <div class="body"><canvas id="trafficChart" height="110"></canvas></div>
        <div class="resize-corner"></div>
      </section>

      <!-- List: Top Issues -->
      <section class="panel" data-id="list-issues" data-type="data" data-main="1" style="left:736px; top:352px; width:560px; height:240px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="alert-circle"></i></div> Top Support Issues</div>
          <div class="d-flex align-items-center gap-2"><span class="badge-main">Main</span><span class="handle"><i data-lucide="move"></i></span></div>
        </div>
        <div class="body">
          <table class="table table-sm table-darkish align-middle mb-0">
            <thead><tr><th>Issue</th><th>Tickets</th><th>Owner</th></tr></thead>
            <tbody id="issues">
              <tr><td>Billing portal doesn’t load</td><td>34</td><td>Support</td></tr>
              <tr><td>API key rotation docs</td><td>18</td><td>Docs</td></tr>
              <tr><td>Webhook retries clarification</td><td>15</td><td>Eng</td></tr>
            </tbody>
          </table>
        </div>
        <div class="resize-corner"></div>
      </section>

      <!-- App Widget: Blog Generator (demo) -->
      <section class="panel" data-id="app-blog" data-type="app" data-main="0" style="left:16px; top:528px; width:700px; height:300px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="file-text"></i></div> Blog Generator</div>
          <div class="d-flex align-items-center gap-2"><span class="text-secondary small">App</span><span class="handle"><i data-lucide="move"></i></span></div>
        </div>
        <div class="body">
          <form id="blogForm" class="row g-2">
            <div class="col-md-6"><input class="form-control" name="topic" placeholder="Topic (e.g., AI for SMBs)" /></div>
            <div class="col-md-4">
              <select class="form-select" name="tone">
                <option value="Professional">Professional</option>
                <option value="Friendly">Friendly</option>
                <option value="Playful">Playful</option>
              </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-accent" id="btn-run-blog" type="submit"><i data-lucide="play"></i> Run</button></div>
          </form>
          <div class="divider"></div>
          <div id="blogOutput" class="small text-secondary">Result appears here…</div>
        </div>
        <div class="resize-corner"></div>
      </section>

    </section>

    <!-- toasts -->
    <div class="toast-float" id="toasts"></div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ---------- Icons
  lucide.createIcons();

  // ---------- Simple toast helper
  const toastBox = document.getElementById('toasts');
  function toast(msg, type='info'){
    const el = document.createElement('div');
    el.className = `alert alert-${type==='info'?'secondary':(type==='success'?'success':(type==='danger'?'danger':'warning'))} shadow-sm mt-2`;
    el.innerHTML = msg;
    toastBox.appendChild(el);
    setTimeout(()=> el.remove(), 4000);
  }

  // ---------- Theme presets
  const rootBody = document.body;
  const savedTheme = localStorage.getItem('nd.theme');
  if(savedTheme){ rootBody.className = `theme-${savedTheme}`; }
  document.querySelectorAll('[data-theme]').forEach(a=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      const t = a.getAttribute('data-theme');
      rootBody.className = `theme-${t}`;
      localStorage.setItem('nd.theme', t);
      document.querySelector('.theme-dot').style.background = getComputedStyle(document.body).getPropertyValue('--accent');
    })
  });

  // ---------- Charts (demo data)
  function initCharts(){
    if(!window.Chart) return; // fallback noop
    const ctx1 = document.getElementById('revChart');
    const ctx2 = document.getElementById('trafficChart');
    const gridColor = 'rgba(255,255,255,.08)';
    const textColor = '#e6edf6';
    const accent = getComputedStyle(document.body).getPropertyValue('--accent').trim();

    new Chart(ctx1.getContext('2d'), {
      type: 'line',
      data: { labels: Array.from({length: 30}, (_,i)=> i+1), datasets: [{ label: 'Revenue', data: Array.from({length:30},()=> Math.round(7000+Math.random()*2000)), tension:.35, fill:false, borderWidth:2 }] },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{labels:{color:textColor}}}, scales:{ x:{ grid:{color:gridColor}, ticks:{color:textColor} }, y:{ grid:{color:gridColor}, ticks:{color:textColor} } } }
    });

    new Chart(ctx2.getContext('2d'), {
      type: 'bar',
      data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets: [{ label: 'Visits', data:[530,610,580,740,890,660,720] }] },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{labels:{color:textColor}}}, scales:{ x:{ grid:{color:gridColor}, ticks:{color:textColor} }, y:{ grid:{color:gridColor}, ticks:{color:textColor} } } }
    });
  }
  initCharts();

  // ---------- Edit mode, drag & resize
  let EDIT = false;
  const canvas = document.getElementById('canvas');
  const panels = () => Array.from(canvas.querySelectorAll('.panel'));

  // Load saved layout
  (function loadLayout(){
    const saved = JSON.parse(localStorage.getItem('nd.layout')||'null');
    if(!saved) return;
    panels().forEach(p=>{
      const id = p.dataset.id; const s = saved[id];
      if(!s) return; Object.assign(p.style, { left:s.left, top:s.top, width:s.width, height:s.height });
    });
  })();

  function persistLayout(){
    const out = {};
    panels().forEach(p=> out[p.dataset.id] = { left:p.style.left, top:p.style.top, width:p.style.width, height:p.style.height });
    localStorage.setItem('nd.layout', JSON.stringify(out));
    toast('Layout saved.','success');
  }

  document.getElementById('btn-save').addEventListener('click', persistLayout);
  document.getElementById('btn-reset').addEventListener('click', ()=>{ localStorage.removeItem('nd.layout'); location.reload(); });

  document.getElementById('btn-edit').addEventListener('click', ()=>{
    EDIT = !EDIT;
    document.getElementById('btn-edit').textContent = EDIT ? 'Editing… (Done)' : 'Edit Mode';
    panels().forEach(p=> p.classList.toggle('ghost', EDIT));
  });

  interact('.panel').draggable({
    allowFrom: '.handle',
    listeners: {
      start (e){ if(!EDIT) e.interaction.stop(); },
      move (e){ const t=e.target; const x=(parseFloat(t.getAttribute('data-x'))||0)+e.dx; const y=(parseFloat(t.getAttribute('data-y'))||0)+e.dy; t.style.transform=`translate(${x}px, ${y}px)`; t.setAttribute('data-x',x); t.setAttribute('data-y',y); },
      end (e){ const t=e.target; const x=parseFloat(t.getAttribute('data-x'))||0; const y=parseFloat(t.getAttribute('data-y'))||0; // set new left/top and reset transform
        const rect=t.getBoundingClientRect(); const parentRect=canvas.getBoundingClientRect();
        const left=Math.max(8, rect.left - parentRect.left);
        const top=Math.max(8, rect.top - parentRect.top);
        t.style.left = Math.round(left/16)*16 + 'px';
        t.style.top  = Math.round(top/16)*16 + 'px';
        t.style.transform=''; t.setAttribute('data-x',0); t.setAttribute('data-y',0);
      }
    }
  }).resizable({
    edges: { left:false, right:true, bottom:true, top:false },
    listeners: {
      move (e){ if(!EDIT) return; let {x,y} = e.target.dataset; x = parseFloat(x)||0; y = parseFloat(y)||0; e.target.style.width = Math.round(e.rect.width/16)*16 + 'px'; e.target.style.height = Math.round(e.rect.height/16)*16 + 'px'; }
    }
  });

  // ---------- Main button (refresh data widgets)
  document.getElementById('btn-main').addEventListener('click', ()=>{
    // Simulate live refresh of data widgets
    toast('Refreshing data panels…');
    document.getElementById('revenue').textContent = (80000 + Math.floor(Math.random()*10000)).toLocaleString();
    document.getElementById('subs').textContent = (12000 + Math.floor(Math.random()*1000)).toLocaleString();
    // re-render charts with slight noise
    initCharts();
  });

  // ---------- App widget (Blog Generator) — demo only
  document.getElementById('blogForm').addEventListener('submit', (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const topic = fd.get('topic')||'AI for SMBs';
    const tone = fd.get('tone')||'Professional';
    // Simulate live progress + result
    const out = document.getElementById('blogOutput');
    out.innerHTML = `<div class='text-secondary'>Starting…</div>`;
    setTimeout(()=> out.innerHTML = `<div>📌 <b>Outline</b>…</div>`, 400);
    setTimeout(()=> out.innerHTML = `<div>✍️ <b>Drafting</b>…</div>`, 900);
    setTimeout(()=> out.innerHTML = `<div class='mb-2'>✅ <b>Complete</b></div><div class='fw-bold'>Blog: ${topic}</div><div class='text-secondary mb-2'>Tone: ${tone}</div><div>LLM‑generated body preview…</div>`, 1600);
    toast('Blog Generator finished.','success');
  });
</script>
</body>
</html>
