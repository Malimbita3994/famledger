/**
 * Header user profile: dropdown + optional Lottie avatar (Profile.json via __LOTTIE_PROFILE__ or fetch).
 *   PulseProfile.init(options)
 */
(function (global) {
  "use strict";

  function reducedMotion() {
    return global.matchMedia && global.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  function playProfileLottie(container, data, lottie) {
    if (!container || !data || typeof lottie === "undefined") return null;
    var reduced = reducedMotion();
    container.innerHTML = "";
    var anim = lottie.loadAnimation({
      container: container,
      renderer: "svg",
      loop: true,
      autoplay: !reduced,
      animationData: data,
      rendererSettings: {
        preserveAspectRatio: "xMidYMid slice",
      },
    });
    if (reduced && anim) anim.goToAndStop(0, true);
    global.requestAnimationFrame(function () {
      if (anim && typeof anim.resize === "function") anim.resize();
    });
    return anim;
  }

  function init(opts) {
    opts = opts || {};
    var root = opts.rootElement || global.document.getElementById(opts.rootId || "pulse-profile");
    if (!root || root.getAttribute("data-pulse-profile-bound") === "1") return;
    root.setAttribute("data-pulse-profile-bound", "1");

    var trigger = opts.triggerElement || global.document.getElementById(opts.triggerId || "pulse-profile-trigger");
    var menu = opts.menuElement || global.document.getElementById(opts.menuId || "pulse-profile-menu");
    var lottieEl = opts.lottieContainerElement || global.document.getElementById(opts.lottieContainerId || "lottie-profile-avatar");
    var jsonUrl = encodeURI(opts.jsonUrl || "Profile.json");
    var embedKey = opts.embedGlobal || "__LOTTIE_PROFILE__";

    if (lottieEl && typeof global.lottie !== "undefined") {
      var embedded = global[embedKey];
      if (embedded) {
        try {
          playProfileLottie(lottieEl, embedded, global.lottie);
        } catch (e) {}
      } else if (global.location.protocol === "http:" || global.location.protocol === "https:") {
        global
          .fetch(jsonUrl)
          .then(function (r) {
            if (!r.ok) throw new Error("HTTP " + r.status);
            return r.json();
          })
          .then(function (data) {
            playProfileLottie(lottieEl, data, global.lottie);
          })
          .catch(function () {});
      }
    }

    if (!trigger || !menu) return;

    function openMenu() {
      menu.hidden = false;
      trigger.setAttribute("aria-expanded", "true");
    }

    function closeMenu() {
      menu.hidden = true;
      trigger.setAttribute("aria-expanded", "false");
    }

    function toggleMenu() {
      if (menu.hidden) openMenu();
      else closeMenu();
    }

    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      toggleMenu();
    });

    global.document.addEventListener("click", function () {
      closeMenu();
    });

    menu.addEventListener("click", function (e) {
      e.stopPropagation();
    });

    global.document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !menu.hidden) {
        closeMenu();
        trigger.focus();
      }
    });

    global.addEventListener("hashchange", closeMenu);
  }

  global.PulseProfile = { init: init };
})(typeof window !== "undefined" ? window : this);
