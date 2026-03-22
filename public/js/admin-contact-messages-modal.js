/**
 * Load Bootstrap contact message modal (view variant) on /admin/contact-messages via AJAX.
 * Requires: jQuery, Bootstrap 3 modal JS, famledger-contact-form-modal.js (view branch).
 */
(function (window, document) {
  "use strict";

  function stripBootstrapModalUi($) {
    var el = document.getElementById("adminContactMessageModal");
    if (el) {
      try {
        $(el).modal("hide");
      } catch (e) {}
      if (el.parentNode) {
        el.parentNode.removeChild(el);
      }
    }
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open").css("padding-right", "");
  }

  function openModalFromUrl($, container, url, cfg) {
    if (!container || !url || !$) {
      return;
    }
    stripBootstrapModalUi($);
    fetch(url, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "text/html",
      },
      credentials: "same-origin",
    })
      .then(function (r) {
        if (!r.ok) {
          throw new Error("HTTP " + r.status);
        }
        return r.text();
      })
      .then(function (html) {
        container.innerHTML = html;
        container.removeAttribute("aria-hidden");
        var modalEl = document.getElementById("adminContactMessageModal");
        // Keep dialog under document.body so no ancestor (e.g. injection container) can be aria-hidden while the modal has focus.
        if (modalEl && modalEl.parentNode !== document.body) {
          document.body.appendChild(modalEl);
        }
        if (window.FamLedgerContactFormModal && typeof window.FamLedgerContactFormModal.init === "function") {
          window.FamLedgerContactFormModal.init();
        }
        $("#adminContactMessageModal").modal("show");
        if (cfg && cfg.stripOpenQuery && window.history && window.history.replaceState) {
          try {
            var u = new URL(window.location.href);
            if (u.searchParams.has("open")) {
              u.searchParams.delete("open");
              var qs = u.searchParams.toString();
              window.history.replaceState({}, "", u.pathname + (qs ? "?" + qs : "") + u.hash);
            }
          } catch (e2) {}
        }
      })
      .catch(function () {
        if (cfg && cfg.indexUrl) {
          window.location.href = cfg.indexUrl;
        }
      });
  }

  function init(cfg) {
    cfg = cfg || {};
    var container = document.querySelector(cfg.container || "#famledger-admin-contact-modal-container");
    if (!container || typeof window.jQuery === "undefined") {
      return;
    }
    var $ = window.jQuery;

    document.body.addEventListener("click", function (e) {
      var t = e.target.closest("[data-famledger-contact-message-modal-url]");
      if (!t) {
        return;
      }
      e.preventDefault();
      openModalFromUrl($, container, t.getAttribute("data-famledger-contact-message-modal-url"), {
        stripOpenQuery: false,
        indexUrl: cfg.indexUrl,
      });
    });

    if (cfg.openOnLoadId && cfg.modalUrlTemplate) {
      var id = String(cfg.openOnLoadId).replace(/\D/g, "");
      if (id) {
        var url = cfg.modalUrlTemplate.replace("__ID__", id);
        setTimeout(function () {
          openModalFromUrl($, container, url, {
            stripOpenQuery: true,
            indexUrl: cfg.indexUrl,
          });
        }, 0);
      }
    }
  }

  window.FamLedgerAdminContactMessagesModal = { init: init };

  document.addEventListener("DOMContentLoaded", function () {
    if (window.FAMLEDGER_CONTACT_MESSAGES_INDEX) {
      init(window.FAMLEDGER_CONTACT_MESSAGES_INDEX);
    }
  });
})(window, document);
