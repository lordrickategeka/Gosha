/* ==========================================================================
   GarageHQ — behaviour layer
   Vanilla ES2019, no dependencies, no build step. Drive everything from
   data-attributes so the same JS works against Blade-rendered markup.

     <script src="garagehq.js" defer></script>

   Everything is namespaced under window.GarageHQ.
   ========================================================================== */
(function (window, document) {
  'use strict';

  /* ---------- Icons: lifted from resources/views/components/partials/sidebar.blade.php
     (Heroicons outline, 24×24, stroke-width 2). Render with GarageHQ.icon(name). ---- */
  var ICONS = {
    dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    workOrders:'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    washBay:   'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
    checkIn:   'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
    calendar:  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    bayStatus: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
    customers: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    staff:     'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    vehicles:  'M8 7h8m-8 4h8m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
    invoices:  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    payments:  'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    expenses:  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    commissions:'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z',
    stock:     'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    suppliers: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
    reports:   'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    roles:     'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    branches:  'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    templates: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    serviceTypes:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    settings:  'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    clock:     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    modules:   'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'
  };

  function icon(name, size) {
    var d = ICONS[name];
    if (!d) return '';
    var s = size || 16;
    return '<svg class="gh-icon" viewBox="0 0 24 24" width="' + s + '" height="' + s + '" fill="none" ' +
           'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
           '<path d="' + d + '"></path></svg>';
  }

  /* ---------- Module registry — mirrors the sidebar sections in the Laravel app ---- */
  var MODULES = [
    { key:'work',      name:'Work Orders', icon:'workOrders', route:'work-orders.index',
      items:[ {label:'All work orders', icon:'workOrders', route:'work-orders.index'},
              {label:'Check-in',        icon:'checkIn',    route:'work-orders.create'},
              {label:'Appointments',    icon:'calendar',   route:'appointments.index'},
              {label:'Calendar',        icon:'calendar',   route:'calendar'},
              {label:'Bay status',      icon:'bayStatus',  route:'bays.status'} ] },
    { key:'wash',      name:'Wash Bay',   icon:'washBay', route:'wash-orders.index',
      items:[ {label:'Queue',      icon:'washBay',   route:'wash-orders.index'},
              {label:'New wash',   icon:'checkIn',   route:'wash-orders.create'},
              {label:'Packages',   icon:'washBay',   route:'packages.index'},
              {label:'Bay status', icon:'bayStatus', route:'bays.status'} ] },
    { key:'customers', name:'Customers',  icon:'customers', route:'customers.index',
      items:[ {label:'Customers', icon:'customers', route:'customers.index'},
              {label:'Vehicles',  icon:'vehicles',  route:'vehicles.index'} ] },
    { key:'finance',   name:'Finance',    icon:'expenses', route:'invoices.index',
      items:[ {label:'Invoices',    icon:'invoices',    route:'invoices.index'},
              {label:'Quotations',  icon:'invoices',    route:'quotations.index'},
              {label:'Payments',    icon:'payments',    route:'payments.index'},
              {label:'Expenses',    icon:'expenses',    route:'expenses.index'},
              {label:'Commissions', icon:'commissions', route:'commissions.index'} ] },
    { key:'stock',     name:'Inventory',  icon:'stock', route:'inventory.index',
      items:[ {label:'All items', icon:'stock',     route:'inventory.index'},
              {label:'Low stock', icon:'stock',     route:'inventory.low-stock'},
              {label:'Movements', icon:'reports',   route:'inventory.movements'},
              {label:'Suppliers', icon:'suppliers', route:'suppliers.index'} ] },
    { key:'reports',   name:'Reports',    icon:'reports', route:'reports.sales',
      items:[ {label:'Sales',       icon:'reports', route:'reports.sales'},
              {label:'Operations',  icon:'reports', route:'reports.operations'},
              {label:'Inventory',   icon:'stock',   route:'reports.inventory'},
              {label:'Staff',       icon:'staff',   route:'reports.staff'} ] },
    { key:'hr',        name:'HR & Staff', icon:'staff', route:'users.index',
      items:[ {label:'Staff',                icon:'staff', route:'users.index'},
              {label:'Roles & permissions',  icon:'roles', route:'roles.index'},
              {label:'Attendance',           icon:'clock'} ] },
    { key:'settings',  name:'Settings',   icon:'settings', route:'settings',
      items:[ {label:'Branches',          icon:'branches',     route:'branches.index'},
              {label:'Service types',     icon:'serviceTypes', route:'service-types.index'},
              {label:'Service categories',icon:'serviceTypes', route:'service-categories.index'},
              {label:'Templates',         icon:'templates',    route:'templates.index'},
              {label:'Settings',          icon:'settings',     route:'settings'} ] }
  ];

  /* ---------- Formatting ---------- */
  function ugx(n) {
    return 'UGX ' + Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
  }
  function ugxShort(n) {
    n = Number(n || 0);
    if (n >= 1e9) return 'UGX ' + (n / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
    if (n >= 1e6) return 'UGX ' + (n / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
    if (n >= 1e3) return 'UGX ' + Math.round(n / 1e3) + 'K';
    return ugx(n);
  }
  function initials(name) {
    return String(name || '').trim().split(/\s+/).map(function (w) { return w[0]; })
      .slice(0, 2).join('').toUpperCase();
  }

  /* ---------- Status vocabulary ---------- */
  var WORK_ORDER_STAGES = ['Checked in', 'Diagnosis', 'Awaiting OK', 'In progress', 'QC', 'Ready'];
  var WASH_STAGES = ['Waiting', 'Washing', 'Drying & QC', 'Ready to release'];

  var BADGE_TONE = {
    'Checked in':'', 'Diagnosis':'info', 'Awaiting OK':'warning', 'In progress':'primary',
    'Parts':'warning', 'QC':'info', 'Ready':'success',
    'Paid':'success', 'Unpaid':'', 'Part paid':'warning', 'Overdue':'error',
    'In stock':'success', 'Low':'warning', 'Reorder':'error',
    'Waiting':'warning', 'Washing':'info', 'Drying & QC':'success'
  };
  function badgeClass(status) {
    var tone = BADGE_TONE[status];
    return 'gh-badge' + (tone ? ' gh-badge--' + tone : '');
  }
  function nextStage(list, current) {
    var i = list.indexOf(current);
    return i < 0 || i >= list.length - 1 ? current : list[i + 1];
  }

  /* ---------- Screen routing (prototype / SPA-lite) ----------
     <button data-goto="jobs">   →   shows [data-screen="jobs"]
     Deep-links via #jobs, restores on reload.                                */
  function showScreen(name) {
    var screens = document.querySelectorAll('[data-screen]');
    if (!screens.length) return;
    var found = false;
    Array.prototype.forEach.call(screens, function (el) {
      var on = el.getAttribute('data-screen') === name;
      el.classList.toggle('is-active', on);
      if (on) found = true;
    });
    if (!found) return;
    Array.prototype.forEach.call(document.querySelectorAll('[data-goto]'), function (el) {
      el.classList.toggle('is-active', el.getAttribute('data-goto') === name);
    });
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '#' + name);
    }
    document.dispatchEvent(new CustomEvent('gh:screen', { detail: { screen: name } }));
  }

  /* ---------- Behaviours ---------- */

  // Filter chips: <div data-filter-group="status"><button data-filter="Ready">
  // Rows opt in with data-filter-value="Ready".
  function bindFilters(root) {
    Array.prototype.forEach.call(root.querySelectorAll('[data-filter-group]'), function (group) {
      group.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-filter]');
        if (!btn || !group.contains(btn)) return;
        var value = btn.getAttribute('data-filter');
        Array.prototype.forEach.call(group.querySelectorAll('[data-filter]'), function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        var scope = document.querySelector(group.getAttribute('data-filter-target') || '') || document;
        Array.prototype.forEach.call(scope.querySelectorAll('[data-filter-value]'), function (row) {
          var match = value === '*' || row.getAttribute('data-filter-value') === value;
          row.style.display = match ? '' : 'none';
        });
        document.dispatchEvent(new CustomEvent('gh:filter', { detail: { group: group.getAttribute('data-filter-group'), value: value } }));
      });
    });
  }

  // Clickable table rows: <tr data-href="/work-orders/1042">
  function bindRowLinks(root) {
    root.addEventListener('click', function (e) {
      var row = e.target.closest('[data-href]');
      if (!row) return;
      if (e.target.closest('button, a, input, select')) return;
      var href = row.getAttribute('data-href');
      if (/^#/.test(href)) showScreen(href.slice(1)); else window.location.href = href;
    });
  }

  // Advance a work order: <button data-advance-status data-status="In progress">
  function bindStatusAdvance(root) {
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-advance-status]');
      if (!btn) return;
      var current = btn.getAttribute('data-status');
      var next = nextStage(WORK_ORDER_STAGES, current);
      btn.setAttribute('data-status', next);
      btn.textContent = next === 'Ready' ? 'Release vehicle' : 'Move to ' + nextStage(WORK_ORDER_STAGES, next);
      var badge = document.querySelector(btn.getAttribute('data-status-target') || '');
      if (badge) { badge.textContent = next; badge.className = badgeClass(next); }
      var track = document.querySelector(btn.getAttribute('data-track-target') || '');
      if (track) paintTrack(track, WORK_ORDER_STAGES.indexOf(next));
      document.dispatchEvent(new CustomEvent('gh:status', { detail: { status: next } }));
    });
  }

  function paintTrack(track, doneIndex) {
    Array.prototype.forEach.call(track.querySelectorAll('.gh-track__step'), function (step, i) {
      step.classList.toggle('is-done', i < doneIndex);
      step.classList.toggle('is-current', i === doneIndex);
    });
  }

  // Wash board: click a card to move it one column right.
  function bindBoard(root) {
    root.addEventListener('click', function (e) {
      var card = e.target.closest('.gh-board__card');
      if (!card) return;
      var col = card.closest('.gh-board__col');
      var next = col && col.nextElementSibling;
      if (!next || !next.classList.contains('gh-board__col')) return;
      next.appendChild(card);
      recountBoard(root);
      document.dispatchEvent(new CustomEvent('gh:wash-stage', {
        detail: { plate: card.getAttribute('data-plate'), stage: next.getAttribute('data-stage') }
      }));
    });
    recountBoard(root);
  }
  function recountBoard(root) {
    Array.prototype.forEach.call(root.querySelectorAll('.gh-board__col'), function (col) {
      var badge = col.querySelector('.gh-board__count');
      if (badge) badge.textContent = col.querySelectorAll('.gh-board__card').length;
    });
  }

  // Walk-around checklist toggles.
  function bindChecks(root) {
    root.addEventListener('click', function (e) {
      var check = e.target.closest('.gh-check');
      if (!check) return;
      var on = check.classList.toggle('is-checked');
      var box = check.querySelector('.gh-check__box');
      if (box) box.textContent = on ? '✓' : '';
      var input = check.querySelector('input[type=checkbox]');
      if (input) input.checked = on;
    });
  }

  // Record a payment: <button data-pay="mobile-money" data-invoice="INV-2291">
  function bindPayments(root) {
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-pay]');
      if (!btn) return;
      var invoice = btn.getAttribute('data-invoice');
      var badge = document.querySelector('[data-invoice-status="' + invoice + '"]');
      if (badge) { badge.textContent = 'Paid'; badge.className = badgeClass('Paid'); }
      document.dispatchEvent(new CustomEvent('gh:payment', {
        detail: { invoice: invoice, method: btn.getAttribute('data-pay') }
      }));
    });
  }

  // Segmented range switch (Today / This week / This month).
  function bindSegmented(root) {
    Array.prototype.forEach.call(root.querySelectorAll('.gh-segmented'), function (seg) {
      seg.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        Array.prototype.forEach.call(seg.querySelectorAll('button'), function (b) {
          b.classList.toggle('is-active', b === btn);
        });
        document.dispatchEvent(new CustomEvent('gh:range', { detail: { range: btn.dataset.range || btn.textContent.trim() } }));
      });
    });
  }

  function bindGoto(root) {
    root.addEventListener('click', function (e) {
      var el = e.target.closest('[data-goto]');
      if (!el) return;
      showScreen(el.getAttribute('data-goto'));
    });
  }

  /* ---------- Init ---------- */
  function init(root) {
    root = root || document;
    bindGoto(root);
    bindFilters(root);
    bindRowLinks(root);
    bindStatusAdvance(root);
    bindChecks(root);
    bindPayments(root);
    bindSegmented(root);
    Array.prototype.forEach.call(root.querySelectorAll('.gh-board'), bindBoard);

    // hydrate any <i data-icon="washBay"> placeholders
    Array.prototype.forEach.call(root.querySelectorAll('[data-icon]'), function (el) {
      el.innerHTML = icon(el.getAttribute('data-icon'), Number(el.getAttribute('data-icon-size')) || 16);
    });

    var start = (window.location.hash || '').replace('#', '');
    if (start) showScreen(start);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { init(); });
  } else {
    init();
  }

  window.GarageHQ = {
    ICONS: ICONS, MODULES: MODULES,
    WORK_ORDER_STAGES: WORK_ORDER_STAGES, WASH_STAGES: WASH_STAGES,
    icon: icon, ugx: ugx, ugxShort: ugxShort, initials: initials,
    badgeClass: badgeClass, nextStage: nextStage,
    showScreen: showScreen, init: init
  };
})(window, document);
