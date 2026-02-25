const body = document.body;
const navToggle = document.getElementById("navToggle");
const siteNav = document.getElementById("siteNav");
const navLinks = Array.from(document.querySelectorAll(".site-nav a"));
const revealItems = Array.from(document.querySelectorAll(".reveal"));
const yearNode = document.getElementById("currentYear");
const roleRotator = document.getElementById("roleRotator");
const tiltCards = Array.from(document.querySelectorAll(".tilt-card"));
const pointerGlow = document.getElementById("pointerGlow");
const formNotice = document.getElementById("formNotice");
const contactForm = document.getElementById("contactForm");
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

if (yearNode) {
  yearNode.textContent = String(new Date().getFullYear());
}

if (navToggle && siteNav) {
  navToggle.addEventListener("click", () => {
    const isOpen = siteNav.classList.toggle("is-open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    if (siteNav) {
      siteNav.classList.remove("is-open");
    }

    if (navToggle) {
      navToggle.setAttribute("aria-expanded", "false");
    }
  });
});

if (supportsRevealAnimation()) {
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
    { threshold: 0.2 }
  );

  revealItems.forEach((item) => revealObserver.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add("is-visible"));
}

const sectionAnchors = navLinks
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

function updateActiveLink() {
  const scrollPoint = window.scrollY + 140;

  sectionAnchors.forEach(({ link, section }) => {
    const start = section.offsetTop;
    const end = start + section.offsetHeight;
    const isActive = scrollPoint >= start && scrollPoint < end;
    link.classList.toggle("active", isActive);
  });
}

function updateHeaderState() {
  body.classList.toggle("is-scrolled", window.scrollY > 12);
}

window.addEventListener("scroll", updateActiveLink, { passive: true });
window.addEventListener("scroll", updateHeaderState, { passive: true });
updateActiveLink();
updateHeaderState();

const rotatingRoles = [
  "Fotografo (hobbista) + Dronista",
  "Developer: DiscordJS, HTML, PHP, MySQL, JavaScript",
  "Gestione database",
  "Pianista"
];

if (roleRotator && !reducedMotion) {
  let index = 0;
  setInterval(() => {
    index = (index + 1) % rotatingRoles.length;
    roleRotator.style.opacity = "0";

    setTimeout(() => {
      roleRotator.textContent = rotatingRoles[index];
      roleRotator.style.opacity = "1";
    }, 190);
  }, 2800);
}

if (!reducedMotion) {
  tiltCards.forEach((card) => {
    card.addEventListener("mousemove", (event) => {
      const rect = card.getBoundingClientRect();
      const px = (event.clientX - rect.left) / rect.width;
      const py = (event.clientY - rect.top) / rect.height;
      const rotateY = (px - 0.5) * 10;
      const rotateX = (0.5 - py) * 8;
      card.style.transform = `perspective(920px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    card.addEventListener("mouseleave", () => {
      card.style.transform = "";
    });
  });
}

if (pointerGlow && !reducedMotion && window.matchMedia("(pointer: fine)").matches) {
  const position = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
  const mouse = { x: position.x, y: position.y };

  window.addEventListener("mousemove", (event) => {
    mouse.x = event.clientX;
    mouse.y = event.clientY;
  });

  function renderGlow() {
    position.x += (mouse.x - position.x) * 0.14;
    position.y += (mouse.y - position.y) * 0.14;
    pointerGlow.style.left = `${position.x}px`;
    pointerGlow.style.top = `${position.y}px`;
    requestAnimationFrame(renderGlow);
  }

  requestAnimationFrame(renderGlow);
}

if (contactForm && formNotice) {
  const fields = Array.from(contactForm.querySelectorAll("input, textarea"));
  fields.forEach((field) => {
    field.addEventListener("input", () => {
      if (formNotice.classList.contains("error")) {
        formNotice.textContent = "";
        formNotice.className = "form-notice";
      }
    });
  });
}

function supportsRevealAnimation() {
  if (reducedMotion) {
    return false;
  }

  return "IntersectionObserver" in window;
}
