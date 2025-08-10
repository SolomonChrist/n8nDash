// Minimal fallback if Chart.js CDN fails
(function(){
  if (!window.Chart) {
    console.warn('Chart.js CDN unavailable. Charts will render as JSON blocks.');
    window.Chart = function(ctx, cfg){
      const pre = document.createElement('pre');
      pre.textContent = JSON.stringify(cfg.data||cfg, null, 2);
      (ctx && ctx.parentNode ? ctx.parentNode : document.body).appendChild(pre);
      return { destroy: function(){} };
    };
  }
})();