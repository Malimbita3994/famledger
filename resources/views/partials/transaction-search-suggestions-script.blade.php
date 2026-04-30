{{-- SweetAlert2 search modal for [data-fl-open-search-modal] + [data-fl-transaction-suggest] (navbar + search page). --}}
@once
@push('scripts')
<script>
(function () {
  var L = {
    searching: {!! json_encode(__('Searching…')) !!},
    noSuggestions: {!! json_encode(__('No suggestions')) !!},
    workspace: {!! json_encode(__('Settings & workspace')) !!},
    cat: {!! json_encode(__('Categories')) !!},
    people: {!! json_encode(__('People')) !!},
    recent: {!! json_encode(__('Recent')) !!},
    tx: {!! json_encode(__('Transactions')) !!},
    income: {!! json_encode(__('Income')) !!},
    expense: {!! json_encode(__('Expense')) !!},
    ledger: {!! json_encode(__('Ledger')) !!},
    title: {!! json_encode(__('Search')) !!},
    placeholder: {!! json_encode(__('Search settings, people, categories, ledger…')) !!},
    emptyHint: {!! json_encode(__('Type at least two characters to see suggestions.')) !!},
    footer: {!! json_encode(__('FamLedger search · Settings, categories, people, recents, and indexed transactions.')) !!},
  };

  var Swal = window.Swal;
  var modalInput = null;
  var modalResults = null;
  var activeInput = null;
  var activeUrl = '';
  var searchSubmitUrl = '';
  var searchSubmitFragment = '';
  var debounceT = null;

  function escAttr(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;');
  }

  function isSearchSwalOpen() {
    return Swal && Swal.isVisible() && Swal.getPopup() && Swal.getPopup().querySelector('#fl-sw-search-input');
  }

  function syncTriggerFromModal() {
    if (activeInput && modalInput) activeInput.value = modalInput.value;
  }

  function closeSearchModal() {
    if (isSearchSwalOpen()) Swal.close();
  }

  function submitActive() {
    syncTriggerFromModal();
    var q = modalInput ? modalInput.value.trim() : '';
    if (q === '') return;
    var goUrl = searchSubmitUrl;
    var inp = activeInput;
    closeSearchModal();
    if (inp) {
      inp.value = q;
      var form = inp.closest('form');
      if (form) {
        form.submit();
        return;
      }
    }
    if (goUrl) {
      var join = goUrl.indexOf('?') >= 0 ? '&' : '?';
      var url = goUrl + join + 'q=' + encodeURIComponent(q);
      if (searchSubmitFragment) {
        url += searchSubmitFragment;
      }
      window.location.href = url;
    }
  }

  function fmtDate(iso) {
    if (!iso || typeof iso !== 'string') return '—';
    try {
      var d = new Date(iso.indexOf('T') > -1 ? iso : iso + 'T12:00:00');
      if (isNaN(d.getTime())) return iso;
      return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch (e) {
      return iso;
    }
  }

  function fmtMoney(n, cur) {
    if (n == null || n === '') return '';
    var num = typeof n === 'number' ? n : parseFloat(String(n).replace(/,/g, ''));
    if (isNaN(num)) return '';
    var c = cur ? String(cur).toUpperCase() : '';
    return (c ? c + '\u00a0' : '') + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function strItem(x) {
    if (x == null) return '';
    if (typeof x === 'string') return x;
    if (typeof x === 'object' && x.text != null) return String(x.text);
    return String(x);
  }

  function renderRichResults(data) {
    if (!modalResults || !document.body.contains(modalResults)) return;
    modalResults.innerHTML = '';
    var frag = document.createDocumentFragment();

    function sectionHeader(label) {
      var h = document.createElement('div');
      h.className = 'fl-sw-search-section';
      h.textContent = label;
      return h;
    }

    function addSection(label, nodes) {
      if (!nodes.length) return;
      frag.appendChild(sectionHeader(label));
      nodes.forEach(function (n) {
        frag.appendChild(n);
      });
    }

    var settingsNodes = [];
    (data.settings || []).forEach(function (row) {
      var r = row && typeof row === 'object' ? row : {};
      var title = strItem(r.title);
      var sub = r.subtitle != null ? String(r.subtitle) : '';
      var url = r.url != null ? String(r.url) : '';
      if (!title.trim() || !url.trim()) return;
      settingsNodes.push(buildLinkRow(title, sub, url));
    });
    addSection(L.workspace, settingsNodes);

    var catNodes = [];
    (data.categories || []).forEach(function (name) {
      var t = strItem(name);
      if (!t.trim()) return;
      catNodes.push(buildSimpleRow(t, L.cat, t));
    });
    addSection(L.cat, catNodes);

    var peopleNodes = [];
    (data.persons || []).forEach(function (name) {
      var t = strItem(name);
      if (!t.trim()) return;
      peopleNodes.push(buildSimpleRow(t, L.people, t));
    });
    addSection(L.people, peopleNodes);

    var recentNodes = [];
    (data.recent_searches || []).forEach(function (q) {
      var t = strItem(q);
      if (!t.trim()) return;
      recentNodes.push(buildSimpleRow(t, L.recent, t));
    });
    addSection(L.recent, recentNodes);

    var txNodes = [];
    (data.transactions || []).forEach(function (x) {
      var tx = x && typeof x === 'object' ? x : {};
      var title = strItem(tx.text != null ? tx.text : tx);
      if (!title.trim()) return;
      var meta = tx.meta && typeof tx.meta === 'object' ? tx.meta : {};
      var type = String(meta.type || '');
      var typeLabel = type === 'income' ? L.income : type === 'expense' ? L.expense : type;
      var parts = [];
      if (meta.category) parts.push(String(meta.category));
      if (meta.person) parts.push(String(meta.person));
      parts.push(fmtDate(String(meta.date || '')));
      var sub = parts.filter(Boolean).join(' · ');
      var money = fmtMoney(meta.amount, meta.currency_code);
      var third = document.createElement('div');
      third.textContent = [L.ledger, typeLabel, money].filter(Boolean).join(' · ');
      txNodes.push(buildRichRow(title, sub, third, title));
    });
    addSection(L.tx, txNodes);

    if (!frag.childNodes.length) {
      var empty = document.createElement('div');
      empty.className = 'fl-sw-search-empty';
      empty.textContent = L.noSuggestions;
      modalResults.appendChild(empty);
      return;
    }
    modalResults.appendChild(frag);
  }

  function buildLinkRow(title, subtitle, url) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'fl-sw-search-hit';
    var t1 = document.createElement('div');
    t1.className = 'fl-sw-search-title';
    t1.textContent = title;
    var t2 = document.createElement('div');
    t2.className = 'fl-sw-search-meta';
    t2.textContent = subtitle && subtitle.trim() ? subtitle : L.workspace;
    btn.appendChild(t1);
    btn.appendChild(t2);
    btn.addEventListener('click', function () {
      window.location.href = url;
    });
    return btn;
  }

  function buildSimpleRow(title, badge, value) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'fl-sw-search-hit';
    var t1 = document.createElement('div');
    t1.className = 'fl-sw-search-title';
    t1.textContent = title;
    var t2 = document.createElement('div');
    t2.className = 'fl-sw-search-meta';
    t2.textContent = badge;
    btn.appendChild(t1);
    btn.appendChild(t2);
    btn.addEventListener('click', function () {
      if (modalInput) modalInput.value = value;
      submitActive();
    });
    return btn;
  }

  function buildRichRow(title, subtitle, extraEl, value) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'fl-sw-search-hit';
    var t1 = document.createElement('div');
    t1.className = 'fl-sw-search-title';
    t1.textContent = title;
    var t2 = document.createElement('div');
    t2.className = 'fl-sw-search-meta';
    t2.textContent = subtitle;
    btn.appendChild(t1);
    btn.appendChild(t2);
    if (extraEl) {
      extraEl.className = (extraEl.className ? extraEl.className + ' ' : '') + 'fl-sw-search-ledger';
      btn.appendChild(extraEl);
    }
    btn.addEventListener('click', function () {
      if (modalInput) modalInput.value = value;
      submitActive();
    });
    return btn;
  }

  function setLoading() {
    if (!modalResults || !document.body.contains(modalResults)) return;
    modalResults.innerHTML =
      '<div class="fl-sw-search-loading"><span class="fl-sw-search-spinner" aria-hidden="true"></span><span>' +
      escAttr(L.searching) +
      '</span></div>';
  }

  function loadSuggestions() {
    if (!activeUrl || !modalInput) return;
    var q = modalInput.value.trim();
    if (q.length < 2) return;
    setLoading();
    fetch(activeUrl + '?q=' + encodeURIComponent(q), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!isSearchSwalOpen()) return;
        renderRichResults(data);
      })
      .catch(function () {
        if (!modalResults || !document.body.contains(modalResults)) return;
        modalResults.innerHTML =
          '<div class="fl-sw-search-empty fl-sw-search-error">' + escAttr(L.noSuggestions) + '</div>';
      });
  }

  function scheduleLoad() {
    clearTimeout(debounceT);
    debounceT = setTimeout(loadSuggestions, 200);
  }

  function buildSearchHtml() {
    return (
      '<div class="fl-sw-search-inner">' +
      '<input id="fl-sw-search-input" type="search" autocomplete="off" dir="ltr" ' +
      'class="fl-sw-search-input" placeholder="' +
      escAttr(L.placeholder) +
      '" />' +
      '<div id="fl-sw-search-results" class="fl-sw-search-results-box"></div>' +
      '<p class="fl-sw-search-footer">' +
      escAttr(L.footer) +
      '</p></div>'
    );
  }

  function wirePopupInputs() {
    var popup = Swal.getPopup();
    if (!popup) return;
    modalInput = popup.querySelector('#fl-sw-search-input');
    modalResults = popup.querySelector('#fl-sw-search-results');
    if (!modalInput || !modalResults) return;

    modalInput.addEventListener('input', function () {
      syncTriggerFromModal();
      var q = modalInput.value.trim();
      if (q.length < 2) {
        modalResults.innerHTML =
          '<div class="fl-sw-search-empty">' + escAttr(L.emptyHint) + '</div>';
        return;
      }
      setLoading();
      scheduleLoad();
    });

    modalInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        submitActive();
      }
    });

    requestAnimationFrame(function () {
      modalInput.focus();
      var len = modalInput.value.length;
      try {
        modalInput.setSelectionRange(len, len);
      } catch (e) {}
    });
  }

  function openSearchModal(opts) {
    if (!Swal) return;
    opts = opts || {};
    var nextUrl = opts.suggestionsUrl || '';
    var nextSubmit = opts.searchSubmitUrl || '';
    var nextFragment = opts.searchFragment || '';
    var nextInput = opts.activeInput || null;
    var initial = opts.initialQuery != null ? String(opts.initialQuery) : '';

    activeUrl = nextUrl;
    searchSubmitUrl = nextSubmit;
    searchSubmitFragment = nextFragment;
    activeInput = nextInput;

    if (isSearchSwalOpen()) {
      activeUrl = nextUrl;
      searchSubmitUrl = nextSubmit;
      searchSubmitFragment = nextFragment;
      activeInput = nextInput;
      modalInput = Swal.getPopup().querySelector('#fl-sw-search-input');
      modalResults = Swal.getPopup().querySelector('#fl-sw-search-results');
      if (modalInput) {
        modalInput.value = initial;
        modalInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      return;
    }

    var hintHtml = '<div class="fl-sw-search-empty">' + escAttr(L.emptyHint) + '</div>';

    Swal.fire({
      title: L.title,
      html: buildSearchHtml(),
      showConfirmButton: false,
      showCloseButton: true,
      allowOutsideClick: true,
      allowEscapeKey: true,
      focusConfirm: false,
      scrollbarPadding: true,
      customClass: {
        popup: 'fl-sw-search-popup',
        container: 'fl-sw-search-z',
      },
      didOpen: function () {
        wirePopupInputs();
        if (!modalInput || !modalResults) return;
        modalInput.value = initial;
        var q = modalInput.value.trim();
        if (q.length < 2) {
          modalResults.innerHTML = hintHtml;
        } else {
          setLoading();
          scheduleLoad();
        }
      },
      didClose: function () {
        clearTimeout(debounceT);
        modalInput = null;
        modalResults = null;
        activeInput = null;
        activeUrl = '';
        searchSubmitUrl = '';
        searchSubmitFragment = '';
      },
    });
  }

  function openSearchFromNav(btn) {
    openSearchModal({
      suggestionsUrl: btn.getAttribute('data-suggestions-url') || '',
      searchSubmitUrl: btn.getAttribute('data-search-url') || '',
      searchFragment: btn.getAttribute('data-search-fragment') || '',
      activeInput: null,
      initialQuery: '',
    });
  }

  function wire(container) {
    if (container.dataset.flSuggestInit) return;
    container.dataset.flSuggestInit = '1';
    var input = container.querySelector('input[data-suggestions-url]');
    var url = input && input.getAttribute('data-suggestions-url');
    if (!input || !url) return;

    input.addEventListener('input', function () {
      var q = input.value.trim();
      if (q.length < 2) {
        if (isSearchSwalOpen()) Swal.close();
        return;
      }
      var f = input.closest('form');
      openSearchModal({
        suggestionsUrl: url,
        searchSubmitUrl: f && f.action ? f.action : '',
        activeInput: input,
        initialQuery: q,
      });
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isSearchSwalOpen()) Swal.close();
    });
  }

  function init() {
    document.querySelectorAll('[data-fl-open-search-modal]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openSearchFromNav(btn);
      });
    });
    document.querySelectorAll('[data-fl-transaction-suggest]').forEach(wire);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>
@endpush
@endonce
