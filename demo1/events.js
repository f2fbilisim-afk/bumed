(function () {
  var root = document.querySelector(".bumed-events");
  if (!root || typeof Swiper === "undefined") return;

  var wrapper = root.querySelector(".events-swiper .swiper-wrapper");
  var swiperEl = root.querySelector(".events-swiper");
  var prevEl = root.querySelector(".events-prev");
  var nextEl = root.querySelector(".events-next");
  if (!wrapper || !swiperEl) return;

  var waveSvg =
    '<span class="event-card-wave" aria-hidden="true">' +
    '<svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
    '<path d="M0 28 C200 4 400 44 600 22 S1000 6 1200 26 L1200 48 L0 48 Z" fill="currentColor" /></svg></span>';

  function esc(s) {
  return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/"/g, "&quot;");
  }

  function slideHtml(ev) {
    var img = esc(ev.image || "");
    var title = esc(ev.title || "");
    var url = esc(ev.url || "#");
    var alt = esc(ev.title || "Etkinlik görseli");
    var poster = ev.poster ? " event-card-image--poster event-card-image--poster-" + esc(String(ev.poster)) : "";

    return (
      '<div class="swiper-slide">' +
      '<article class="event-card">' +
      '<a class="event-card-link" href="' +
      url +
      '">' +
      '<div class="event-card-image' +
      poster +
      '">' +
      '<img src="' +
      img +
      '" alt="' +
      alt +
      '" width="640" height="360" loading="lazy" decoding="async" />' +
      '<time class="event-card-date" datetime="' +
      esc(ev.date || "") +
      '">' +
      esc(ev.dateDay || "") +
      "<span>" +
      esc(ev.dateMonth || "") +
      "</span></time>" +
      waveSvg +
      "</div>" +
      '<div class="event-card-content">' +
      '<p class="event-card-time"><i class="fa-regular fa-clock" aria-hidden="true"></i> ' +
      esc(ev.time || "") +
      "</p>" +
      '<h3 class="event-card-title">' +
      title +
      "</h3>" +
      '<p class="event-card-location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' +
      esc(ev.location || "") +
      "</p>" +
      "</div></a></article></div>"
    );
  }

  var swiperInstance = null;

  /** Swiper loop için yeterli slayt (masaüstü 3 görünümde akış devam etsin). */
  function expandForLoop(list) {
    var base = list.slice();
    if (base.length < 1) return base;
    while (base.length < 6) {
      base = base.concat(list);
    }
    return base;
  }

  function duplicateDomSlides() {
    var slides = wrapper.querySelectorAll(".swiper-slide:not([data-loop-clone])");
    if (slides.length >= 6) return;
    slides.forEach(function (sl) {
      var dup = sl.cloneNode(true);
      dup.setAttribute("data-loop-clone", "1");
      dup.setAttribute("aria-hidden", "true");
      wrapper.appendChild(dup);
    });
  }

  function mountSwiper(events) {
    var list = Array.isArray(events) ? events.slice() : [];
    if (!list.length) return;

    list = expandForLoop(list);

    wrapper.innerHTML = list.map(slideHtml).join("");

    if (swiperInstance) {
      swiperInstance.destroy(true, true);
      swiperInstance = null;
    }

    swiperInstance = new Swiper(swiperEl, {
      slidesPerView: 1,
      spaceBetween: 16,
      slidesPerGroup: 1,
      loop: true,
      speed: 800,
      watchOverflow: false,
      grabCursor: true,
      autoplay: {
        delay: 6500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: nextEl,
        prevEl: prevEl,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 20,
          slidesPerGroup: 1,
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 28,
          slidesPerGroup: 1,
        },
      },
    });

    if (swiperInstance.autoplay) {
      swiperInstance.autoplay.start();
    }
  }

  fetch("events-feed.php", { credentials: "same-origin" })
    .then(function (res) {
      return res.ok ? res.json() : Promise.reject(new Error("feed"));
    })
    .then(function (data) {
      if (data && Array.isArray(data.events) && data.events.length) {
        mountSwiper(data.events);
      } else {
        mountSwiperFromExisting();
      }
    })
    .catch(function () {
      mountSwiperFromExisting();
    });

  function mountSwiperFromExisting() {
    var cards = wrapper.querySelectorAll(".swiper-slide");
    if (!cards.length) return;
    if (swiperInstance) return;

    duplicateDomSlides();

    swiperInstance = new Swiper(swiperEl, {
      slidesPerView: 1,
      spaceBetween: 16,
      slidesPerGroup: 1,
      loop: true,
      speed: 800,
      watchOverflow: false,
      grabCursor: true,
      autoplay: {
        delay: 6500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: nextEl,
        prevEl: prevEl,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 20,
          slidesPerGroup: 1,
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 28,
          slidesPerGroup: 1,
        },
      },
    });

    if (swiperInstance.autoplay) {
      swiperInstance.autoplay.start();
    }
  }
})();
