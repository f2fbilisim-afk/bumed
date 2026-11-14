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
    { label: "E-Kartlar", href: "https://bumed.webonalti.net/uyelik/e-kart", keywords: "e-kart kart dijital" },
    { label: "İhtiyaç Haritası", href: "https://bumed.webonalti.net/mezunlar/ihtiyac-haritasi", keywords: "ihtiyaç harita" },
    { label: "BÜMED House konum", href: "https://www.google.com/maps/search/?api=1&query=BÜMED+House+Akat+Mah.+Hare+Sok.+No+3+Beşiktaş", keywords: "house konum adres maps" },
    { label: "Bize Katıl", href: "https://online.bumed.org.tr/form/bumed-yeni-uyelik-formu-2026", keywords: "bize katıl üye ol" },
    { label: "Üye Girişi", href: "https://online.bumed.org.tr/", keywords: "giriş login" },
    { label: "Üyelik Yenileme", href: "https://online.bumed.org.tr/odeme", keywords: "yenileme aidat" },
    { label: "Bağış Yap", href: "https://online.bumed.org.tr/bumed-e-destek-ol-tl", keywords: "bağış donate" },
    { label: "İletişim", href: "https://bumed.webonalti.net/bumed/iletisim", keywords: "iletişim form mail" },
    { label: "bumed@bumed.org.tr", href: "mailto:bumed@bumed.org.tr", keywords: "e-posta mail bumed" },
    { label: "0 212 359 58 00", href: "tel:+902123595800", keywords: "telefon arama iletişim" }
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
      if (tab.hasAttribute("aria-selected")) {
        tab.setAttribute("aria-selected", on ? "true" : "false");
      }
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
    var panelName = tab.getAttribute("data-m-tab");
    if (!panelName) return;
    tab.addEventListener("click", function () {
      activatePanel(panelName);
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

  (function initTabbarContact() {
    var tabbar = root.querySelector(".m-tabbar");
    var fab = root.querySelector("[data-m-tabbar-fab]");
    var pop = root.querySelector("[data-m-tabbar-contact]");
    var closeBtn = root.querySelector("[data-m-tabbar-contact-close]");
    if (!fab || !pop) return;

    function setContactOpen(open) {
      fab.classList.toggle("is-open", open);
      fab.setAttribute("aria-expanded", open ? "true" : "false");
      pop.hidden = !open;
    }

    if (window.BumedCapGame && window.BumedCapGame.registerContactToggle) {
      window.BumedCapGame.registerContactToggle(function () {
        setContactOpen(pop.hidden);
      });
    }

    fab.addEventListener("click", function (e) {
      e.stopPropagation();
      if (window.BumedCapGame && window.BumedCapGame.onFabClick(e)) {
        return;
      }
      setContactOpen(pop.hidden);
    });

    if (closeBtn) {
      closeBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        setContactOpen(false);
      });
    }

    document.addEventListener("click", function (e) {
      if (pop.hidden) return;
      if (tabbar && tabbar.contains(e.target)) return;
      setContactOpen(false);
    });
  })();

  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    setDrawer(false);
    setSearch(false);
    var fab = root.querySelector("[data-m-tabbar-fab]");
    var pop = root.querySelector("[data-m-tabbar-contact]");
    if (fab && pop && !pop.hidden) {
      fab.classList.remove("is-open");
      fab.setAttribute("aria-expanded", "false");
      pop.hidden = true;
    }
  });

  (function initMobileHero() {
    var hero = root.querySelector("[data-m-hero]");
    if (!hero) return;

    var slides = hero.querySelectorAll("[data-m-hero-slide]");
    var dots = hero.querySelectorAll(".m-hero-thumb");
    var kickerEl = hero.querySelector("[data-m-hero-kicker]");
    var titleEl = hero.querySelector("[data-m-hero-title]");
    var textEl = hero.querySelector("[data-m-hero-text]");
    var ctaEl = hero.querySelector("[data-m-hero-cta]");
    var index = 0;
    var timer = null;
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var slideMeta = [
      {
        pos: "62% 38%",
        kicker: "BOĞAZİÇİ RUHU",
        title: "Kuşaklar Boyu Süren Bir Bağ",
        text:
          "Boğaziçi Üniversitesi mezunlarını bir araya getiren, bağları güçlendiren, dayanışmayı büyüten mezunlar derneği.",
        cta: "BÜMED'i Keşfet",
        href: "https://bumed.webonalti.net/",
      },
      {
        pos: "50% 42%",
        kicker: "BÜMED ETKİNLİKLERİ",
        title: "BÜMED Etkinlikleri",
        text: "Kuşakları buluşturan, ilgi alanlarına hitap eden etkinliklerimizi keşfet.",
        cta: "Etkinlikleri Keşfet",
        href: "https://bumed.webonalti.net/etkinlikler",
      },
      {
        pos: "50% 42%",
        kicker: "BÜMED BÜYÜYOR",
        title: "Mezunlar Bir Arada",
        text: "Kampüste ve şehirde büyüyen mezun topluluğumuza katılın.",
        cta: "BÜMED'e Katıl",
        href: "https://online.bumed.org.tr/form/bumed-yeni-uyelik-formu-2026",
      },
    ];

    function applyCopy(meta) {
      if (kickerEl) kickerEl.textContent = meta.kicker;
      if (titleEl) titleEl.textContent = meta.title;
      if (textEl) textEl.textContent = meta.text;
      if (ctaEl) {
        ctaEl.href = meta.href;
        ctaEl.innerHTML =
          meta.cta + ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>';
      }
    }

    function syncSlidePositions() {
      for (var s = 0; s < slides.length; s++) {
        var pos = slideMeta[s] ? slideMeta[s].pos : slides[s].getAttribute("data-m-hero-pos");
        if (pos) slides[s].style.objectPosition = pos;
      }
    }

    function goTo(i) {
      index = (i + slides.length) % slides.length;
      var meta = slideMeta[index] || slideMeta[0];
      applyCopy(meta);
      syncSlidePositions();
      for (var s = 0; s < slides.length; s++) {
        slides[s].classList.toggle("is-active", s === index);
      }
      for (var d = 0; d < dots.length; d++) {
        var on = d === index;
        dots[d].classList.toggle("is-active", on);
        dots[d].setAttribute("aria-selected", on ? "true" : "false");
      }
    }

    function schedule() {
      if (timer) clearInterval(timer);
      if (reduceMotion) return;
      timer = setInterval(function () {
        goTo(index + 1);
      }, 3000);
    }

    function userGo(i) {
      goTo(i);
      schedule();
    }

    for (var di = 0; di < dots.length; di++) {
      (function (dotIndex) {
        dots[dotIndex].addEventListener("click", function (e) {
          e.preventDefault();
          userGo(dotIndex);
        });
      })(di);
    }

    hero.addEventListener("focusin", function () {
      if (timer) clearInterval(timer);
    });
    hero.addEventListener("focusout", schedule);

    goTo(0);
    schedule();
  })();

  (function initMobileEventCarousel() {
    var tracks = root.querySelectorAll(".m-events-scroll--more");
    if (!tracks.length) return;
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion) return;

    tracks.forEach(function (track) {
      setInterval(function () {
        var card = track.querySelector(".m-event-card");
        if (!card) return;
        var gap = 10;
        var step = card.offsetWidth + gap;
        var maxScroll = track.scrollWidth - track.clientWidth - 2;
        if (maxScroll <= 0) return;
        if (track.scrollLeft >= maxScroll) {
          track.scrollTo({ left: 0, behavior: "smooth" });
        } else {
          track.scrollBy({ left: step, behavior: "smooth" });
        }
      }, 4200);
    });
  })();

  activatePanel("home");
})();
