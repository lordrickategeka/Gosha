/* =============================================================
   Loganate Docs — Tweaks panel (vanilla, follows host edit-mode protocol)
   ============================================================= */

(function () {
  "use strict";

  const panel = document.getElementById("tweaks-panel");
  const closeBtn = document.getElementById("tweaks-close");

  const STORAGE_KEY = "loganate.tweaks";
  function readTweaks() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}"); }
    catch { return {}; }
  }
  function writeTweaks(t) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(t));
  }

  /* --- Apply a single tweak --- */
  function applyTweak(key, value) {
    if (key === "hero") {
      const hero = document.getElementById("hero");
      if (hero) hero.dataset.variant = value;
    } else {
      document.documentElement.dataset[key] = value;
    }
    // Update pressed state in panel
    const group = panel.querySelector(`[data-tweak="${key}"]`);
    if (group) {
      group.querySelectorAll("button").forEach((b) => {
        b.setAttribute("aria-pressed", b.dataset.value === value ? "true" : "false");
      });
    }
  }

  /* --- Apply all current settings on load --- */
  function applyAll() {
    const defaults = window.TWEAK_DEFAULTS || {};
    const saved = readTweaks();
    const merged = Object.assign({}, defaults, saved);
    Object.keys(merged).forEach((k) => applyTweak(k, merged[k]));
  }
  applyAll();

  /* --- Wire up controls --- */
  panel.querySelectorAll("[data-tweak]").forEach((group) => {
    const key = group.dataset.tweak;
    group.querySelectorAll("button").forEach((btn) => {
      btn.addEventListener("click", () => {
        const value = btn.dataset.value;
        applyTweak(key, value);
        const t = readTweaks();
        t[key] = value;
        writeTweaks(t);
        // Tell host so the change survives reload
        window.parent?.postMessage(
          { type: "__edit_mode_set_keys", edits: { [key]: value } },
          "*"
        );
      });
    });
  });

  /* --- Host edit-mode protocol --- */
  // Listener FIRST, then announce availability
  window.addEventListener("message", (e) => {
    const data = e?.data || {};
    if (data.type === "__activate_edit_mode") {
      panel.setAttribute("data-open", "true");
    } else if (data.type === "__deactivate_edit_mode") {
      panel.setAttribute("data-open", "false");
    }
  });

  window.parent?.postMessage({ type: "__edit_mode_available" }, "*");

  closeBtn?.addEventListener("click", () => {
    panel.setAttribute("data-open", "false");
    window.parent?.postMessage({ type: "__edit_mode_dismissed" }, "*");
  });

})();
