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
    html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
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

    /* SCROLLABLE dashboard */
    .canvas{position:relative;border:1px dashed var(--line);border-radius:16px;background:linear-gradient(180deg,#0c1226,#0a0f20);min-height:70vh;padding:14px;overflow:visible}
    .grid-bg{position:absolute;inset:0;background-image:linear-gradient(transparent 31px,var(--line) 32px), linear-gradient(90deg, transparent 31px,var(--line) 32px);background-size:32px 32px;opacity:.35;pointer-events:none}

    .panel{position:absolute;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:visible}
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

    .form-chip{border:1px dashed var(--line);padding:6px 10px;border-radius:10px}

    /* Config drawer inside widget */
    .cfg-drawer{border:1px solid var(--line); background:rgba(255,255,255,.03); border-radius:12px; padding:10px;}

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
      <span class="chip">Demo — Frontend-only</span>
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
        <div class="text-secondary small">High-contrast dark UI with neon accents • Drag/resize in Edit Mode • Charts powered by Chart.js</div>
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

      <!-- NEW: App Widget — Webhook App (Blog-style run view + inline editor) -->
      <section class="panel" data-id="app-webhook" data-type="app" data-main="0" style="left:736px; top:608px; width:560px; height:460px;">
        <div class="head">
          <div class="title"><div class="icon"><i data-lucide="webhook"></i></div> Webhook App</div>
          <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small">App</span>
            <button class="btn btn-soft btn-sm" id="btn-wh-config"><i data-lucide="settings"></i> Configure</button>
            <span class="handle"><i data-lucide="move"></i></span>
          </div>
        </div>
        <div class="body">
          <!-- Inline editor drawer (hidden by default) -->
          <div id="wh-config" class="cfg-drawer d-none"></div>

          <!-- Run view (blog-style) -->
          <div id="wh-run"></div>

          <div class="divider"></div>
          <div id="whResp" class="small text-secondary">No response yet.</div>
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

  // ---------- Edit mode, drag & resize + SCROLLING canvas size
  let EDIT = false;
  const canvas = document.getElementById('canvas');
  const panels = () => Array.from(canvas.querySelectorAll('.panel'));

  function fitCanvasHeight(){
    const bottoms = panels().map(p=> p.offsetTop + p.offsetHeight);
    const maxBottom = bottoms.length ? Math.max(...bottoms) : 0;
    const pad = 48; // bottom padding
    const min = Math.max(window.innerHeight * 0.6, 480);
    canvas.style.height = Math.max(maxBottom + pad, min) + 'px';
  }

  // ResizeObserver to react to panel content growth (e.g., editing fields)
  const ro = new ResizeObserver(()=> fitCanvasHeight());
  function observePanels(){ panels().forEach(p=> ro.observe(p)); }

  // Load saved layout
  (function loadLayout(){
    const saved = JSON.parse(localStorage.getItem('nd.layout')||'null');
    if(saved){
      panels().forEach(p=>{
        const id = p.dataset.id; const s = saved[id];
        if(!s) return; Object.assign(p.style, { left:s.left, top:s.top, width:s.width, height:s.height });
      });
    }
    fitCanvasHeight();
    observePanels();
    window.addEventListener('resize', fitCanvasHeight);
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
        fitCanvasHeight();
      }
    }
  }).resizable({
    edges: { left:false, right:true, bottom:true, top:false },
    listeners: {
      move (e){ if(!EDIT) return; e.target.style.width = Math.round(e.rect.width/16)*16 + 'px'; e.target.style.height = Math.round(e.rect.height/16)*16 + 'px'; fitCanvasHeight(); }
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
    setTimeout(()=> out.innerHTML = `<div class='mb-2'>✅ <b>Complete</b></div><div class='fw-bold'>Blog: ${topic}</div><div class='text-secondary mb-2'>Tone: ${tone}</div><div>LLM-generated body preview…</div>`, 1600);
    toast('Blog Generator finished.','success');
  });

  // =============================================================
  // Webhook App — Blog-style RUN view + inline EDITOR
  // =============================================================
  (function(){
    const LS_KEY = 'nd.webhookWidget.v3';

    const $ = (sel, root=document) => root.querySelector(sel);
    const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
    const esc = (s) => String(s ?? '').replace(/[&<>\"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;','\'':'&#39;'}[m]));

    const panel = document.querySelector('[data-id="app-webhook"]');
    const runRoot = $('#wh-run', panel);
    const cfgRoot = $('#wh-config', panel);
    const respRoot = $('#whResp', panel);

    const state = {
      url: '',
      method: 'POST',
      sendAsJson: false, // If true and POST with no files -> JSON body
      headers: [], // [{id,key,value}]
      response: {
        mode: 'auto', // auto|json|text|raw
        extractPath: '', // dot path to show as message
        showMessageOnly: false, // show only small message, hide detailed box
        responseOnly: false, // hide form, just show a Run button
        runButtonLabel: 'Run'
      },
      fields: [ // initial example fields
        { id: uid(), type: 'text', label: 'Topic', name: 'topic', required: false, placeholder: 'AI for SMBs', defaultValue: '' },
        { id: uid(), type: 'select', label: 'Tone', name: 'tone', required: false, options: ['Professional','Friendly','Playful'], defaultValue: 'Professional' }
      ]
    };

    // Load persisted
    try{ const saved = JSON.parse(localStorage.getItem(LS_KEY)||'null'); if(saved) deepAssign(state, saved); }catch{}

    // Configure button
    $('#btn-wh-config', panel).addEventListener('click', ()=>{
      cfgRoot.classList.toggle('d-none');
      renderConfig();
      fitCanvasHeight();
    });

    // Helpers
    function uid(){ return 'f' + Math.random().toString(36).slice(2,9); }
    function deepAssign(target, src){ for(const k in src){ if(src[k] && typeof src[k]==='object' && !Array.isArray(src[k])){ target[k]??={}; deepAssign(target[k], src[k]); } else target[k] = src[k]; } }
    function cap(s){ return s.charAt(0).toUpperCase()+s.slice(1); }
    function getByPath(obj, path){ if(!path) return undefined; return path.split('.').reduce((a,k)=> (a && (k in a)) ? a[k] : undefined, obj); }

    // ---------- RUN VIEW (blog-style form, minimal footer message)
    function renderRun(){
      const s = state;
      const methodOpts = ['GET','POST'].map(m=>`<option ${s.method===m?'selected':''}>${m}</option>`).join('');

      const fieldsHtml = s.response.responseOnly ? '' : s.fields.map(f=> inputHtml(f)).join('');

      runRoot.innerHTML = `
        <form id="whForm" class="row g-2" ${s.method==='POST' ? 'enctype="multipart/form-data"' : ''}>
          <div class="col-12">
            <div class="input-group input-group-sm">
              <span class="input-group-text">Method</span>
              <select id="whMethod" class="form-select">${methodOpts}</select>
              <input id="whUrl" class="form-control" placeholder="https://your-n8n/webhook/..." value="${esc(s.url)}" />
              <button class="btn btn-accent" id="whSend" type="submit"><i data-lucide="play"></i> ${esc(s.response.runButtonLabel || 'Run')}</button>
            </div>
            <div class="form-text">${s.sendAsJson? 'POST JSON body' : 'Auto body (multipart if files)'}. Files are ignored on GET. Configure headers & format via <b>Configure</b>.</div>
          </div>
          ${fieldsHtml || ''}
        </form>
      `;
      lucide.createIcons();

      // Set default values on inputs
      if(!s.response.responseOnly){
        for(const f of s.fields){
          const el = runRoot.querySelector(`[name='${CSS.escape(f.name)}']`);
          if(!el) continue;
          if(f.type==='checkbox'){ el.checked = !!f.defaultValue; }
          else if(f.type==='select'){ if(f.defaultValue) el.value = f.defaultValue; }
          else if(f.type!=='file' && f.defaultValue!=null){ el.value = f.defaultValue; }
        }
      }

      $('#whMethod', runRoot).addEventListener('change', e=>{ state.method = e.target.value; save(); });
      $('#whUrl', runRoot).addEventListener('input', e=>{ state.url = e.target.value.trim(); save(); });
      $('#whForm', runRoot).addEventListener('submit', onSubmit);
      fitCanvasHeight();
    }

    function inputHtml(f){
      const id = esc(f.id), nm = esc(f.name), req = f.required?'required':'';
      const ph = f.placeholder? `placeholder="${esc(f.placeholder)}"` : '';
      switch(f.type){
        case 'text':
          return `<div class="col-md-6"><input id="${id}" name="${nm}" class="form-control" ${ph} ${req} /></div>`;
        case 'number':
          return `<div class="col-md-3"><input id="${id}" name="${nm}" type="number" class="form-control" ${ph} ${req} /></div>`;
        case 'textarea':
          return `<div class="col-12"><textarea id="${id}" name="${nm}" rows="4" class="form-control" ${ph} ${req}></textarea></div>`;
        case 'checkbox':
          return `<div class="col-12 form-check ms-2"><input id="${id}" name="${nm}" class="form-check-input" type="checkbox"> <label class="form-check-label" for="${id}">${esc(f.label||nm)}</label></div>`;
        case 'select':
          return `<div class="col-md-3"><select id="${id}" name="${nm}" class="form-select">${(f.options||[]).map(o=>`<option>${esc(o)}</option>`).join('')}</select></div>`;
        case 'file':
          return `<div class="col-12"><input id="${id}" name="${nm}" type="file" class="form-control" ${req} /></div>`;
        default:
          return `<div class="col-12"><input id="${id}" name="${nm}" class="form-control" ${ph} ${req} /></div>`;
      }
    }

    async function onSubmit(e){
      e.preventDefault();
      const s = state;
      const url = ($('#whUrl', runRoot).value||'').trim();
      if(!url){ toast('Please enter a webhook URL.', 'warning'); return; }

      const btn = $('#whSend', runRoot);
      btn.disabled = true; const spin = document.createElement('span'); spin.className='spinner-border spinner-border-sm ms-2'; btn.appendChild(spin);

      const form = $('#whForm', runRoot);
      const hasFile = !!$$("input[type='file']", form).find(i=> i.files && i.files.length);
      const method = $('#whMethod', runRoot).value || 'POST';

      let fetchUrl = url;
      let fetchOpts = { method, headers: {}, mode: 'cors' };

      // Apply custom headers (skip Content-Type if we'll send multipart)
      for(const h of (state.headers||[])){ if(!h.key) continue; fetchOpts.headers[h.key] = h.value ?? ''; }

      if(method==='GET'){
        const params = new URLSearchParams();
        if(!s.response.responseOnly){
          for(const f of s.fields){
            if(f.type==='file') continue;
            const el = form.querySelector(`[name='${CSS.escape(f.name)}']`);
            if(!el) continue;
            if(f.type==='checkbox') params.append(f.name, el.checked?'true':'false');
            else params.append(f.name, el.value ?? '');
          }
        }
        // warn if files selected on GET
        const anyGetFile = !!$$("input[type='file']", form).find(i=> i.files && i.files.length);
        if(anyGetFile) toast('GET request: file inputs will be ignored.', 'warning');
        fetchUrl += (fetchUrl.includes('?')?'&':'?') + params.toString();
      } else {
        if(hasFile || !s.sendAsJson){
          const fd = new FormData();
          if(!s.response.responseOnly){
            for(const f of s.fields){
              const el = form.querySelector(`[name='${CSS.escape(f.name)}']`);
              if(!el) continue;
              if(f.type==='file'){ if(el.files && el.files.length) fd.append(f.name, el.files[0]); }
              else if(f.type==='checkbox'){ fd.append(f.name, el.checked?'true':'false'); }
              else { fd.append(f.name, el.value ?? ''); }
            }
          }
          // Ensure we don't override browser-generated boundary
          if(fetchOpts.headers['Content-Type']) delete fetchOpts.headers['Content-Type'];
          fetchOpts.body = fd;
        } else {
          const obj = {};
          if(!s.response.responseOnly){
            for(const f of s.fields){
              const el = form.querySelector(`[name='${CSS.escape(f.name)}']`);
              if(!el || f.type==='file') continue;
              obj[f.name] = (f.type==='checkbox') ? !!el.checked : (el.value ?? '');
            }
          }
          fetchOpts.headers['Content-Type'] = 'application/json';
          fetchOpts.body = JSON.stringify(obj);
        }
      }

      respRoot.classList.remove('text-secondary');
      respRoot.innerHTML = `<div class='form-chip'>Sending… Please wait.</div>`;

      try{
        const res = await fetch(fetchUrl, fetchOpts);
        const ct = (res.headers.get('content-type')||'').toLowerCase();
        let mode = s.response.mode;
        if(mode==='auto'){ mode = ct.includes('json') ? 'json' : (ct.startsWith('text/') ? 'text' : 'raw'); }

        let detailHtml = '';
        let msg = res.ok ? 'Success' : `HTTP ${res.status}`;
        let rawData;
        if(mode==='json'){
          try{ rawData = await res.json(); }catch{ rawData = { error: 'Invalid JSON body' }; }
          const pretty = esc(JSON.stringify(rawData, null, 2));
          detailHtml = `<pre class="small mb-0">${pretty}</pre>`;
          if(s.response.extractPath){
            const val = getByPath(rawData, s.response.extractPath);
            if(val!==undefined) msg = `${s.response.extractPath}: ${typeof val==='object'? esc(JSON.stringify(val)): esc(String(val))}`;
          }
        } else if(mode==='text'){
          const t = await res.text(); rawData = t; detailHtml = `<pre class="small mb-0">${esc(t)}</pre>`;
        } else { // raw
          const blob = await res.blob(); rawData = blob; const url = URL.createObjectURL(blob);
          detailHtml = `<div class='small'>Received <b>${esc(ct||'binary')}</b> (${blob.size.toLocaleString()} bytes). <a href="${url}" download="response">Download</a></div>`;
        }

        const alertType = res.ok ? 'success' : 'warning';
        const msgOnly = !!s.response.showMessageOnly;
        respRoot.innerHTML = `
          <div class="alert alert-${alertType} py-2 px-3 mb-2">${esc(msg)}</div>
          ${msgOnly ? '' : `<div class="p-2" style="background:rgba(255,255,255,.03); border:1px solid var(--line); border-radius:12px; max-height:48vh; overflow:auto;">${detailHtml}</div>`}
          <div class="mt-2 d-flex gap-2">
            <button class="btn btn-soft btn-sm" id="whClear"><i data-lucide="rotate-ccw"></i> Clear</button>
          </div>
        `;
        lucide.createIcons();
        $('#whClear', respRoot).addEventListener('click', ()=>{ respRoot.classList.add('text-secondary'); respRoot.innerHTML = 'No response yet.'; fitCanvasHeight(); });
        toast(res.ok ? 'Webhook succeeded.' : `Webhook returned ${res.status}`, alertType);
      }catch(err){
        respRoot.innerHTML = `<div class='alert alert-danger py-2 px-3'>${esc(err.message)}<br/>Likely a network or CORS error. Ensure your n8n HTTP Response includes <code>Access-Control-Allow-Origin: *</code> and <code>Access-Control-Allow-Headers</code>.</div>`;
        toast('Network/CORS error when calling webhook.', 'danger');
      }finally{
        btn.disabled = false; spin.remove(); fitCanvasHeight();
      }
    }

    // ---------- CONFIG VIEW (inline drawer with field builder + headers + response prefs)
    function renderConfig(){
      const s = state;
      cfgRoot.innerHTML = `
        <form id="cfgForm" class="row g-2">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="fw-bold">Configure Webhook</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-soft btn-sm" data-act="close"><i data-lucide="x"></i> Close</button>
              <button type="submit" class="btn btn-accent btn-sm"><i data-lucide="save"></i> Save</button>
            </div>
          </div>
          <div class="col-md-8">
            <label class="form-label small">Webhook URL</label>
            <input data-cfg="url" class="form-control form-control-sm" placeholder="https://your-n8n/webhook/..." value="${esc(s.url)}" />
          </div>
          <div class="col-md-2">
            <label class="form-label small">Method</label>
            <select data-cfg="method" class="form-select form-select-sm">
              <option ${s.method==='POST'?'selected':''}>POST</option>
              <option ${s.method==='GET'?'selected':''}>GET</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small">POST Body</label>
            <select data-cfg="body" class="form-select form-select-sm">
              <option value="auto" ${!s.sendAsJson?'selected':''}>Auto</option>
              <option value="json" ${s.sendAsJson?'selected':''}>JSON</option>
            </select>
          </div>

          <div class="col-12 mt-1"><div class="divider"></div></div>

          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div class="small text-secondary">Headers</div>
              <button class="btn btn-soft btn-sm" type="button" data-act="add-header"><i data-lucide="plus"></i> Add Header</button>
            </div>
            <div id="hdrList" class="mt-1">
              ${(s.headers||[]).map(h=> headerRow(h)).join('') || `<div class='text-secondary small'>No headers set.</div>`}
            </div>
          </div>

          <div class="col-12 mt-1"><div class="divider"></div></div>

          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div class="small text-secondary">Fields (form inputs)</div>
              <div class="d-flex gap-2">
                <select id="addType" class="form-select form-select-sm" style="width:auto">
                  <option value="text">Text</option>
                  <option value="number">Number</option>
                  <option value="textarea">Textarea</option>
                  <option value="checkbox">Checkbox</option>
                  <option value="select">Select</option>
                  <option value="file">File</option>
                </select>
                <button type="button" class="btn btn-accent btn-sm" data-act="add-field"><i data-lucide="plus"></i> Add Field</button>
              </div>
            </div>
            <div id="fieldList" class="mt-2">
              ${s.fields.map((f,i)=> fieldRow(f,i)).join('') || `<div class='text-secondary small'>No fields yet. Add one above.</div>`}
            </div>
          </div>

          <div class="col-12 mt-1"><div class="divider"></div></div>

          <div class="col-12">
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small">Response Mode</label>
                <select data-cfg="resp-mode" class="form-select form-select-sm">
                  <option value="auto" ${s.response.mode==='auto'?'selected':''}>Auto</option>
                  <option value="json" ${s.response.mode==='json'?'selected':''}>JSON</option>
                  <option value="text" ${s.response.mode==='text'?'selected':''}>Text</option>
                  <option value="raw" ${s.response.mode==='raw'?'selected':''}>Raw/Binary</option>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label small">Extract JSON path (for small message)</label>
                <input data-cfg="resp-path" class="form-control form-control-sm" placeholder="e.g. data.total" value="${esc(s.response.extractPath||'')}" />
              </div>
              <div class="col-md-2">
                <label class="form-label small">Message only</label><br>
                <input type="checkbox" data-cfg="resp-msg-only" class="form-check-input mt-2" ${s.response.showMessageOnly?'checked':''} />
              </div>
              <div class="col-md-2">
                <label class="form-label small">Response only</label><br>
                <input type="checkbox" data-cfg="resp-only" class="form-check-input mt-2" ${s.response.responseOnly?'checked':''} />
              </div>
              <div class="col-md-4">
                <label class="form-label small">Run button label</label>
                <input data-cfg="resp-run-label" class="form-control form-control-sm" value="${esc(s.response.runButtonLabel||'Run')}" />
              </div>
            </div>
          </div>
        </form>
      `;
      lucide.createIcons();

      // Delegated config events
      cfgRoot.addEventListener('input', onCfgInput);
      cfgRoot.addEventListener('click', onCfgClick);
      $('#cfgForm', cfgRoot).addEventListener('submit', (e)=>{ e.preventDefault(); save(); renderRun(); toast('Saved.', 'success'); fitCanvasHeight(); });
      fitCanvasHeight();
    }

    function headerRow(h){
      const id = esc(h.id||uid()); if(!h.id) h.id=id;
      return `<div class="row g-2 align-items-end mb-1" data-hrow="${id}">
        <div class="col-md-5"><input class="form-control form-control-sm" data-h="key" placeholder="Header name" value="${esc(h.key||'')}" /></div>
        <div class="col-md-5"><input class="form-control form-control-sm" data-h="val" placeholder="Header value" value="${esc(h.value||'')}" /></div>
        <div class="col-md-2 d-flex justify-content-end"><button type="button" class="btn btn-soft btn-sm" data-act="del-header"><i data-lucide="trash"></i></button></div>
      </div>`;
    }

    function fieldRow(f,i){
      return `<div class="row g-2 align-items-end mb-2" data-row="${esc(f.id)}" style="border:1px dashed var(--line); border-radius:12px; padding:8px;">
        <div class="col-md-3">
          <label class="form-label small">Type</label>
          <select class="form-select form-select-sm" data-edit="type">
            ${['text','number','textarea','checkbox','select','file'].map(t=>`<option value="${t}" ${f.type===t?'selected':''}>${cap(t)}</option>`).join('')}
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small">Label</label>
          <input class="form-control form-control-sm" data-edit="label" value="${esc(f.label||'')}" />
        </div>
        <div class="col-md-3">
          <label class="form-label small">Name</label>
          <input class="form-control form-control-sm" data-edit="name" value="${esc(f.name||'')}" />
        </div>
        <div class="col-md-2 form-check mt-4">
          <input class="form-check-input" type="checkbox" data-edit="req" id="req_${esc(f.id)}" ${f.required?'checked':''}>
          <label for="req_${esc(f.id)}" class="form-check-label small">Required</label>
        </div>
        <div class="col-md-6 ${f.type==='select'?'':'d-none'}">
          <label class="form-label small">Select options (comma-separated)</label>
          <input class="form-control form-control-sm" data-edit="options" value="${esc((f.options||[]).join(', '))}" />
        </div>
        <div class="col-md-3 ${['text','number','textarea'].includes(f.type)?'':'d-none'}">
          <label class="form-label small">Placeholder</label>
          <input class="form-control form-control-sm" data-edit="ph" value="${esc(f.placeholder||'')}" />
        </div>
        <div class="col-md-3 ${f.type!=='checkbox'?'':'d-none'}">
          <label class="form-label small">Default value</label>
          <input class="form-control form-control-sm" data-edit="def" value="${esc(f.defaultValue||'')}" />
        </div>
        <div class="col-md-3 ${f.type==='checkbox'?'':'d-none'}">
          <label class="form-label small">Checked by default</label><br>
          <input type="checkbox" class="form-check-input mt-2" data-edit="defcheck" ${f.defaultValue? 'checked':''} />
        </div>
        <div class="col-md-2 d-flex justify-content-end">
          <button type="button" class="btn btn-soft btn-sm" data-act="del-field"><i data-lucide="trash"></i></button>
        </div>
      </div>`;
    }

    function onCfgInput(e){
      const t = e.target; const s = state;
      // Top-level
      if(t.matches('[data-cfg="url"]')) s.url = t.value.trim();
      if(t.matches('[data-cfg="method"]')) s.method = t.value;
      if(t.matches('[data-cfg="body"]')) s.sendAsJson = (t.value==='json');
      if(t.matches('[data-cfg="resp-mode"]')) s.response.mode = t.value;
      if(t.matches('[data-cfg="resp-path"]')) s.response.extractPath = t.value;
      if(t.matches('[data-cfg="resp-run-label"]')) s.response.runButtonLabel = t.value;
      if(t.matches('[data-cfg="resp-msg-only"]')) s.response.showMessageOnly = t.checked;
      if(t.matches('[data-cfg="resp-only"]')) s.response.responseOnly = t.checked;

      // Headers
      if(t.closest('[data-hrow]')){
        const row = t.closest('[data-hrow]'); const id = row.getAttribute('data-hrow');
        const h = (state.headers||[]).find(x=>x.id===id);
        if(!h) return; if(t.matches('[data-h="key"]')) h.key = t.value; if(t.matches('[data-h="val"]')) h.value = t.value;
      }

      // Fields
      if(t.closest('[data-row]')){
        const row = t.closest('[data-row]'); const id = row.getAttribute('data-row');
        const f = state.fields.find(x=>x.id===id); if(!f) return;
        if(t.matches('[data-edit="type"]')){ f.type = t.value; if(f.type==='select' && !f.options) f.options=['Option A','Option B']; renderConfig(); }
        if(t.matches('[data-edit="label"]')) f.label = t.value;
        if(t.matches('[data-edit="name"]')) f.name = t.value;
        if(t.matches('[data-edit="req"]')) f.required = t.checked;
        if(t.matches('[data-edit="options"]')) f.options = t.value.split(',').map(x=>x.trim()).filter(Boolean);
        if(t.matches('[data-edit="ph"]')) f.placeholder = t.value;
        if(t.matches('[data-edit="def"]')) f.defaultValue = t.value;
        if(t.matches('[data-edit="defcheck"]')) f.defaultValue = t.checked;
      }

      save();
      fitCanvasHeight();
    }

    function onCfgClick(e){
      const btn = e.target.closest('[data-act]'); if(!btn) return; const act = btn.getAttribute('data-act');
      if(act==='close'){ cfgRoot.classList.add('d-none'); fitCanvasHeight(); return; }
      if(act==='add-header'){
        state.headers = state.headers||[]; state.headers.push({ id: uid(), key:'', value:'' }); renderConfig(); save(); return;
      }
      if(act==='del-header'){
        const row = btn.closest('[data-hrow]'); const id=row.getAttribute('data-hrow'); state.headers = (state.headers||[]).filter(h=>h.id!==id); renderConfig(); save(); return;
      }
      if(act==='add-field'){
        const type = $('#addType', cfgRoot).value;
        const idx = state.fields.length+1;
        const f = { id: uid(), type, label: cap(type)+' Field', name: `${type}_${idx}`, required:false };
        if(type==='select') f.options=['Option A','Option B'];
        state.fields.push(f); renderConfig(); save(); return;
      }
      if(act==='del-field'){
        const row = btn.closest('[data-row]'); const id=row.getAttribute('data-row'); state.fields = state.fields.filter(f=>f.id!==id); renderConfig(); save(); return;
      }
    }

    function save(){ localStorage.setItem(LS_KEY, JSON.stringify(state)); }

    // Initial render
    renderRun();

  })();
</script>
</body>
</html>
