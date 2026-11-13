(function () {
  var root = document.getElementById("bumed-mobile-app");
  if (!root) return;

  var drawer = root.querySelector("[data-m-drawer]");
  var scrim = root.querySelector("[data-m-scrim]");
  var menuBtn = root.querySelector("[data-m-menu-open]");
  var menuClose = root.querySelector("[data-m-menu-close]");
  var search = root.querySelector("[data-m-search]");
  var searchBtn = root.querySelector("[data-m-search-open]");
  var searchClose = root.querySelector("[data-m-search-close]");
  var searchForm = root.querySelector("[data-m-search-form]");
  var searchResults = root.querySelector("[data-m-search-results]");
  var tabs = root.querySelectorAll("[data-m-tab]");
  var panels = root.querySelectorAll("[data-m-panel]");
  var quickTiles = root.querySelectorAll("[data-m-goto]");
  var logoLink = root.querySelector(".m-topbar-logo-link");

  var searchIndex = [
    { label: "Yaklaşan Etkinlikler", panel: "events", keywords: "etkinlik buluşma program" },
    { label: "BÜMED Akademi", panel: "akademi", keywords: "akademi eğitim program liderlik" },
    { label: "Duyurular ve Haberler", panel: "duyurular", keywords: "duyuru haber ilan" },
    { label: "Üyelik İşlemleri", panel: "uyelik", keywords: "üye aidat giriş üyelik" },
    { label: "Üye Ol", href: "https://online.bumed.org.tr/form/bumed-yeni-uyelik-formu-2026", keywords: "üye katıl kayıt" },
    { label: "Aidat Öde", href: "https://online.bumed.org.tr/odeme", keywords: "aidat ödeme yenileme" },
    { label: "BÜMED House", href: "https://bumed.webonalti.net/bumed/bumed-house", keywords: "house rezervasyon" },
    { label: "Boğaziçi Network", href: "https://bogazici.network/", keywords: "network mezun" },
    { label: "İletişim", href: "https://bumed.webonalti.net/bumed/iletisim", keywords: "iletişim form mail" }
  ];

  function setDrawer(open) {
    if (!drawer || !scrim) return;
    drawer.classList.toggle("is-open", open);
    scrim.classList.toggle("is-open", open);
    drawer.setAttribute("aria-hidden", open ? "false" : "true");
    if (menuBtn) menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.style.overflow = open || (search && search.classList.contains("is-open")) ? "hidden" : "";
  }

  function setSearch(open) {
    if (!search) return;
    search.classList.toggle("is-open", open);
    search.setAttribute("aria-hidden", open ? "false" : "true");
    if (searchBtn) searchBtn.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.style.overflow = open || (drawer && drawer.classList.contains("is-open")) ? "hidden" : "";
    if (open) {
      var input = search.querySelector("input[type='search']");
      if (input) {
        window.setTimeout(function () {
          input.focus();
        }, 120);
      }
    }
  }

  function activatePanel(name) {
    panels.forEach(function (panel) {
      var match = panel.getAttribute("data-m-panel") === name;
      panel.classList.toggle("is-active", match);
      panel.hidden = !match;
    });
    tabs.forEach(function (tab) {
      var on = tab.getAttribute("data-m-tab") === name;
      tab.classList.toggle("is-active", on);
      tab.setAttribute("aria-selected", on ? "true" : "false");
    });
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function runSearch(q) {
    if (!searchResults) return;
    var query = (q || "").trim().toLowerCase();
    searchResults.innerHTML = "";
    if (!query) return;
    var hits = searchIndex.filter(function (item) {
      return item.label.toLowerCase().indexOf(query) !== -1 || item.keywords.indexOf(query) !== -1;
    });
    if (!hits.length) {
      var li = document.createElement("li");
      li.textContent = "Sonuç bulunamadı. bumed.webonalti.net üzerinde aramayı deneyin.";
      li.style.padding = "0.65rem 0";
      li.style.fontSize = "0.8rem";
      li.style.color = "#56607a";
      searchResults.appendChild(li);
      return;
    }
    hits.forEach(function (item) {
      var li = document.createElement("li");
      var a = document.createElement("a");
      a.textContent = item.label;
      a.href = item.href || "#";
      if (item.panel) {
        a.addEventListener("click", function (e) {
          e.preventDefault();
          setSearch(false);
          activatePanel(item.panel);
        });
      } else {
        a.addEventListener("click", function () {
          setSearch(false);
        });
      }
      li.appendChild(a);
      searchResults.appendChild(li);
    });
  }

  if (logoLink) {
    logoLink.addEventListener("click", function (e) {
      e.preventDefault();
      activatePanel("home");
    });
  }

  if (menuBtn) menuBtn.addEventListener("click", function () { setDrawer(true); });
  if (menuClose) menuClose.addEventListener("click", function () { setDrawer(false); });
  if (scrim) scrim.addEventListener("click", function () { setDrawer(false); });

  if (searchBtn) searchBtn.addEventListener("click", function () { setSearch(true); });
  if (searchClose) searchClose.addEventListener("click", function () { setSearch(false); });

  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var input = searchForm.querySelector("input[type='search']");
      var q = input ? input.value : "";
      runSearch(q);
      if (q && window.matchMedia("(max-width: 900px)").matches) {
        var external = "https://bumed.webonalti.net/?s=" + encodeURIComponent(q);
        if (searchResults && !searchResults.children.length) {
          window.location.href = external;
        }
      }
    });
    var input = searchForm.querySelector("input[type='search']");
    if (input) {
      input.addEventListener("input", function () {
        runSearch(input.value);
      });
    }
  }

  tabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
      activatePanel(tab.getAttribute("data-m-tab"));
    });
  });

  quickTiles.forEach(function (el) {
    el.addEventListener("click", function (e) {
      var panel = el.getAttribute("data-m-goto");
      var href = el.getAttribute("data-m-href");
      if (panel) {
        e.preventDefault();
        activatePanel(panel);
        return;
      }
      if (href && el.tagName === "BUTTON") {
        e.preventDefault();
        window.location.href = href;
      }
    });
  });

  root.querySelectorAll("[data-m-section-all]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var panel = btn.getAttribute("data-m-section-all");
      if (panel) activatePanel(panel);
    });
  });

  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    setDrawer(false);
    setSearch(false);
  });

  activatePanel("home");
})();
