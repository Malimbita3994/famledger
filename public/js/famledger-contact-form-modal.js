/**
 * FamLedger contact form modal: Quill + spam check (math or optional reCAPTCHA v2) for Bootstrap 3 modals.
 * Auto-inits all elements with class .famledger-contact-form-modal.
 * Requires: jQuery, Bootstrap 3 modal, Quill 1.3.x (defer order: jQuery, Bootstrap, Quill, then this file).
 */
(function (window, document) {
  "use strict";

  function getEditorEl(quill) {
    if (!quill || !quill.container) {
      return null;
    }
    return quill.container.querySelector(".ql-editor");
  }

  function attachModal(modalEl) {
    if (!modalEl || modalEl.getAttribute("data-famledger-contact-modal-attached") === "1") {
      return;
    }
    modalEl.setAttribute("data-famledger-contact-modal-attached", "1");

    var formId = modalEl.getAttribute("data-form-id");
    var quillContainerId = modalEl.getAttribute("data-quill-container-id");
    var hiddenMessageId = modalEl.getAttribute("data-hidden-message-id");
    var recaptchaContainerId = modalEl.getAttribute("data-recaptcha-container-id");
    var captchaDriver = (modalEl.getAttribute("data-captcha-driver") || "math").toLowerCase();
    var openOnLoad = modalEl.getAttribute("data-open-on-load") === "1";

    var quillInstance = null;
    var recaptchaWidgetId = null;

    function escapeHtml(text) {
      var d = document.createElement("div");
      d.textContent = text == null ? "" : String(text);
      return d.innerHTML;
    }

    function clearClientErrors() {
      var box = document.getElementById(modalEl.id + "_client_errors");
      if (!box) {
        return;
      }
      box.style.display = "none";
      box.innerHTML = "";
    }

    function showClientErrors(messages) {
      var box = document.getElementById(modalEl.id + "_client_errors");
      if (!box || !messages || !messages.length) {
        return;
      }
      box.style.display = "block";
      box.innerHTML = messages
        .map(function (m) {
          return "<div>" + escapeHtml(m) + "</div>";
        })
        .join("");
      try {
        box.scrollIntoView({ block: "nearest", behavior: "smooth" });
      } catch (e) {}
    }

    function syncQuillToTextarea() {
      var ta = document.getElementById(hiddenMessageId);
      if (!ta || !quillInstance) {
        return;
      }
      var ed = getEditorEl(quillInstance);
      ta.value = ed ? ed.innerHTML : "";
    }

    function initQuill() {
      if (quillInstance) {
        return;
      }
      var el = document.getElementById(quillContainerId);
      var ta = document.getElementById(hiddenMessageId);
      if (!el || !ta || typeof window.Quill === "undefined") {
        return;
      }

      var toolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ["bold", "italic", "underline", "strike"],
        [{ color: [] }, { background: [] }],
        [{ list: "ordered" }, { list: "bullet" }],
        [{ indent: "-1" }, { indent: "+1" }],
        ["link"],
        ["clean"],
      ];

      try {
        quillInstance = new window.Quill(el, {
          theme: "snow",
          modules: { toolbar: toolbarOptions },
          placeholder: "Type your message…",
        });
      } catch (err) {
        console.error("FamLedger contact modal: Quill init failed", err);
        return;
      }

      var ed = getEditorEl(quillInstance);
      if (ed && ta.value && ta.value.trim() !== "") {
        ed.innerHTML = ta.value;
      }

      quillInstance.on("text-change", syncQuillToTextarea);
      syncQuillToTextarea();
    }

    function ensureQuill(attemptsLeft) {
      attemptsLeft = typeof attemptsLeft === "number" ? attemptsLeft : 40;
      if (quillInstance) {
        return;
      }
      if (typeof window.Quill !== "undefined") {
        initQuill();
        if (quillInstance) {
          return;
        }
      }
      if (attemptsLeft <= 0) {
        console.warn("FamLedger contact modal: Quill failed to load.");
        return;
      }
      setTimeout(function () {
        ensureQuill(attemptsLeft - 1);
      }, 50);
    }

    function initRecaptcha() {
      if (!window.__recaptchaSiteKey || !recaptchaContainerId) {
        return;
      }
      if (!document.getElementById(recaptchaContainerId)) {
        return;
      }
      if (typeof window.grecaptcha === "undefined") {
        return;
      }
      window.grecaptcha.ready(function () {
        try {
          if (recaptchaWidgetId === null) {
            recaptchaWidgetId = window.grecaptcha.render(recaptchaContainerId, {
              sitekey: window.__recaptchaSiteKey,
              theme: "light",
            });
            modalEl.setAttribute("data-recaptcha-widget-id", String(recaptchaWidgetId));
          } else {
            window.grecaptcha.reset(recaptchaWidgetId);
          }
        } catch (e) {
          console.error("FamLedger contact modal: reCAPTCHA render failed", e);
        }
      });
    }

    /**
     * api.js is async; grecaptcha may not exist when the modal first opens.
     * Poll like Quill until the library is available, then render once.
     */
    function ensureRecaptcha(attemptsLeft) {
      attemptsLeft = typeof attemptsLeft === "number" ? attemptsLeft : 80;
      if (!window.__recaptchaSiteKey || !recaptchaContainerId) {
        return;
      }
      if (!document.getElementById(recaptchaContainerId)) {
        return;
      }
      if (typeof window.grecaptcha !== "undefined") {
        initRecaptcha();
        return;
      }
      if (attemptsLeft <= 0) {
        console.warn(
          "FamLedger contact modal: reCAPTCHA script did not load in time. Check network / ad blockers / RECAPTCHA_SITE_KEY."
        );
        return;
      }
      setTimeout(function () {
        ensureRecaptcha(attemptsLeft - 1);
      }, 50);
    }

    var formEl = document.getElementById(formId);
    if (formEl) {
      formEl.addEventListener(
        "submit",
        function (e) {
          syncQuillToTextarea();
          clearClientErrors();

          var msgs = [];
          var nameEl = formEl.querySelector('[name="name"]');
          var emailEl = formEl.querySelector('[name="email"]');
          var phoneEl = formEl.querySelector('[name="phone"]');
          var ta = document.getElementById(hiddenMessageId);
          var nameVal = nameEl && nameEl.value ? nameEl.value.trim() : "";
          var emailVal = emailEl && emailEl.value ? emailEl.value.trim() : "";
          var phoneVal = phoneEl && phoneEl.value ? phoneEl.value.trim() : "";
          var msgHtml = ta && ta.value ? ta.value : "";
          var plainMsg = msgHtml
            .replace(/<[^>]+>/g, " ")
            .replace(/&nbsp;/g, " ")
            .replace(/\s+/g, " ")
            .trim();

          if (!nameVal) {
            msgs.push("Please enter your full name.");
          }
          if (!emailVal) {
            msgs.push("Please enter your email address.");
          } else if (emailEl && typeof emailEl.checkValidity === "function" && !emailEl.checkValidity()) {
            msgs.push("Please enter a valid email address.");
          }
          if (!phoneVal) {
            msgs.push("Please enter your phone number.");
          }
          if (!plainMsg) {
            msgs.push("Please enter a message.");
          }

          if (msgs.length) {
            e.preventDefault();
            showClientErrors(msgs);
            return;
          }

          if (captchaDriver === "math") {
            var mathInput = formEl.querySelector('[name="contact_captcha_answer"]');
            var mathVal = mathInput && mathInput.value != null ? String(mathInput.value).trim() : "";
            if (!mathVal) {
              e.preventDefault();
              showClientErrors(["Please answer the security question."]);
              return;
            }
            if (mathInput && mathInput.checkValidity && !mathInput.checkValidity()) {
              e.preventDefault();
              showClientErrors(["Please enter a valid number for the security question."]);
              return;
            }
            return;
          }

          if (captchaDriver !== "recaptcha" || !window.__recaptchaSiteKey || !recaptchaContainerId) {
            return;
          }
          if (typeof window.grecaptcha === "undefined") {
            e.preventDefault();
            showClientErrors([
              "Security check could not load. Try disabling ad blockers for this page, then refresh.",
            ]);
            return;
          }
          if (recaptchaWidgetId === null) {
            e.preventDefault();
            ensureRecaptcha(80);
            showClientErrors([
              'Security check is still loading. Wait a moment, complete "I\'m not a robot", then try again.',
            ]);
            return;
          }
          var token = window.grecaptcha.getResponse(recaptchaWidgetId);
          if (!token || String(token).length < 10) {
            e.preventDefault();
            var wrap = document.getElementById(recaptchaContainerId);
            if (wrap && wrap.scrollIntoView) {
              wrap.scrollIntoView({ block: "center", behavior: "smooth" });
            }
            showClientErrors(['Please tick "I\'m not a robot" before sending your message.']);
            return;
          }
        },
        true
      );
    }

    if (!window.jQuery || typeof window.jQuery.fn.modal !== "function") {
      return;
    }

    var $m = window.jQuery(modalEl);
    $m.on("shown.bs.modal", function () {
      clearClientErrors();
      ensureQuill();
      setTimeout(function () {
        ensureQuill(20);
      }, 200);
      if (captchaDriver === "recaptcha") {
        ensureRecaptcha();
        setTimeout(function () {
          ensureRecaptcha(40);
        }, 300);
      }
    });

    $m.on("hidden.bs.modal", function () {
      if (
        captchaDriver === "recaptcha" &&
        typeof window.grecaptcha !== "undefined" &&
        recaptchaWidgetId !== null &&
        window.__recaptchaSiteKey
      ) {
        try {
          window.grecaptcha.reset(recaptchaWidgetId);
        } catch (e) {}
      }
    });

    if (openOnLoad) {
      $m.modal("show");
    }
  }

  function initAll() {
    var nodes = document.querySelectorAll(".famledger-contact-form-modal");
    for (var i = 0; i < nodes.length; i++) {
      attachModal(nodes[i]);
    }
  }

  /**
   * Deferred scripts run before DOMContentLoaded. If we init immediately when readyState !== "loading",
   * we open the modal for validation errors — then the landing page's DOMContentLoaded runs
   * famledgerClearBootstrapModalArtifacts() which calls .modal("hide") on all modals and closes it
   * before the user sees errors. Always schedule init after DOMContentLoaded (setTimeout(0) if already past "loading").
   */
  function scheduleInitAll() {
    function run() {
      initAll();
    }
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", run);
    } else {
      setTimeout(run, 0);
    }
  }
  scheduleInitAll();

  window.FamLedgerContactFormModal = {
    init: initAll,
    attach: attachModal,
  };
})(window, document);
