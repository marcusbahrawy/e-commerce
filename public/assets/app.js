// Motorleaks — core + typeahead + megamenu
(function () {
  var BASE = (typeof window.APP_BASE === 'string' ? window.APP_BASE : '') || '';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  // Typeahead: header search
  function initTypeahead() {
    var input = document.getElementById('header-search');
    var panel = document.getElementById('header-typeahead');
    if (!input || !panel) return;

    var debounceTimer;
    var lastQuery = '';

    function hide() {
      panel.hidden = true;
      panel.innerHTML = '';
    }

    function show(items) {
      if (!items.length) {
        hide();
        return;
      }
      panel.innerHTML = items.map(function (item) {
        return '<a href="' + escapeAttr(item.url) + '" class="header__typeahead-item">' + escapeHtml(item.title) + '</a>';
      }).join('');
      panel.hidden = false;
    }

    function escapeHtml(s) {
      var div = document.createElement('div');
      div.textContent = s;
      return div.innerHTML;
    }
    function escapeAttr(s) {
      return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function fetchSuggestions(q) {
      if (q.length < 2) {
        hide();
        return;
      }
      var url = BASE + '/api/sok-forslag?q=' + encodeURIComponent(q);
      fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.items && lastQuery === q) show(data.items);
        })
        .catch(function () { if (lastQuery === q) hide(); });
    }

    input.addEventListener('input', function () {
      lastQuery = (input.value || '').trim();
      clearTimeout(debounceTimer);
      if (lastQuery.length < 2) {
        hide();
        return;
      }
      debounceTimer = setTimeout(function () { fetchSuggestions(lastQuery); }, 200);
    });

    input.addEventListener('blur', function () {
      setTimeout(hide, 150);
    });

    input.addEventListener('focus', function () {
      if (lastQuery.length >= 2) fetchSuggestions(lastQuery);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') hide();
    });
  }

  // Megamenu: categories dropdown
  function initMegamenu() {
    var btn = document.querySelector('[data-dropdown="categories"]');
    var panel = document.getElementById('dropdown-categories');
    if (!btn || !panel) return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var open = panel.hidden;
      panel.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
      if (!panel.contains(e.target) && e.target !== btn) {
        panel.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  ready(function () {
    initTypeahead();
    initMegamenu();
  });
})();
