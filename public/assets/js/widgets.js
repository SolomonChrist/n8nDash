// Trigger widget run and render output
async function runWidget(w){
  const target = document.querySelector(`#wout-${w.id}`);
  target.innerHTML = `<div class='text-muted'>Starting…</div>`;
  const r = await fetch(`/api/widgets/${w.id}/run`,{method:'POST'}).then(r=>r.json());
  if(!r.ok){ target.innerHTML = `<div class='text-danger'>Failed: ${r.error||'unknown'}</div>`; return; }
  const jobId = r.job_id;
  streamJob(jobId, (events)=>{
    target.innerHTML = renderWidgetOutput(w, events);
  });
}
function renderWidgetOutput(w, events){
  let html = `<div class='small text-muted'>${events.length} events</div>`;
  const last = events[events.length-1]||{};
  const payloadRaw = last.payload_json;
  let payload = {};
  try { payload = typeof payloadRaw==='object' ? payloadRaw : (payloadRaw ? JSON.parse(payloadRaw) : {}); } catch(e){ payload = {}; }
  const res = payload.result || {};
  if(w.type==='data'){
    if(res.chart){
      const id = `c${w.id}`;
      html += `<canvas id='${id}' height='100'></canvas>`;
      setTimeout(()=>{
        try{
          new Chart(document.getElementById(id), {type: res.chart.type||'line', data: {labels: res.chart.labels||[], datasets: res.chart.datasets||[]}});
        }catch(e){}
      },0);
    } else if(res.items){
      html += `<ul class='list-group list-group-flush'>` + res.items.map(i=>`<li class='list-group-item'><a href='${i.url}' target='_blank'>${i.title}</a></li>`).join('') + `</ul>`;
    } else if(typeof res.kpi !== 'undefined'){
      html += `<div class='display-6'>${res.kpi}</div><div class='text-success'>Δ ${res.delta||0}</div>`;
    } else if(res.table){
      html += `<div class='table-responsive'><table class='table table-sm'><thead><tr>` + res.table.columns.map(c=>`<th>${c}</th>`).join('') + `</tr></thead><tbody>` + res.table.rows.map(r=>`<tr>`+r.map(c=>`<td>${c}</td>`).join('')+`</tr>`).join('') + `</tbody></table></div>`;
    } else {
      html += `<pre class='small bg-light p-2'>`+JSON.stringify(res,null,2)+`</pre>`;
    }
  } else {
    if(res.body){ html += `<div>${res.body}</div>`; }
    if(res.title){ html += `<h5>${res.title}</h5>`; }
    if(res.summary){ html += `<p>${res.summary}</p>`; }
    if(res.files){ html += `<div>${res.files.length} file(s)</div>`; }
    if(!res.title && !res.body && !res.summary){
      html += `<pre class='small bg-light p-2'>`+JSON.stringify(res,null,2)+`</pre>`;
    }
  }
  html += `<div class='mt-3'><ol class='small text-muted'>` + events.map(e=>`<li>${(e.step||'…')} ${e.progress?('('+e.progress+'%)'):''} ${e.message||''}</li>`).join('') + `</ol></div>`;
  return html;
}
window.runWidget = runWidget;
