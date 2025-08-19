<?php
require __DIR__ . '/../auth.php';
if (!is_authenticated()) {
  http_response_code(401);
  echo '<div class="card"><p>Потрібен вхід</p></div>';
  exit;
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$v = $_GET['v'] ?? '';

// --- РУХ КАПІТАЛУ ---
if ($v === 'corp/money_flow') {
  echo '<div class="card"><h2 style="margin-top:0">Рух капіталу</h2>
<div id="mf-tabs" class="mf-tabs">
  <div class="mf-tab active" data-mf-tab="for_distribution">Надходження капіталу для розподілу</div>
  <div class="mf-tab" data-mf-tab="owner">Надходження капіталу від власника</div>
  <div class="mf-tab" data-mf-tab="bank">Надходження капіталу від банку</div>
  <div class="mf-tab" data-mf-tab="shmatbank">Shmat Bank</div>
  <div class="mf-tab" data-mf-tab="investors">Надходження від інвесторів</div>
  <div class="mf-tab" data-mf-tab="owner_capital">Капітал власника</div>
</div>
<div id="mf-panel"></div>
</div>';

// Ініціалізація вкладок (покладаємось на існуючі стилі)
  echo '<script>
  (function(){
    window.__csrf = ' . json_encode($_SESSION['csrf_token']) . ';
    function qs(s, r){ return (r||document).querySelector(s); }
    var tabs = qs("#mf-tabs"); var panel = qs("#mf-panel");
    if (!tabs || !panel) return;

    function setActive(name){
      tabs.querySelectorAll(".mf-tab").forEach(function(t){
        t.classList.toggle("active", t.getAttribute("data-mf-tab")===name);
      });
    }

    function renderSection(name){
      // Заглушка: справжній рендер має бути у твоєму app.js; тут мінімально щоб відкривалось
      panel.innerHTML = "<div class=\"card\"><p>Секція: " + name + "</p></div>";
    }

    tabs.addEventListener("click", function(e){
      var t = e.target.closest(".mf-tab"); if(!t) return;
      var name = t.getAttribute("data-mf-tab");
      setActive(name); renderSection(name);
    });

    // Початковий таб
    renderSection("for_distribution");
  })();
  </script>';
  exit;
}

// --- КАПІТАЛ ВЛАСНИКА (плейсхолдер незмінний) ---
if ($v === 'corp/owner_capital') {
  echo '<div class="card"><h2>Капітал власника</h2><div id="oc-wrap"></div></div>';
  echo '<script>(function(){ window.__csrf = ' . json_encode($_SESSION['csrf_token']) . '; })();</script>';
  exit;
}

// --- ОПЕРАЦІЙНИЙ КАПІТАЛ ---
if ($v === 'corp/operational_capital') {
  ?>
  <div class="card">
    <h2 style="margin-top:0">Операційний капітал</h2>
    <style>
      .opcap .table-block { display:block; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff;}
      .opcap .table-row { display:grid; grid-template-columns: repeat(5, 1fr); border-bottom:1px solid #e5e7eb; }
      .opcap .table-row:last-child { border-bottom:none; }
      .opcap .table-header { background:#f3f4f6; font-weight:600; color:#374151;}
      .opcap .table-values { background:#ffffff; color:#111827;}
      .opcap .cell { padding:12px 14px; display:flex; align-items:center; }
      @media (max-width:1100px){ .opcap .table-row { grid-template-columns:1fr; } }
    </style>

    <div id="opcap-root" class="opcap">
      <div class="table-block">
        <div class="table-row table-header">
          <div class="cell">Надходження капіталу від інвестицій банків</div>
          <div class="cell">Надходження капіталу від інвестицій SHMAT BANK</div>
          <div class="cell">Надходження капіталу від інвестицій статутного капіталу</div>
          <div class="cell">Надходження капіталу від прибутку корпорації</div>
          <div class="cell">Надходження капіталу від проектів корпорації</div>
        </div>
        <div class="table-row table-values">
          <div class="cell" data-src="banks">0,00</div>
          <div class="cell" data-src="shmat">0,00</div>
          <div class="cell" data-src="statutory">0,00</div>
          <div class="cell" data-src="profit">0,00</div>
          <div class="cell" data-src="projects">0,00</div>
        </div>
      </div>

      <div class="table-block" style="margin-top:24px;">
        <div class="table-row table-header" style="grid-template-columns: repeat(3, 1fr);">
          <div class="cell">Загальне надходження капіталу</div>
          <div class="cell">Виведено капіталу</div>
          <div class="cell">Залишок капіталу</div>
        </div>
        <div class="table-row table-values" style="grid-template-columns: repeat(3, 1fr);">
          <div class="cell" id="opcap-total-in">0,00</div>
          <div class="cell" id="opcap-withdrawn">0,00</div>
          <div class="cell" id="opcap-balance">0,00</div>
        </div>
      </div>

      <div style="margin-top:18px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-weight:600;">Історія виведення капіталу</div>
        <button class="btn btn-primary" id="opcap-btn">Вивести капітал</button>
      </div>
      <div id="opcap-history" style="margin-top:8px"></div>
    </div>
  </div>

  <script>
  (function(){
    var apiUrl = 'api_operational_capital.php';
    var csrf = <?php echo json_encode($_SESSION['csrf_token']); ?>;

    function formatMoney(v){
      try{ return (Number(v)||0).toLocaleString('uk-UA',{minimumFractionDigits:2, maximumFractionDigits:2}); }
      catch(e){ return (Number(v)||0).toFixed(2); }
    }

    function el(tag, attrs, children){
      var n = document.createElement(tag);
      if(attrs){ Object.keys(attrs).forEach(function(k){ if(k==='class') n.className=attrs[k]; else if(k==='html') n.innerHTML=attrs[k]; else n.setAttribute(k, attrs[k]); }); }
      (children||[]).forEach(function(c){ if(typeof c==='string'){ n.appendChild(document.createTextNode(c)); } else if(c) n.appendChild(c); });
      return n;
    }

    function renderHistory(list){
      var wrap = document.getElementById('opcap-history');
      if(!wrap){ return; }
      if(!list || !list.length){ wrap.innerHTML = '<div class="card"><p>Записів поки немає.</p></div>'; return; }
      var tbl = el('table', {class:'table table-striped'});
      var thead = el('thead', null, [ el('tr', null, [ el('th', null, ['ID']), el('th', null, ['Дата']), el('th', null, ['Сума']), el('th', null, ['Коментар']), el('th', null, ['Дії']) ]) ]);
      var tbody = el('tbody');
      list.forEach(function(r){
        var tr = el('tr', null, [
          el('td', null, [String(r.id)]),
          el('td', null, [r.created_at ? String(r.created_at) : '']),
          el('td', null, [formatMoney(r.amount)]),
          el('td', null, [r.comment || '']),
          el('td', null, [ (function(){
            var b = el('button', {class:'btn btn-sm btn-danger'}, ['Видалити']);
            b.addEventListener('click', function(){
              if(!confirm('Видалити запис #' + r.id + '?')) return;
              fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'delete_withdrawal', id:r.id, csrf_token: csrf}) })
                .then(r=>r.json()).then(function(j){ if(j && j.ok){ loadSummary(); } else { alert(j.error||'Помилка'); } })
                .catch(function(){ alert('Помилка мережі'); });
            });
            return b;
          })() ])
        ]);
        tbody.appendChild(tr);
      });
      tbl.appendChild(thead); tbl.appendChild(tbody);
      wrap.innerHTML = ''; wrap.appendChild(tbl);
    }

    function loadSummary(){
      fetch(apiUrl+'?action=summary&csrf_token='+encodeURIComponent(csrf))
        .then(r=>r.json()).then(function(j){
          if(!j || !j.ok) throw new Error(j && j.error ? j.error : 'Bad response');
          document.getElementById('opcap-total-in').textContent = formatMoney(j.summary.total_in);
          document.getElementById('opcap-withdrawn').textContent = formatMoney(j.summary.withdrawn);
          document.getElementById('opcap-balance').textContent = formatMoney(j.summary.balance);
          renderHistory(j.history||[]);
        })
        .catch(function(e){ console.error(e); });
    }

    function openModal(){
      var dlg = document.createElement('div');
      dlg.innerHTML = '<div class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;z-index:9999;">  <div class="modal" style="background:#fff;border-radius:10px;min-width:360px;max-width:90vw;padding:16px;">    <h3 style="margin-top:0">Вивести капітал</h3>    <div class="form-group"><label>Сума</label><input type="number" step="0.01" id="opcap-amount" class="form-control" style="width:100%"></div>    <div class="form-group"><label>Коментар</label><textarea id="opcap-comment" class="form-control" style="width:100%"></textarea></div>    <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">      <button class="btn btn-secondary" id="opcap-cancel">Скасувати</button>      <button class="btn btn-primary" id="opcap-save">Зберегти</button>    </div>  </div></div>';
      document.body.appendChild(dlg.firstChild);
      var root = document.body.lastChild;
      root.querySelector('#opcap-cancel').addEventListener('click', function(){ document.body.removeChild(root); });
      root.querySelector('#opcap-save').addEventListener('click', function(){
        var amount = parseFloat(root.querySelector('#opcap-amount').value||'0');
        var comment = String(root.querySelector('#opcap-comment').value||'');
        if(!(amount>0)){ alert('Вкажіть суму'); return; }
        fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'create_withdrawal', amount: amount, comment: comment, csrf_token: csrf}) })
          .then(r=>r.json()).then(function(j){
            if(j && j.ok){ document.body.removeChild(root); loadSummary(); }
            else { alert(j.error||'Помилка'); }
          })
          .catch(function(){ alert('Помилка мережі'); });
      });
    }

    var btn = document.getElementById('opcap-btn');
    if (btn) btn.addEventListener('click', openModal);
    loadSummary();
  })();
  </script>
  <?php
  exit;
}

// --- Якщо розділ не знайдено
echo '<div class="card"><p>Невідомий розділ</p></div>';
