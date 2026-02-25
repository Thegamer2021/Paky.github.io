const photoNavToggle = document.getElementById("photoNavToggle");
const photoNav = document.getElementById("photoNav");
const photoLinks = Array.from(document.querySelectorAll(".photo-nav a"));
const reveals = Array.from(document.querySelectorAll(".reveal"));
const filterButtons = Array.from(document.querySelectorAll(".filter-btn"));
const cards = Array.from(document.querySelectorAll(".photo-card"));
const openButtons = Array.from(document.querySelectorAll(".photo-open"));
const lightbox = document.getElementById("lightbox");
const lightboxImage = document.getElementById("lightboxImage");
const lightboxCaption = document.getElementById("lightboxCaption");
const lightboxClose = document.getElementById("lightboxClose");
const photoYear = document.getElementById("photoYear");
const counters = Array.from(document.querySelectorAll("[data-counter]"));
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

if (photoYear) {
  photoYear.textContent = String(new Date().getFullYear());
}

if (photoNavToggle && photoNav) {
  photoNavToggle.addEventListener("click", () => {
    const isOpen = photoNav.classList.toggle("is-open");
    photoNavToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

photoLinks.forEach((link) => {
  link.addEventListener("click", () => {
    if (photoNav) {
      photoNav.classList.remove("is-open");
    }
    if (photoNavToggle) {
      photoNavToggle.setAttribute("aria-expanded", "false");
    }
  });
});

if (!reducedMotion && "IntersectionObserver" in window) {
  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.18 }
  );

  reveals.forEach((item) => revealObserver.observe(item));
} else {
  reveals.forEach((item) => item.classList.add("is-visible"));
}

function animateCounter(el, target, duration = 1200) {
  if (reducedMotion) {
    el.textContent = String(target);
    return;
  }

  let startTime = null;
  const frame = (time) => {
    if (!startTime) {
      startTime = time;
    }
    const progress = Math.min((time - startTime) / duration, 1);
    const value = Math.floor(target * (1 - Math.pow(1 - progress, 3)));
    el.textContent = String(value);

    if (progress < 1) {
      requestAnimationFrame(frame);
    }
  };

  requestAnimationFrame(frame);
}

if (counters.length > 0 && "IntersectionObserver" in window) {
  const counterObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        const target = Number(entry.target.getAttribute("data-counter") || 0);
        animateCounter(entry.target, target);
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.6 }
  );

  counters.forEach((counter) => counterObserver.observe(counter));
} else {
  counters.forEach((counter) => {
    const value = Number(counter.getAttribute("data-counter") || 0);
    counter.textContent = String(value);
  });
}

filterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const selected = button.dataset.filter || "all";

    filterButtons.forEach((btn) => btn.classList.toggle("is-active", btn === button));

    cards.forEach((card) => {
      const category = card.dataset.category || "";
      const show = selected === "all" || category === selected;
      card.classList.toggle("is-hidden", !show);
    });
  });
});

function closeLightbox() {
  if (!lightbox) {
    return;
  }

  lightbox.classList.remove("is-open");
  lightbox.setAttribute("aria-hidden", "true");
  document.body.classList.remove("no-scroll");
}

openButtons.forEach((button) => {
  button.addEventListener("click", () => {
    if (!lightbox || !lightboxImage || !lightboxCaption) {
      return;
    }

    const src = button.getAttribute("data-src") || "";
    const caption = button.getAttribute("data-caption") || "";

    lightboxImage.src = src;
    lightboxImage.alt = caption;
    lightboxCaption.textContent = caption;
    lightbox.classList.add("is-open");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.classList.add("no-scroll");
  });
});

if (lightboxClose) {
  lightboxClose.addEventListener("click", closeLightbox);
}

if (lightbox) {
  lightbox.addEventListener("click", (event) => {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeLightbox();
  }
});

const navSections = photoLinks
  .map((link) => {
    const href = link.getAttribute("href") || "";
    if (!href.startsWith("#")) {
      return null;
    }
    const section = document.querySelector(href);
    if (!section) {
      return null;
    }
    return { link, section };
  })
  .filter(Boolean);

function updateActiveNav() {
  const pointer = window.scrollY + 120;

  navSections.forEach(({ link, section }) => {
    const start = section.offsetTop;
    const end = start + section.offsetHeight;
    const active = pointer >= start && pointer < end;
    link.classList.toggle("active", active);
  });
}

window.addEventListener("scroll", updateActiveNav, { passive: true });
updateActiveNav();
