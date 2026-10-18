// Mobile Nav
const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');
const overlay = document.createElement('div');
overlay.className = 'nav-overlay';
document.body.appendChild(overlay);

function toggleMenu() {
  navToggle.classList.toggle('active');
  navMenu.classList.toggle('active');
  overlay.classList.toggle('active');
  document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
}

navToggle.addEventListener('click', toggleMenu);
overlay.addEventListener('click', toggleMenu);

// Close mobile menu on link click
document.querySelectorAll('.nav-menu .nav-link:not(.dropdown-toggle)').forEach(link => {
  link.addEventListener('click', () => {
    if (navMenu.classList.contains('active')) toggleMenu();
  });
});

// Dropdown on mobile (click instead of hover)
const dropdownToggle = document.querySelector('.dropdown-toggle');
const dropdownMenu = document.querySelector('.dropdown-menu');

if (window.innerWidth <= 768) {
  dropdownToggle.addEventListener('click', (e) => {
    e.preventDefault();
    dropdownMenu.classList.toggle('show');
  });
}

// Update dropdown behavior on resize
window.addEventListener('resize', () => {
  if (window.innerWidth <= 768) {
    dropdownToggle.addEventListener('click', (e) => {
      e.preventDefault();
      dropdownMenu.classList.toggle('show');
    });
  } else {
    dropdownMenu.classList.remove('show');
  }
});

// Scroll animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.15 });

document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

// Navbar scroll effect
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// Active link on scroll
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(section => {
    const top = section.offsetTop - 120;
    if (window.scrollY >= top) {
      current = section.getAttribute('id');
    }
  });
  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') === `#${current}`) {
      link.classList.add('active');
    }
  });
});

// Animated counters
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const el = entry.target;
      const target = parseInt(el.getAttribute('data-target'));
      animateCounter(el, target);
      counterObserver.unobserve(el);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('.number-value[data-target]').forEach(el => {
  counterObserver.observe(el);
});

function animateCounter(el, target) {
  let current = 0;
  const increment = Math.ceil(target / 60);
  const timer = setInterval(() => {
    current += increment;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }
    el.textContent = current.toLocaleString();
  }, 25);
}
