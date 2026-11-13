(function () {
  var root = document.querySelector(".home-carousel--etkinlik");
  if (!root) return;

  var track = root.querySelector("[data-carousel-track]");
  if (!track) return;

  var waveSvg =
    '<span class="event-up-wave" aria-hidden="true">' +
    '<svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
    '<path d="M0 28 C200 4 400 44 600 22 S1000 6 1200 26 L1200 48 L0 48 Z" fill="currentColor" /></svg></span>';

  function esc(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/"/g, "&quot;");
  }

  function cardHtml(ev) {
    var img = esc(ev.image || "");
    var title = esc(ev.title || "");
    var url = esc(ev.url || "#");
    var alt = esc(ev.title || "Etkinlik görseli");
    var poster = ev.poster
      ? " event-up-media--poster event-up-media--poster-" + esc(String(ev.poster))
      : "";

    return (
      '<article class="event-up-card">' +
      '<a class="event-up-link" href="' +
      url +
      '">' +
      '<div class="event-up-media event-up-media--banner' +
      poster +
      '">' +
      '<img src="' +
      img +
      '" alt="' +
      alt +
      '" width="640" height="360" loading="lazy" decoding="async" />' +
      '<time class="event-up-date" datetime="' +
      esc(ev.date || "") +
      '">' +
      esc(ev.dateDay || "") +
      "<span>" +
      esc(ev.dateMonth || "") +
      "</span></time>" +
      "</div>" +
      waveSvg +
      '<div class="event-up-body">' +
      '<p class="event-up-meta"><i class="fa-regular fa-clock" aria-hidden="true"></i> ' +
      esc(ev.time || "") +
      "</p>" +
      "<h3>" +
      title +
      "</h3>" +
      '<p class="event-up-meta"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' +
      esc(ev.location || "") +
      "</p>" +
      "</div></a></article>"
    );
  }

  function remountCarousel(events) {
    var list = Array.isArray(events) ? events.slice() : [];
    if (!list.length) return;

    track.innerHTML = list.map(cardHtml).join("");

    if (typeof window.bumedResetCarousel === "function") {
      window.bumedResetCarousel(track);
    }
    if (typeof window.bumedInitCarousel === "function") {
      window.bumedInitCarousel(track);
    }
  }

  fetch("events-feed.php", { credentials: "same-origin" })
    .then(function (res) {
      return res.ok ? res.json() : Promise.reject(new Error("feed"));
    })
    .then(function (data) {
      if (data && Array.isArray(data.events) && data.events.length) {
        remountCarousel(data.events);
      }
    })
    .catch(function () {
      /* Statik HTML + initHomeCarousel yeterli. */
    });
})();
