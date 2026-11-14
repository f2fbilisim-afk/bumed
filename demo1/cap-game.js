(function () {
  var root = document.getElementById("bumed-mobile-app");
  if (!root) return;

  var GRAVITY = 1900;
  var TAP_IMPULSE = 265;
  var MAX_UP_SPEED = 820;
  var CAP_SIZE = 60;
  var CONTACT_DELAY_MS = 1000;
  var RAPID_GAP_MS = 1000;

  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var state = "idle";
  var fabTapCount = 0;
  var fabTapTimer = null;
  var pendingContactToggle = null;

  var layer;
  var capEl;
  var trailEl;
  var briefEl;
  var toastEl;
  var winEl;
  var confettiEl;
  var winTimeEl;
  var rafId = 0;
  var lastTs = 0;
  var capY = 0;
  var velocity = 0;
  var bounds = { startY: 0, goalY: 0 };
  var gameStartMs = 0;
  var scrollLock = "";
  var fabGuideTimer = null;
  var briefShownAt = 0;

  function measureBounds() {
    var header = root.querySelector(".m-topbar");
    var tabbar = root.querySelector(".m-tabbar");
    var vv = window.visualViewport;
    var offsetTop = vv ? vv.offsetTop : 0;
    var goalY = offsetTop + 12;
    var startY = window.innerHeight - 100;
    if (header) {
      var hr = header.getBoundingClientRect();
      goalY = hr.bottom - 4;
    }
    if (tabbar) {
      var tr = tabbar.getBoundingClientRect();
      startY = tr.top - CAP_SIZE * 0.92;
    }
    return { startY: startY, goalY: goalY, centerX: window.innerWidth * 0.5 };
  }

  function buildUi() {
    layer = document.createElement("div");
    layer.className = "m-cap-game-layer";
    layer.setAttribute("aria-hidden", "true");
    layer.innerHTML =
      '<div class="m-cap-game-brief" data-cap-game-brief hidden role="dialog" aria-modal="true" aria-labelledby="m-cap-game-brief-line">' +
      '<div class="m-cap-game-brief-card">' +
      '<p class="m-cap-game-brief-kicker">Kepini Zirveye Fırlat!</p>' +
      '<p class="m-cap-game-brief-line" id="m-cap-game-brief-line">' +
      "Alttaki kepi art arda tıklayarak zirveye ulaştır, mezun ol!" +
      "</p></div></div>" +
      '<div class="m-cap-game-trail" data-cap-game-trail aria-hidden="true"></div>' +
      '<div class="m-cap-game-cap" data-cap-game-cap aria-hidden="true">' +
      '<i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>' +
      '<p class="m-cap-game-toast" data-cap-game-toast hidden></p>' +
      '<div class="m-cap-game-confetti" data-cap-game-confetti aria-hidden="true"></div>' +
      '<div class="m-cap-game-win" data-cap-game-win role="dialog" aria-modal="true" aria-labelledby="m-cap-game-win-title" hidden>' +
      '<div class="m-cap-game-win-card">' +
      '<button type="button" class="m-cap-game-win-close" data-cap-game-win-close aria-label="Kapat">' +
      '<i class="fa-solid fa-xmark" aria-hidden="true"></i></button>' +
      '<div class="m-cap-game-win-badge"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>' +
      '<p class="m-cap-game-win-kicker">TEBRİKLER!</p>' +
      '<h2 class="m-cap-game-win-title" id="m-cap-game-win-title">MEZUN OLDUN! 🎓</h2>' +
      '<p class="m-cap-game-win-lead">Boğaziçi ruhu hep seninle!</p>' +
      '<p class="m-cap-game-win-time" data-cap-game-win-time></p>' +
      '<p class="m-cap-game-win-note">Gizli oyunu tamamladın. Gerçek bir BÜMED&rsquo;li ruhu! 💙</p>' +
      '<div class="m-cap-game-win-actions">' +
      '<button type="button" class="m-cap-game-win-btn" data-cap-game-retry><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Tekrar Dene</button>' +
      "</div></div></div>";

    root.appendChild(layer);
    capEl = layer.querySelector("[data-cap-game-cap]");
    trailEl = layer.querySelector("[data-cap-game-trail]");
    briefEl = layer.querySelector("[data-cap-game-brief]");
    toastEl = layer.querySelector("[data-cap-game-toast]");
    winEl = layer.querySelector("[data-cap-game-win]");
    confettiEl = layer.querySelector("[data-cap-game-confetti]");
    winTimeEl = layer.querySelector("[data-cap-game-win-time]");

    layer.querySelector("[data-cap-game-win-close]").addEventListener("click", endGame);
    layer.querySelector("[data-cap-game-retry]").addEventListener("click", function () {
      closeWin();
      startPlaying();
    });

    layer.addEventListener(
      "pointerdown",
      function () {
        if (state === "playing") {
          endGame();
        } else if (state === "brief") {
          dismissBrief();
        }
      },
      { passive: true }
    );
  }

  function getFab() {
    return root.querySelector("[data-m-tabbar-fab]");
  }

  function clearFabGuide() {
    window.clearTimeout(fabGuideTimer);
    fabGuideTimer = null;
    var fab = getFab();
    if (fab) {
      fab.classList.remove("is-cap-game-guide", "is-cap-game-press");
    }
  }

  function enableFabGuide() {
    var fab = getFab();
    if (!fab) return;
    if (state !== "brief" && state !== "playing") return;
    fab.classList.add("is-cap-game-guide");
  }

  function scheduleFabGuide(delayMs) {
    window.clearTimeout(fabGuideTimer);
    fabGuideTimer = window.setTimeout(function () {
      enableFabGuide();
    }, delayMs);
  }

  function pulseFabPress() {
    var fab = getFab();
    if (!fab) return;
    fab.classList.remove("is-cap-game-press");
    void fab.offsetWidth;
    fab.classList.add("is-cap-game-press");
    window.setTimeout(function () {
      fab.classList.remove("is-cap-game-press");
    }, 380);
  }

  function boostCap() {
    pulseFabPress();
    velocity -= TAP_IMPULSE;
    if (velocity < -MAX_UP_SPEED) velocity = -MAX_UP_SPEED;
    pulseTrail();
  }

  function lockScroll(on) {
    if (on) {
      scrollLock = document.body.style.overflow;
      document.body.style.overflow = "hidden";
      root.style.touchAction = "none";
    } else {
      document.body.style.overflow = scrollLock;
      root.style.touchAction = "";
    }
  }

  function pulseTrail() {
    if (!trailEl || reducedMotion) return;
    trailEl.classList.add("is-on");
    window.setTimeout(function () {
      trailEl.classList.remove("is-on");
    }, 120);
  }

  function updateCapTransform() {
    if (!capEl) return;
    capEl.style.transform = "translate3d(0, " + capY + "px, 0)";
    if (trailEl && state === "playing") {
      var h = Math.max(0, bounds.startY + CAP_SIZE * 0.5 - capY);
      trailEl.style.top = capY + CAP_SIZE * 0.5 + "px";
      trailEl.style.height = h + "px";
    }
  }

  function showToast(msg) {
    if (!toastEl) return;
    toastEl.hidden = false;
    toastEl.textContent = msg;
    toastEl.classList.add("is-visible");
    window.setTimeout(function () {
      toastEl.classList.remove("is-visible");
      toastEl.hidden = true;
    }, 1600);
  }

  function stopLoop() {
    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = 0;
    }
  }

  function tick(ts) {
    if (state !== "playing") return;
    if (!lastTs) lastTs = ts;
    var dt = Math.min((ts - lastTs) / 1000, 0.032);
    lastTs = ts;

    velocity += GRAVITY * dt;
    capY += velocity * dt;

    if (capY <= bounds.goalY) {
      capY = bounds.goalY;
      stopLoop();
      onWin();
      return;
    }

    if (capY >= bounds.startY) {
      capY = bounds.startY;
      velocity = 0;
    }

    updateCapTransform();
    rafId = requestAnimationFrame(tick);
  }

  function startPlaying() {
    bounds = measureBounds();
    capY = bounds.startY;
    velocity = 0;
    lastTs = 0;
    state = "playing";
    gameStartMs = performance.now();
    layer.classList.add("is-active", "is-playing");
    layer.setAttribute("aria-hidden", "false");
    capEl.setAttribute("aria-hidden", "false");
    if (briefEl) {
      briefEl.hidden = true;
      briefEl.classList.remove("is-open");
    }
    var fab = getFab();
    if (fab && !fab.classList.contains("is-cap-game-guide")) {
      var wait = Math.max(0, 2000 - (Date.now() - briefShownAt));
      scheduleFabGuide(wait);
    }
    pulseFabPress();
    lockScroll(true);
    updateCapTransform();

    if (reducedMotion) {
      capY = bounds.goalY;
      updateCapTransform();
      onWin();
      return;
    }

    stopLoop();
    rafId = requestAnimationFrame(tick);
  }

  function spawnConfetti() {
    if (!confettiEl || reducedMotion) return;
    confettiEl.innerHTML = "";
    var colors = ["#10ede0", "#a8e61d", "#ffffff", "#10ede0"];
    for (var i = 0; i < 36; i++) {
      var p = document.createElement("i");
      p.style.left = Math.random() * 100 + "%";
      p.style.top = "-5%";
      p.style.background = colors[i % colors.length];
      p.style.setProperty("--dx", (Math.random() - 0.5) * 120 + "px");
      p.style.setProperty("--dy", 60 + Math.random() * 55 + "vh");
      p.style.animationDelay = Math.random() * 0.35 + "s";
      confettiEl.appendChild(p);
    }
  }

  function formatDuration(ms) {
    var s = ms / 1000;
    return s.toFixed(1).replace(".", ",") + " saniyede mezun oldun!";
  }

  function onWin() {
    state = "won";
    try {
      if (navigator.vibrate) navigator.vibrate([40, 35, 70]);
    } catch (err) {
      /* ignore */
    }
    capEl.classList.add("is-win-flash");
    spawnConfetti();
    var elapsed = performance.now() - gameStartMs;
    if (winTimeEl) winTimeEl.textContent = formatDuration(elapsed);
    winEl.hidden = false;
    winEl.classList.add("is-open");
  }

  function closeWin() {
    if (winEl) {
      winEl.classList.remove("is-open");
      winEl.hidden = true;
    }
    if (confettiEl) confettiEl.innerHTML = "";
    if (capEl) capEl.classList.remove("is-win-flash");
  }

  function endGame() {
    stopLoop();
    state = "idle";
    closeWin();
    layer.classList.remove("is-active", "is-playing");
    layer.setAttribute("aria-hidden", "true");
    if (capEl) capEl.setAttribute("aria-hidden", "true");
    if (briefEl) {
      briefEl.hidden = true;
      briefEl.classList.remove("is-open");
    }
    clearFabGuide();
    lockScroll(false);
  }

  function dismissBrief() {
    if (state !== "brief") return;
    state = "idle";
    layer.classList.remove("is-active", "is-brief");
    layer.setAttribute("aria-hidden", "true");
    if (briefEl) {
      briefEl.hidden = true;
      briefEl.classList.remove("is-open");
    }
    clearFabGuide();
    lockScroll(false);
  }

  function showBrief() {
    var fab = getFab();
    if (fab) {
      fab.classList.remove("is-cap-arming");
      void fab.offsetWidth;
      fab.classList.add("is-cap-arming");
    }
    state = "brief";
    briefShownAt = Date.now();
    layer.classList.add("is-active", "is-brief");
    layer.setAttribute("aria-hidden", "false");
    if (briefEl) {
      briefEl.hidden = false;
      briefEl.classList.add("is-open");
    }
    clearFabGuide();
    scheduleFabGuide(2000);
    lockScroll(true);
  }

  function registerFabTap() {
    var fab = root.querySelector("[data-m-tabbar-fab]");
    if (!fab) return;
    var lastFabTapMs = 0;

    fab.addEventListener(
      "pointerup",
      function (e) {
        if (e.pointerType === "mouse" && e.button !== 0) return;
        e.stopPropagation();

        if (state === "playing") {
          boostCap();
          return;
        }

        if (state === "brief") {
          startPlaying();
          return;
        }

        if (state === "won") {
          return;
        }

        var now = Date.now();
        if (now - lastFabTapMs > RAPID_GAP_MS) {
          fabTapCount = 0;
        }
        lastFabTapMs = now;
        fabTapCount += 1;

        fab.classList.remove("is-cap-arming");
        void fab.offsetWidth;
        fab.classList.add("is-cap-arming");
        window.clearTimeout(fabTapTimer);

        if (fabTapCount >= 3) {
          fabTapCount = 0;
          window.clearTimeout(fabTapTimer);
          showBrief();
          return;
        }

        fabTapTimer = window.setTimeout(function () {
          if (fabTapCount === 1 && pendingContactToggle) {
            pendingContactToggle();
          }
          fabTapCount = 0;
        }, CONTACT_DELAY_MS);
      },
      { passive: false }
    );
  }

  function onFabClick() {
    if (state === "playing" || state === "won" || state === "brief") {
      return true;
    }
    if (fabTapCount > 0) {
      return true;
    }
    return false;
  }

  window.BumedCapGame = {
    onFabClick: onFabClick,
    registerContactToggle: function (fn) {
      pendingContactToggle = fn;
    },
    isActive: function () {
      return state === "playing" || state === "won" || state === "brief";
    },
    endGame: endGame,
  };

  buildUi();
  registerFabTap();

  window.addEventListener(
    "resize",
    function () {
      if (state !== "playing") return;
      bounds = measureBounds();
      if (capY < bounds.goalY) capY = bounds.goalY;
      if (capY > bounds.startY) capY = bounds.startY;
      updateCapTransform();
    },
    { passive: true }
  );

  if (window.visualViewport) {
    window.visualViewport.addEventListener("resize", function () {
      if (state !== "playing") return;
      bounds = measureBounds();
      updateCapTransform();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      if (state === "playing" || state === "won") endGame();
      else if (state === "brief") dismissBrief();
    }
  });
})();
