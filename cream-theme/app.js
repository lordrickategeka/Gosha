/* =============================================================
   Loganate Docs — vanilla JS for routing, theme, copy, mobile nav
   ============================================================= */

(function () {
  "use strict";

  /* ---------- Routing (hash-based, Home vs Docs) ---------- */
  const viewHome = document.getElementById("view-home");
  const viewDocs = document.getElementById("view-docs");

  function applyRoute() {
    const hash = location.hash || "#home";
    const isDocs = hash.startsWith("#docs") ||
                   hash.startsWith("#overview") ||
                   hash.startsWith("#creating-") ||
                   hash.startsWith("#log-levels") ||
                   hash.startsWith("#attaching-") ||
                   hash.startsWith("#pipelines") ||
                   hash.startsWith("#composing") ||
                   hash.startsWith("#parameter-") ||
                   hash.startsWith("#next");

    viewHome.hidden = isDocs;
    viewDocs.hidden = !isDocs;

    // Update top nav aria-current
    document.querySelectorAll(".topnav a").forEach((a) => {
      const route = a.dataset.route || "home";
      if ((route === "docs" && isDocs) || (route === "home" && !isDocs)) {
        a.setAttribute("aria-current", "page");
      } else {
        a.removeAttribute("aria-current");
      }
    });

    // Close mobile sidebar on route change
    document.getElementById("sidebar")?.setAttribute("data-open", "false");
    document.getElementById("burger")?.setAttribute("aria-expanded", "false");

    window.scrollTo({ top: 0, behavior: "instant" });
  }

  window.addEventListener("hashchange", applyRoute);
  applyRoute();

  /* ---------- Theme toggle ---------- */
  const themeBtn = document.getElementById("theme-toggle");
  function readTweaks() {
    try { return JSON.parse(localStorage.getItem("loganate.tweaks") || "{}"); }
    catch { return {}; }
  }
  function writeTweaks(t) {
    localStorage.setItem("loganate.tweaks", JSON.stringify(t));
  }
  themeBtn?.addEventListener("click", () => {
    const cur = document.documentElement.dataset.theme || "light";
    const next = cur === "light" ? "dark" : "light";
    document.documentElement.dataset.theme = next;
    const t = readTweaks();
    t.theme = next;
    writeTweaks(t);
    // Persist via tweaks protocol too
    window.parent?.postMessage({ type: "__edit_mode_set_keys", edits: { theme: next } }, "*");
  });

  /* ---------- Code copy buttons ---------- */
  document.querySelectorAll("[data-copy]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const block = btn.closest(".codeblock");
      const pre = block?.querySelector("pre");
      if (!pre) return;
      const text = pre.innerText;
      try {
        await navigator.clipboard.writeText(text);
      } catch {
        // fallback
        const ta = document.createElement("textarea");
        ta.value = text; document.body.appendChild(ta);
        ta.select(); document.execCommand("copy"); ta.remove();
      }
      btn.classList.add("is-copied");
      const label = btn.querySelector("span");
      const prev = label.textContent;
      label.textContent = "Copied";
      setTimeout(() => {
        btn.classList.remove("is-copied");
        label.textContent = prev;
      }, 1400);
    });
  });

  /* ---------- Codeblock tabs (visual only) ---------- */
  document.querySelectorAll(".codeblock__tabs").forEach((tabs) => {
    tabs.querySelectorAll(".codeblock__tab").forEach((tab) => {
      tab.addEventListener("click", () => {
        tabs.querySelectorAll(".codeblock__tab").forEach((t) => t.setAttribute("aria-selected", "false"));
        tab.setAttribute("aria-selected", "true");
      });
    });
  });

  /* ---------- Mobile sidebar ---------- */
  const burger = document.getElementById("burger");
  const sidebar = document.getElementById("sidebar");
  burger?.addEventListener("click", () => {
    const open = sidebar.getAttribute("data-open") === "true";
    sidebar.setAttribute("data-open", open ? "false" : "true");
    burger.setAttribute("aria-expanded", open ? "false" : "true");
  });

  /* ---------- Sidebar links — set aria-current on click ---------- */
  document.querySelectorAll(".side-link").forEach((link) => {
    link.addEventListener("click", () => {
      document.querySelectorAll(".side-link").forEach((l) => l.removeAttribute("aria-current"));
      link.setAttribute("aria-current", "page");
    });
  });

  /* ---------- TOC scroll-spy ---------- */
  const tocLinks = document.querySelectorAll("#toc-list a");
  if (tocLinks.length) {
    const targets = [...tocLinks].map((a) => {
      const id = a.getAttribute("href").slice(1);
      return { link: a, el: document.getElementById(id) };
    }).filter((t) => t.el);

    const setActive = (id) => {
      tocLinks.forEach((a) => a.classList.toggle("is-active", a.getAttribute("href") === "#" + id));
    };

    const io = new IntersectionObserver((entries) => {
      // Pick the entry closest to top that is intersecting
      const visible = entries
        .filter((e) => e.isIntersecting)
        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (visible[0]) setActive(visible[0].target.id);
    }, { rootMargin: "-80px 0px -70% 0px", threshold: 0 });

    targets.forEach((t) => io.observe(t.el));
  }

  /* ---------- Search keyboard shortcut (visual only) ---------- */
  document.addEventListener("keydown", (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === "k") {
      e.preventDefault();
      document.querySelector(".search input")?.focus();
    }
  });

  /* ---------- Route from data-route links (intercept Home<->Docs jumps) ---------- */
  // (Native hashchange already handles this; just make sure clicks scroll to top)
  document.querySelectorAll("[data-route]").forEach((a) => {
    a.addEventListener("click", () => {
      // applyRoute() fires via hashchange
    });
  });

})();
