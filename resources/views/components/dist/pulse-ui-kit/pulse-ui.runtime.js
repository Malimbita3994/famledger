/**
 * Pulse UI — behaviors for the Pulse kit (header, dashboard reveal, hash router,
 * login fields, Lottie helpers). Requires lottie-web if you use Lottie helpers.
 *
 *   PulseUI.init(options)     // run all with defaults (safe if nodes missing)
 *   PulseUI.initHeader(opts)
 *   PulseUI.initDashboardReady(opts)
 *   PulseUI.initDashboardLottie(opts)
 *   PulseUI.initDataDashboardGraphLotties(opts)
 *   PulseUI.initLandingPageLottie(opts)
 *   PulseUI.initSidebarAppIcon(opts)
 *   PulseUI.registerLazyInputLottie(opts)  // sets window.__pulseInitInputLottie
 *   PulseUI.initHashRouter(opts)
 *   PulseUI.bindMotionFields(root)
 *   PulseUI.initLoginForm(opts)
 *   PulseUI.initLoginScreenLottie(opts)
 *   PulseUI.initFooterLottie(opts)
 *
 * Globals kept for compatibility: __pulseOnRoute, __pulseInitInputLottie
 */
(function (global) {
  "use strict";

  function reducedMotion() {
    return global.matchMedia && global.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  function initHeader(opts) {
    opts = opts || {};
    var el = opts.element || global.document.getElementById(opts.id || "site-header");
    if (!el) return;
    var cls = opts.animateClass || "Header-module--animateIn--1863a";
    function reveal() {
      el.classList.add(cls);
    }
    if (reducedMotion()) {
      reveal();
      return;
    }
    global.requestAnimationFrame(function () {
      global.requestAnimationFrame(reveal);
    });
  }

  function initDashboardReady(opts) {
    opts = opts || {};
    var dash = opts.element || global.document.getElementById(opts.id || "sample-dashboard");
    if (!dash) return;
    var readyClass = opts.readyClass || "sample-dashboard--ready";
    function ready() {
      void dash.offsetWidth;
      global.requestAnimationFrame(function () {
        dash.classList.add(readyClass);
      });
    }
    if (reducedMotion()) {
      dash.classList.add(readyClass);
      return;
    }
    var delay = typeof opts.delayMs === "number" ? opts.delayMs : 420;
    global.requestAnimationFrame(function () {
      global.requestAnimationFrame(function () {
        global.setTimeout(ready, delay);
      });
    });
  }

  function playLottieInContainer(container, data, lottie) {
    if (!container || typeof lottie === "undefined" || !data) return null;
    var reduced = reducedMotion();
    container.innerHTML = "";
    var anim = lottie.loadAnimation({
      container: container,
      renderer: "svg",
      loop: true,
      autoplay: !reduced,
      animationData: data,
    });
    if (reduced) anim.goToAndStop(0, true);
    global.requestAnimationFrame(function () {
      if (anim && typeof anim.resize === "function") anim.resize();
    });
    return anim;
  }

  function initDashboardLottie(opts) {
    opts = opts || {};
    var block = opts.blockElement || global.document.getElementById(opts.blockId || "dashboard-json-block");
    var container = opts.containerElement || global.document.getElementById(opts.containerId || "lottie-dashboard");
    var lottie = global.lottie;
    if (!block || !container || typeof lottie === "undefined") return;
    if (opts.allowRepeat !== true && container.getAttribute("data-pulse-lottie-dashboard") === "1") return;
    container.setAttribute("data-pulse-lottie-dashboard", "1");

    var embedKey = opts.embedGlobal || "__LOTTIE_DASHBOARD__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Data Dashboard.json");

    function showBlock() {
      global.requestAnimationFrame(function () {
        block.classList.add(opts.visibleClass || "is-visible");
      });
    }

    function play(data) {
      playLottieInContainer(container, data, lottie);
      showBlock();
    }

    function fail() {
      block.classList.add(opts.errorClass || "dashboard-json-block--error");
      showBlock();
    }

    var embedded = global[embedKey];
    if (embedded) {
      try {
        play(embedded);
      } catch (e) {
        fail();
      }
      return;
    }

    if (global.location.protocol === "http:" || global.location.protocol === "https:") {
      global
        .fetch(jsonUrl)
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(play)
        .catch(fail);
      return;
    }

    fail();
  }

  function initLandingPageLottie(opts) {
    opts = opts || {};
    var block = opts.blockElement || global.document.getElementById(opts.blockId || "landing-lottie-block");
    var container = opts.containerElement || global.document.getElementById(opts.containerId || "lottie-landing");
    var lottie = global.lottie;
    if (!block || !container || typeof lottie === "undefined") return;
    if (opts.allowRepeat !== true && container.getAttribute("data-pulse-lottie-landing") === "1") return;
    container.setAttribute("data-pulse-lottie-landing", "1");

    var embedKey = opts.embedGlobal || "__LOTTIE_LANDING_PAGE__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Landing Page.json");

    function showBlock() {
      global.requestAnimationFrame(function () {
        block.classList.add(opts.visibleClass || "pulse-landing__lottie--visible");
      });
    }

    function play(data) {
      playLottieInContainer(container, data, lottie);
      showBlock();
    }

    function fail() {
      block.classList.add(opts.errorClass || "pulse-landing__lottie--error");
      showBlock();
    }

    var embedded = global[embedKey];
    if (embedded) {
      try {
        play(embedded);
      } catch (e) {
        fail();
      }
      return;
    }

    if (global.location.protocol === "http:" || global.location.protocol === "https:") {
      global
        .fetch(jsonUrl)
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(play)
        .catch(fail);
      return;
    }

    fail();
  }

  function initSidebarAppIcon(opts) {
    opts = opts || {};
    var container =
      opts.containerElement || global.document.getElementById(opts.containerId || "lottie-sidebar-app-icon");
    var lottie = global.lottie;
    if (!container || typeof lottie === "undefined") return;
    if (opts.allowRepeat !== true && container.getAttribute("data-pulse-sidebar-app-icon") === "1") return;
    container.setAttribute("data-pulse-sidebar-app-icon", "1");

    var embedKey = opts.embedGlobal || "__LOTTIE_APP_ICON__";
    var jsonUrl = encodeURI(opts.jsonUrl || "icon.json");

    function play(data) {
      playLottieInContainer(container, data, lottie);
    }

    function fail() {}

    var embedded = global[embedKey];
    if (embedded) {
      try {
        play(embedded);
      } catch (e) {
        fail();
      }
      return;
    }

    if (global.location.protocol === "http:" || global.location.protocol === "https:") {
      global
        .fetch(jsonUrl)
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(play)
        .catch(fail);
      return;
    }

    fail();
  }

  function resizeAllPulseGraphLotties() {
    var list = global.__PULSE_GRAPH_LOTTIE_ANIMS__;
    if (!list || !list.length) return;
    global.requestAnimationFrame(function () {
      global.requestAnimationFrame(function () {
        for (var j = 0; j < list.length; j++) {
          try {
            if (list[j] && typeof list[j].resize === "function") list[j].resize();
          } catch (e) {}
        }
      });
    });
  }

  function initFooterLottie(opts) {
    opts = opts || {};
    var container =
      opts.containerElement || global.document.getElementById(opts.containerId || "lottie-footer");
    var lottie = global.lottie;
    if (!container || typeof lottie === "undefined") return;
    if (opts.allowRepeat !== true && container.getAttribute("data-pulse-footer-lottie") === "1") return;
    container.setAttribute("data-pulse-footer-lottie", "1");

    var embedKey = opts.embedGlobal || "__LOTTIE_FOOTER__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Footer.json");

    function play(data) {
      playLottieInContainer(container, data, lottie);
      resizeAllPulseGraphLotties();
    }

    function fail() {}

    var embedded = global[embedKey];
    if (embedded) {
      try {
        play(embedded);
      } catch (e) {
        fail();
      }
      return;
    }

    if (global.location.protocol === "http:" || global.location.protocol === "https:") {
      global
        .fetch(jsonUrl)
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(play)
        .catch(fail);
      return;
    }

    fail();
  }

  /**
   * Mount the same embedded Data Dashboard Lottie in every `[data-pulse-graph-lottie]` node
   * (each bar / line / pie chart card, etc.). Uses __LOTTIE_DASHBOARD__ or fetch(jsonUrl).
   */
  function initDataDashboardGraphLotties(opts) {
    opts = opts || {};
    var nodes = global.document.querySelectorAll(opts.selector || "[data-pulse-graph-lottie]");
    if (!nodes.length) return;
    var lottie = global.lottie;
    if (typeof lottie === "undefined") return;

    var embedKey = opts.embedGlobal || "__LOTTIE_DASHBOARD__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Data Dashboard.json");
    if (!global.__PULSE_GRAPH_LOTTIE_ANIMS__) global.__PULSE_GRAPH_LOTTIE_ANIMS__ = [];

    function mountAll(data) {
      for (var i = 0; i < nodes.length; i++) {
        var el = nodes[i];
        if (el.getAttribute("data-pulse-graph-lottie-init") === "1") continue;
        el.setAttribute("data-pulse-graph-lottie-init", "1");
        try {
          var anim = playLottieInContainer(el, data, lottie);
          if (anim) global.__PULSE_GRAPH_LOTTIE_ANIMS__.push(anim);
        } catch (e) {}
      }
      resizeAllPulseGraphLotties();
    }

    var embedded = global[embedKey];
    if (embedded) {
      try {
        mountAll(embedded);
      } catch (e) {}
      return;
    }

    if (global.location.protocol === "http:" || global.location.protocol === "https:") {
      global
        .fetch(jsonUrl)
        .then(function (r) {
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(mountAll)
        .catch(function () {});
      return;
    }
  }

  function registerLazyInputLottie(opts) {
    opts = opts || {};
    if (opts.allowRepeat !== true && typeof global.__pulseInitInputLottie === "function") return;
    var anim = null;
    var loadStarted = false;
    var block = opts.blockElement || global.document.getElementById(opts.blockId || "settings-input-lottie-block");
    var container = opts.containerElement || global.document.getElementById(opts.containerId || "lottie-input-field");
    var activePageId = opts.activePageId || "settings";
    var embedKey = opts.embedGlobal || "__LOTTIE_INPUT_FIELD__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Input Field - UX in Motion.json");
    var lottieRef = function () {
      return global.lottie;
    };

    function fail() {
      if (block) block.classList.add(opts.errorClass || "dashboard-json-block--error");
    }

    function startAnim(data) {
      var lottie = lottieRef();
      if (!container || typeof lottie === "undefined") return;
      if (anim) {
        try {
          anim.destroy();
        } catch (e) {}
        anim = null;
      }
      anim = playLottieInContainer(container, data, lottie);
    }

    global.__pulseInitInputLottie = function () {
      var page = global.document.getElementById(activePageId);
      if (!page || !page.classList.contains("sample-page--active")) return;
      if (anim) {
        global.requestAnimationFrame(function () {
          if (anim && typeof anim.resize === "function") anim.resize();
        });
        return;
      }
      if (loadStarted) return;
      if (!block || !container || typeof lottieRef() === "undefined") return;
      loadStarted = true;

      var embedded = global[embedKey];
      if (embedded) {
        try {
          startAnim(embedded);
        } catch (e) {
          fail();
        }
        return;
      }

      if (global.location.protocol === "http:" || global.location.protocol === "https:") {
        global
          .fetch(jsonUrl)
          .then(function (r) {
            if (!r.ok) throw new Error("HTTP " + r.status);
            return r.json();
          })
          .then(startAnim)
          .catch(fail);
        return;
      }

      fail();
    };
  }

  function initHashRouter(opts) {
    opts = opts || {};
    if (opts.skipIfBound !== false && typeof global.__pulseOnRoute === "function") return;
    var VALID = opts.routes || [
      "overview",
      "analytics",
      "reports",
      "customers",
      "billing",
      "settings",
    ];
    var nav = opts.navElement || global.document.getElementById(opts.navId || "dashboard-primary-nav");
    var pages = opts.pageElements || global.document.querySelectorAll(".sample-page");
    var panels = opts.railPanelElements || global.document.querySelectorAll(".sample-rail-panel");
    var appEl = opts.appElement || global.document.getElementById(opts.appId || "pulse-app");
    var loginRoot = opts.loginElement || global.document.getElementById(opts.loginId || "pulse-login");
    var titleEl =
      opts.titleElement ||
      (typeof opts.titleSelector === "string" ? global.document.querySelector(opts.titleSelector) : null) ||
      global.document.querySelector("title");
    var loginHash = (opts.loginHash || "login").toLowerCase();
    var defaultRoute = opts.defaultRoute || "overview";
    var onShowPage = opts.onShowPage;
    var titles = opts.titles || { login: "Pulse · Sign in", app: "Pulse · Dashboard" };
    /* file://: avoid history.replaceState (blocked / noisy in iframes & some embedded previews; unique opaque origins) */
    var fileVirtualRoute =
      opts.forceHistory === true ? false : global.location.protocol === "file:";
    var virtualHash =
      global.location.hash && global.location.hash !== "#"
        ? global.location.hash
        : "#" + defaultRoute;

    function currentHash() {
      if (fileVirtualRoute) return virtualHash;
      return global.location.hash || "";
    }

    function navigateToFragment(frag) {
      var h = frag.indexOf("#") === 0 ? frag : "#" + frag;
      if (fileVirtualRoute) {
        virtualHash = h;
        onRoute();
        return;
      }
      try {
        global.history.replaceState(null, "", h);
      } catch (e) {}
      onRoute();
    }

    global.__pulseNavigateTo = navigateToFragment;

    function normalize(slug) {
      slug = String(slug || defaultRoute)
        .replace(/^#/, "")
        .toLowerCase();
      return VALID.indexOf(slug) >= 0 ? slug : defaultRoute;
    }

    function showPage(slug) {
      slug = normalize(slug);
      Array.prototype.forEach.call(pages, function (el) {
        var on = el.getAttribute("data-page") === slug;
        el.classList.toggle("sample-page--active", on);
      });
      Array.prototype.forEach.call(panels, function (el) {
        el.classList.toggle(
          "sample-rail-panel--active",
          el.getAttribute("data-rail-page") === slug
        );
      });
      if (nav) {
        nav.querySelectorAll("a[data-page]").forEach(function (a) {
          var on = a.getAttribute("data-page") === slug;
          if (on) a.setAttribute("aria-current", "page");
          else a.removeAttribute("aria-current");
        });
      }
      if (typeof onShowPage === "function") {
        onShowPage(slug);
      } else if (slug === "settings" && typeof global.__pulseInitInputLottie === "function") {
        global.requestAnimationFrame(function () {
          global.__pulseInitInputLottie();
        });
      }
      resizeAllPulseGraphLotties();
    }

    function applyShell() {
      var raw = (currentHash() || "").replace(/^#/, "").toLowerCase();
      var isLogin = raw === loginHash;
      global.document.body.classList.toggle(opts.loginBodyClass || "pulse-body-login", isLogin);
      if (appEl) appEl.hidden = isLogin;
      if (loginRoot) {
        loginRoot.hidden = !isLogin;
        if (isLogin) {
          loginRoot.classList.add(opts.loginVisibleClass || "pulse-login--visible");
        } else {
          loginRoot.classList.remove(opts.loginVisibleClass || "pulse-login--visible");
        }
      }
      if (titleEl) {
        titleEl.textContent = isLogin ? titles.login : titles.app;
      }
    }

    function onRoute() {
      applyShell();
      var raw = (currentHash() || "").replace(/^#/, "").toLowerCase();
      if (raw === loginHash) return;
      showPage(normalize(raw || defaultRoute));
    }

    global.__pulseOnRoute = onRoute;

    if (nav) {
      nav.addEventListener("click", function (e) {
        var a = e.target.closest("a[data-page]");
        if (!a) return;
        e.preventDefault();
        var slug = a.getAttribute("data-page");
        navigateToFragment(slug);
      });
    }

    global.addEventListener("hashchange", function () {
      if (fileVirtualRoute) {
        virtualHash = global.location.hash || virtualHash;
      }
      onRoute();
    });

    if (fileVirtualRoute) {
      if (!global.location.hash || global.location.hash === "#") {
        virtualHash = "#" + defaultRoute;
      }
    } else if (!global.location.hash) {
      try {
        global.history.replaceState(null, "", "#" + defaultRoute);
      } catch (e) {}
    }
    onRoute();
  }

  function initSidebarToggle(opts) {
    opts = opts || {};
    var dash = opts.rootElement || global.document.getElementById(opts.rootId || "sample-dashboard");
    var btn = opts.buttonElement || global.document.getElementById(opts.buttonId || "dashboard-sidebar-toggle");
    if (!dash || !btn) return;
    if (btn.getAttribute("data-pulse-sidebar-toggle") === "1") return;
    btn.setAttribute("data-pulse-sidebar-toggle", "1");
    var storageKey = opts.storageKey || "pulse-sidebar-collapsed";

    function apply(collapsed) {
      dash.classList.toggle("sample-dashboard--sidebar-collapsed", collapsed);
      btn.setAttribute("aria-expanded", collapsed ? "false" : "true");
      btn.setAttribute("aria-label", collapsed ? "Expand sidebar" : "Collapse sidebar");
      btn.title = collapsed ? "Expand sidebar" : "Collapse sidebar";
      try {
        global.localStorage.setItem(storageKey, collapsed ? "1" : "0");
      } catch (e) {}
    }

    var storedCollapsed = false;
    try {
      storedCollapsed = global.localStorage.getItem(storageKey) === "1";
    } catch (e) {}
    if (storedCollapsed) {
      apply(true);
    }

    btn.addEventListener("click", function () {
      apply(!dash.classList.contains("sample-dashboard--sidebar-collapsed"));
    });
  }

  function initLoginScreenLottie(opts) {
    opts = opts || {};
    var loginRoot = opts.loginElement || global.document.getElementById(opts.loginId || "pulse-login");
    var container =
      opts.containerElement || global.document.getElementById(opts.containerId || "lottie-login-screen");
    var lottie = global.lottie;
    if (!loginRoot || !container || typeof lottie === "undefined") return;
    if (loginRoot.getAttribute("data-pulse-login-lottie-route") === "1") return;
    loginRoot.setAttribute("data-pulse-login-lottie-route", "1");

    var embedKey = opts.embedGlobal || "__LOTTIE_LOGIN_SCREEN__";
    var jsonUrl = encodeURI(opts.jsonUrl || "Login Screen.json");
    var anim = null;

    function mountData(data) {
      if (!data || !loginRoot || loginRoot.hidden) return;
      if (container.getAttribute("data-pulse-login-lottie-init") === "1") return;
      anim = playLottieInContainer(container, data, lottie);
      container.setAttribute("data-pulse-login-lottie-init", "1");
    }

    function syncPlayback() {
      if (!loginRoot || loginRoot.hidden) {
        if (anim && typeof anim.pause === "function") anim.pause();
        return;
      }
      if (container.getAttribute("data-pulse-login-lottie-init") === "1") {
        global.requestAnimationFrame(function () {
          if (anim && typeof anim.resize === "function") anim.resize();
          if (anim && typeof anim.play === "function" && !reducedMotion()) anim.play();
        });
        return;
      }

      var embedded = global[embedKey];
      if (embedded) {
        try {
          mountData(embedded);
        } catch (e) {}
        global.requestAnimationFrame(function () {
          if (anim && typeof anim.resize === "function") anim.resize();
        });
        return;
      }

      if (global.location.protocol === "http:" || global.location.protocol === "https:") {
        global
          .fetch(jsonUrl)
          .then(function (r) {
            if (!r.ok) throw new Error("HTTP " + r.status);
            return r.json();
          })
          .then(function (data) {
            if (!loginRoot || loginRoot.hidden) return;
            if (container.getAttribute("data-pulse-login-lottie-init") === "1") return;
            mountData(data);
            global.requestAnimationFrame(function () {
              if (anim && typeof anim.resize === "function") anim.resize();
            });
          })
          .catch(function () {});
      }
    }

    var prevOnRoute = global.__pulseOnRoute;
    global.__pulseOnRoute = function () {
      if (typeof prevOnRoute === "function") prevOnRoute.apply(null, arguments);
      syncPlayback();
    };

    syncPlayback();
  }

  function bindLoginPasswordToggle(opts) {
    opts = opts || {};
    var btn = opts.toggleButton || global.document.getElementById(opts.toggleId || "pulse-login-pw-toggle");
    var input =
      opts.passwordInput || global.document.getElementById(opts.passwordInputId || "login-password");
    if (!btn || !input) return;
    if (btn.getAttribute("data-pulse-pw-toggle") === "1") return;
    btn.setAttribute("data-pulse-pw-toggle", "1");
    btn.addEventListener("click", function () {
      var show = input.getAttribute("type") === "password";
      input.setAttribute("type", show ? "text" : "password");
      btn.setAttribute("aria-pressed", show ? "true" : "false");
      btn.setAttribute("aria-label", show ? "Hide password" : "Show password");
    });
  }

  function bindMotionFields(root) {
    if (!root) return;
    root.querySelectorAll("[data-mfield]").forEach(function (wrap) {
      var input = wrap.querySelector("input");
      if (!input) return;
      function sync() {
        wrap.classList.toggle(
          "login-mfield--filled",
          !!(input.value && String(input.value).length > 0)
        );
      }
      input.addEventListener("input", sync);
      input.addEventListener("blur", sync);
      sync();
    });
  }

  function initLoginForm(opts) {
    opts = opts || {};
    var form = opts.formElement || global.document.getElementById(opts.formId || "pulse-login-form");
    var loginRoot = opts.loginElement || global.document.getElementById(opts.loginId || "pulse-login");
    var afterSubmitHash = opts.afterSubmitHash || "#overview";
    bindMotionFields(loginRoot);
    bindLoginPasswordToggle(opts.passwordToggle);
    if (form && form.getAttribute("data-pulse-login-bound") === "1") return;
    if (form) form.setAttribute("data-pulse-login-bound", "1");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var frag = String(afterSubmitHash || "").replace(/^#/, "");
        if (typeof global.__pulseNavigateTo === "function") {
          global.__pulseNavigateTo(frag || "overview");
        } else {
          try {
            global.history.replaceState(null, "", "#" + (frag || "overview"));
          } catch (err) {}
          if (typeof global.__pulseOnRoute === "function") {
            global.__pulseOnRoute();
          }
        }
      });
    }
  }

  /**
   * @param {object} [options]
   * @param {boolean} [options.header=true]
   * @param {boolean} [options.dashboardReady=true]
   * @param {boolean} [options.dashboardLottie=true]
   * @param {boolean} [options.dataDashboardGraphLotties=true]
   * @param {boolean} [options.lazyInputLottie=true]
   * @param {boolean} [options.router=true]
   * @param {boolean} [options.loginForm=true]
   * @param {boolean} [options.sidebarToggle=true]
   * @param {boolean|object} [options.sidebarAppIcon=true]  pass false to skip; object forwarded to initSidebarAppIcon
   * @param {boolean|object} [options.loginScreenLottie=true]  pass false to skip; object forwarded to initLoginScreenLottie
   * @param {boolean|object} [options.footerLottie=true]  pass false to skip; object forwarded to initFooterLottie
   */
  function init(options) {
    options = options || {};
    if (options.header !== false) initHeader(options.header);
    if (options.sidebarToggle !== false) initSidebarToggle(options.sidebarToggle);
    if (options.dashboardReady !== false) initDashboardReady(options.dashboardReady);
    if (options.lazyInputLottie !== false) registerLazyInputLottie(options.lazyInputLottie);
    if (options.router !== false) initHashRouter(options.router);
    if (options.loginScreenLottie !== false) {
      initLoginScreenLottie(
        options.loginScreenLottie && typeof options.loginScreenLottie === "object"
          ? options.loginScreenLottie
          : {}
      );
    }
    if (options.loginForm !== false) initLoginForm(options.loginForm);
    if (options.dashboardLottie !== false) initDashboardLottie(options.dashboardLottie);
    if (options.dataDashboardGraphLotties !== false) initDataDashboardGraphLotties(options.dataDashboardGraphLotties);
    if (options.sidebarAppIcon !== false) {
      initSidebarAppIcon(
        options.sidebarAppIcon && typeof options.sidebarAppIcon === "object"
          ? options.sidebarAppIcon
          : {}
      );
    }
    if (options.footerLottie !== false) {
      initFooterLottie(
        options.footerLottie && typeof options.footerLottie === "object"
          ? options.footerLottie
          : {}
      );
    }
  }

  global.PulseUI = {
    reducedMotion: reducedMotion,
    init: init,
    initHeader: initHeader,
    initSidebarToggle: initSidebarToggle,
    initDashboardReady: initDashboardReady,
    initDashboardLottie: initDashboardLottie,
    initLandingPageLottie: initLandingPageLottie,
    initSidebarAppIcon: initSidebarAppIcon,
    initDataDashboardGraphLotties: initDataDashboardGraphLotties,
    resizeAllPulseGraphLotties: resizeAllPulseGraphLotties,
    registerLazyInputLottie: registerLazyInputLottie,
    initHashRouter: initHashRouter,
    bindMotionFields: bindMotionFields,
    initLoginForm: initLoginForm,
    initLoginScreenLottie: initLoginScreenLottie,
    initFooterLottie: initFooterLottie,
    playLottieInContainer: playLottieInContainer,
  };
})(typeof window !== "undefined" ? window : this);
