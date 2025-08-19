document.addEventListener('DOMContentLoaded',()=>{const r=document.getElementById('content-root');async function load(k){r.innerHTML='<div class="card"><p>Завантаження…</p></div>';const x=await fetch('/corpos-full-v2/public/fragment.php?v='+encodeURIComponent(k),{headers:{'X-Requested-With':'fetch'}});r.innerHTML=await x.text();if(k==='corp/money_flow')initMoneyFlowTabs();}function route(){const k=(location.hash||'').slice(1);if(k)load(k);}addEventListener('hashchange',route);route();});
function qs(s,r=document){return r.querySelector(s)};function ce(t,p={}){const e=document.createElement(t);Object.assign(e,p);return e;}function fmt(n){return Number(n||0).toLocaleString('uk-UA',{minimumFractionDigits:2,maximumFractionDigits:2});}
function initMoneyFlowTabs(){const c=qs('#mf-tabs'),p=qs('#mf-panel');if(!c||!p)return;c.addEventListener('click',e=>{const b=e.target.closest('[data-mf-tab]');if(!b)return;c.querySelectorAll('[data-mf-tab]').forEach(el=>el.classList.remove('active'));b.classList.add('active');if(b.dataset.mfTab==='owner')renderOwnerIncome(p);else if(b.dataset.mfTab==='owner_capital')renderOwnerCapital(p);else p.innerHTML='<div class="card"><p>Секція у розробці…</p></div>';});(c.querySelector('[data-mf-tab].active')||c.querySelector('[data-mf-tab]')).click();}
async function apiOwner(a,d={}){const f=new URLSearchParams({action:a,csrf_token:window.__csrf});Object.entries(d).forEach(([k,v])=>f.append(k,v));const r=await fetch('/corpos-full-v2/public/api_owner.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:f});if(!r.ok)throw 0;return r.json();}
function renderOwnerIncome(panel){panel.innerHTML=`<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap"><h3 style="margin:0">Надходження капіталу від власника</h3><button id="btn-owner-add" class="btn primary">Додати надходження від власника</button></div><div id="owner-totals" class="totals"></div><div id="owner-table-wrap" style="margin-top:12px"></div></div>`;qs('#btn-owner-add',panel).onclick=()=>openOwnerModal(panel);loadOwnerTable(panel);}
function openOwnerModal(root,row){const m=ce('div',{className:'modal-backdrop'});m.innerHTML=`<div class="modal"><h3>${row?'Редагувати':'Додати'} надходження</h3><div class="form"><label>Сума</label><input id="owner-amount" type="number" step="0.01" value="${row?row.row.amount:''}"><label>Коментар</label><textarea id="owner-comment" rows="3">${row?(row.row.comment||''):''}</textarea></div><div class="actions"><button class="btn" id="c">Скасувати</button><button class="btn primary" id="s">Зберегти</button></div></div>`;document.body.appendChild(m);qs('#c',m).onclick=()=>m.remove();qs('#s',m).onclick=async()=>{const amount=parseFloat(qs('#owner-amount',m).value||'0');const comment=qs('#owner-comment',m).value||'';try{await apiOwner(row?'update':'create',Object.assign(row?{id:row.row.id}:{},{amount,comment}));m.remove();loadOwnerTable(root);}catch(e){alert('Помилка');}};}
function delOwner(root,id){const m=ce('div',{className:'modal-backdrop'});m.innerHTML=`<div class="modal"><h3>Видалити запис?</h3><div class="actions"><button class="btn" id="n">Ні</button><button class="btn danger" id="y">Так</button></div></div>`;document.body.appendChild(m);qs('#n',m).onclick=()=>m.remove();qs('#y',m).onclick=async()=>{await apiOwner('delete',{id});m.remove();loadOwnerTable(root);};}
function renderTotals(root,t){const w=qs('#owner-totals',root),H=['Всього надходжень','Страховий фонд','ІТ','SHMAT BANK','Операційна діяльність','Благодійний фонд','Капітал власника','Капітал для інвестування'],V=[t.total,t.sf,t.it,t.sb,t.op,t.char,t.owner,t.invest].map(fmt);const tbl=ce('table',{className:'table'}),th=ce('thead'),trh=ce('tr');H.forEach(h=>{const thd=ce('th');thd.textContent=h;trh.appendChild(thd);});th.appendChild(trh);const tb=ce('tbody'),tr=ce('tr');V.forEach(v=>{const td=ce('td');td.textContent=v;td.className='money';tr.appendChild(td);});tb.appendChild(tr);tbl.appendChild(th);tbl.appendChild(tb);w.innerHTML='';w.appendChild(tbl);}
async function loadOwnerTable(root){const w=qs('#owner-table-wrap',root);w.textContent='Завантаження…';const data=await apiOwner('list',{});renderTotals(root,data.totals||{});const rows=data.items||[];if(!rows.length){w.textContent='Записів поки немає.';return;}const TH=['№','Дата запису','Сума','Страховий фонд (2%)','ІТ (9%)','SHMAT BANK (5%)','Операційна діяльність (6%)','Благодійний фонд (4%)','Капітал власника (10%)','Капітал для інвестування (64%)','Дії'];const tbl=ce('table',{className:'table'}),thead=ce('thead'),trh=ce('tr');TH.forEach(h=>{const th=ce('th');th.textContent=h;trh.appendChild(th);});thead.appendChild(trh);const tb=ce('tbody');rows.forEach((r,i)=>{const tr=ce('tr');const dt=new Date(r.row.created_at.replace(' ','T'));[i+1,dt.toLocaleString(),fmt(r.row.amount),fmt(r.split.sf),fmt(r.split.it),fmt(r.split.sb),fmt(r.split.op),fmt(r.split.char),fmt(r.split.owner),fmt(r.split.invest)].forEach(v=>{const td=ce('td');td.textContent=v;td.className='money';tr.appendChild(td);});const act=ce('td');act.className='row-actions';const e=ce('button',{className:'icon-btn'});e.textContent='✏️';e.onclick=()=>openOwnerModal(root,r);const d=ce('button',{className:'icon-btn'});d.textContent='🗑️';d.onclick=()=>delOwner(root,r.row.id);act.appendChild(e);act.appendChild(d);tr.appendChild(act);tb.appendChild(tr);});tbl.appendChild(thead);tbl.appendChild(tb);w.innerHTML='';w.appendChild(tbl);}
async function apiOwnerCap(a,d={}){const f=new URLSearchParams({action:a,csrf_token:window.__csrf});Object.entries(d).forEach(([k,v])=>f.append(k,v));const r=await fetch('/corpos-full-v2/public/api_owner_capital.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:f});if(!r.ok)throw 0;return r.json();}
function renderOwnerCapital(panel){panel.innerHTML=`<div class="card"><h3 style="margin-top:0">Капітал власника</h3><div id="oc-sources"></div><div id="oc-summary" style="margin-top:12px"></div><div style="margin:12px 0"><button id="wd-add" class="btn primary">Вивести капітал</button></div><div id="oc-history"></div></div>`;qs('#wd-add',panel).onclick=()=>openWdModal(panel);loadOwnerCap(panel);}
function openWdModal(root,row){const m=ce('div',{className:'modal-backdrop'});m.innerHTML=`<div class="modal"><h3>${row?'Редагувати':'Вивести'} капітал</h3><div class="form"><label>Сума</label><input id="wd-amount" type="number" step="0.01" value="${row?row.amount:''}"><label>Коментар</label><textarea id="wd-comment" rows="3">${row?(row.comment||''):''}</textarea></div><div class="actions"><button class="btn" id="c">Скасувати</button><button class="btn primary" id="s">ОК</button></div></div>`;document.body.appendChild(m);qs('#c',m).onclick=()=>m.remove();qs('#s',m).onclick=async()=>{const amount=parseFloat(qs('#wd-amount',m).value||'0');const comment=qs('#wd-comment',m).value||'';try{if(row)await apiOwnerCap('update_withdrawal',{id:row.id,amount,comment});else await apiOwnerCap('create_withdrawal',{amount,comment});m.remove();loadOwnerCap(root);}catch(e){alert('Помилка');}};}
function delWd(root,id){const m=ce('div',{className:'modal-backdrop'});m.innerHTML=`<div class="modal"><h3>Видалити запис?</h3><div class="actions"><button class="btn" id="n">Ні</button><button class="btn danger" id="y">Так</button></div></div>`;document.body.appendChild(m);qs('#n',m).onclick=()=>m.remove();qs('#y',m).onclick=async()=>{await apiOwnerCap('delete_withdrawal',{id});m.remove();loadOwnerCap(root);};}
async function loadOwnerCap(root){const d=await apiOwnerCap('summary',{});const s=d.sources||[],sum=d.summary||{},hist=d.history||[];const sw=qs('#oc-sources',root),H=s.map(x=>x.name),V=s.map(x=>fmt(x.amount));let tbl=ce('table',{className:'table'}),thead=ce('thead'),trh=ce('tr');H.forEach(h=>{const th=ce('th');th.textContent=h;trh.appendChild(th);});thead.appendChild(trh);let tb=ce('tbody'),tr=ce('tr');V.forEach(v=>{const td=ce('td');td.textContent=v;td.className='money';tr.appendChild(td);});tb.appendChild(tr);tbl.appendChild(thead);tbl.appendChild(tb);sw.innerHTML='';sw.appendChild(tbl);const smw=qs('#oc-summary',root),HH=['Загальне надходження капіталу','Виведено капіталу','Залишок капіталу'],VV=[sum.total_in,sum.withdrawn,sum.balance].map(fmt);tbl=ce('table',{className:'table'});thead=ce('thead');trh=ce('tr');HH.forEach(h=>{const th=ce('th');th.textContent=h;trh.appendChild(th);});thead.appendChild(trh);tb=ce('tbody');tr=ce('tr');VV.forEach(v=>{const td=ce('td');td.textContent=v;td.className='money';tr.appendChild(td);});tb.appendChild(tr);tbl.appendChild(thead);tbl.appendChild(tb);smw.innerHTML='';smw.appendChild(tbl);const hw=qs('#oc-history',root),HT=['Дата','Сума виведених коштів','Коментар','Дії'];tbl=ce('table',{className:'table'});thead=ce('thead');trh=ce('tr');HT.forEach(h=>{const th=ce('th');th.textContent=h;trh.appendChild(th);});thead.appendChild(trh);tb=ce('tbody');hist.forEach(r=>{const tr=ce('tr');const dt=new Date(r.created_at.replace(' ','T'));[dt.toLocaleString(),fmt(r.amount),r.comment||''].forEach(v=>{const td=ce('td');td.textContent=v;if(v===fmt(r.amount))td.className='money';tr.appendChild(td);});const act=ce('td');act.className='row-actions';const e=ce('button',{className:'icon-btn'});e.textContent='✏️';e.onclick=()=>openWdModal(root,r);const d=ce('button',{className:'icon-btn'});d.textContent='🗑️';d.onclick=()=>delWd(root,r.id);act.appendChild(e);act.appendChild(d);tr.appendChild(act);tb.appendChild(tr);});tbl.appendChild(thead);tbl.appendChild(tb);hw.innerHTML='';hw.appendChild(tbl);}

// === Operational Capital (Операційний капітал) ===
async function apiOperCap(action, data={}){
  const f = new URLSearchParams(Object.assign({action, csrf_token: window.__csrf_token||''}, data));
  const r = await fetch('/corpos-full-v2/public/api_operational_capital.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: f});
  if(!r.ok) throw 0; return r.json();
}

function renderOperationalCapital(root){
  if(!root) return;
  // initial load
  loadOperCap(root);
  // bind open modal
  const btn = root.querySelector('#opcap-btn');
  if(btn){ btn.onclick = ()=> openOperModal(root); }
}

async function loadOperCap(root){
  root.querySelector('#opcap-history').innerHTML = '<div class="card"><p>Завантаження…</p></div>';
  try{
    const data = await apiOperCap('summary',{});
    if(!data.ok) throw 0;
    const fmt = (x)=>Number(x).toLocaleString('uk-UA',{minimumFractionDigits:2,maximumFractionDigits:2});
    // fill sources (optional future)
    const map = {
      banks: 0, shmat: 0, statutory: 0, profit: 0, projects: 0
    };
    (data.sources||[]).forEach((s,idx)=>{
      // map by order
      const keys=['banks','shmat','statutory','profit','projects'];
      const k = keys[idx]; if(typeof map[k]!=='undefined') map[k] = s.amount||0;
    });
    Object.keys(map).forEach(k=>{
      const el = root.querySelector('[data-src="'+k+'"]'); if(el) el.textContent = fmt(map[k]);
    });

    // fill totals
    const ti = root.querySelector('#opcap-total-in');
    const wd = root.querySelector('#opcap-withdrawn');
    const bl = root.querySelector('#opcap-balance');
    if(ti) ti.textContent = fmt((data.summary||{}).total_in||0);
    if(wd) wd.textContent = fmt((data.summary||{}).withdrawn||0);
    if(bl) bl.textContent = fmt((data.summary||{}).balance||0);

    // history table
    const hist = data.history||[];
    const tbl = document.createElement('table'); tbl.className='table';
    const thead = document.createElement('thead'); const thr = document.createElement('tr');
    ['Дата','Сума виведених коштів','Коментар','Дії'].forEach(h=>{ const th=document.createElement('th'); th.textContent=h; thr.appendChild(th); });
    thead.appendChild(thr); tbl.appendChild(thead);
    const tb = document.createElement('tbody');
    if(hist.length===0){
      const tr=document.createElement('tr'); const td=document.createElement('td'); td.colSpan=4; td.textContent='Записів немає'; tr.appendChild(td); tb.appendChild(tr);
    }else{
      hist.forEach(row=>{
        const tr=document.createElement('tr');
        const dt = new Date(row.created_at||row.createdAt||Date.now());
        const dtd = isNaN(dt.getTime()) ? (row.created_at||'') : dt.toLocaleString('uk-UA');
        [dtd, fmt(row.amount||0), row.comment||''].forEach(v=>{ const td=document.createElement('td'); td.textContent=v; tr.appendChild(td); });
        const act=document.createElement('td');
        const del=document.createElement('button'); del.className='btn btn-sm'; del.textContent='🗑';
        del.onclick=()=>delOperWd(root, row.id);
        act.appendChild(del); tr.appendChild(act);
        tb.appendChild(tr);
      });
    }
    tbl.appendChild(tb);
    const w = root.querySelector('#opcap-history'); w.innerHTML=''; w.appendChild(tbl);
  }catch(e){
    root.querySelector('#opcap-history').innerHTML = '<div class="card"><p>Помилка завантаження</p></div>';
  }
}

function openOperModal(root){
  const m = document.createElement('div'); m.className='modal-backdrop';
  m.innerHTML = '<div class="modal"><h3>Вивести капітал</h3><div class="form">  <label>Сума</label><input id="oper-amount" type="number" step="0.01">  <label>Коментар</label><textarea id="oper-comment" rows="2"></textarea>  </div><div class="modal-actions">  <button class="btn" id="oper-cancel">Скасувати</button>  <button class="btn btn-primary" id="oper-save">Зберегти</button>  </div></div>';
  document.body.appendChild(m);
  m.querySelector('#oper-cancel').onclick=()=>m.remove();
  m.querySelector('#oper-save').onclick=async ()=>{
    const amount = parseFloat(m.querySelector('#oper-amount').value||'0');
    const comment = (m.querySelector('#oper-comment').value||'').trim();
    try{
      const r = await apiOperCap('create_withdrawal',{amount,comment});
      if(!r.ok) throw 0; m.remove(); loadOperCap(root);
    }catch(e){ alert('Помилка'); }
  };
}

async function delOperWd(root,id){
  if(!confirm('Видалити запис?')) return;
  try{ const r = await apiOperCap('delete_withdrawal',{id}); if(!r.ok) throw 0; loadOperCap(root);}catch(e){ alert('Помилка'); }
}


// Auto-init Operational Capital when its root node appears in DOM
(function(){
  let inited = false;
  const initIfReady = ()=>{
    if(inited) return;
    const el = document.getElementById('opcap-root');
    if(el){ inited=true; try{ renderOperationalCapital(el);}catch(e){} }
  };
  // try immediately
  initIfReady();
  // observe DOM changes
  const mo = new MutationObserver(()=>initIfReady());
  mo.observe(document.body, {childList:true, subtree:true});
  // also on hash change
  window.addEventListener('hashchange', ()=>setTimeout(initIfReady, 50));
})();
