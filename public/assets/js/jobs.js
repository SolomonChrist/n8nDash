// Job helpers (SSE + polling fallback)
async function streamJob(jobId, onEvents){
  const url = `/api/jobs/${jobId}/stream`;
  if (!!window.EventSource) {
    const es = new EventSource(url);
    es.onmessage = (ev)=>{ try{ onEvents(JSON.parse(ev.data)); }catch(e){} };
    es.addEventListener('tick', (ev)=>{ try{ onEvents(JSON.parse(ev.data)); }catch(e){} });
    es.onerror = ()=>{ es.close(); };
  } else {
    // Polling fallback
    const poll = async ()=>{
      const r = await fetch(`/api/jobs/${jobId}`).then(r=>r.json());
      onEvents(r.events||[]);
    };
    for(let i=0;i<20;i++){ await poll(); await new Promise(r=>setTimeout(r,3000)); }
  }
}
window.streamJob = streamJob;
