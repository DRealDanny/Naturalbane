const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('visible');
    });
  }, { threshold: 0.1 });
  
  document.querySelectorAll('.doctor, .signs, .bonus-block').forEach(el => {
    el.classList.add('reveal');
    observer.observe(el);
  });

  /* --- SCROLL TO TOP FUNCTIONALITY --- */
const scrollTopBtn = document.getElementById("scrollTopBtn");

// Show button when scrolled down 300px
window.addEventListener("scroll", () => {
  if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
    scrollTopBtn.style.display = "block";
  } else {
    scrollTopBtn.style.display = "none";
  }
});

// Smooth scroll to top when clicked
scrollTopBtn.addEventListener("click", () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth"
  });
});