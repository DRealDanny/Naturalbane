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

/* --- UPDATED LIGHTBOX LOGIC --- */
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const zoomBtn = document.getElementById("zoomBtn");
const closeBtn = document.querySelector(".close-lightbox");

// 1. Open Lightbox
document.querySelectorAll(".img-preview img").forEach(image => {
  image.addEventListener("click", () => {
    lightbox.classList.remove("is-zoomed"); // Always reset zoom when opening
    lightbox.style.display = "flex";
    lightboxImg.src = image.src;
    document.body.style.overflow = "hidden";
  });
});

// 2. Toggle Zoom via SVG Button
zoomBtn.addEventListener("click", (e) => {
  e.stopPropagation(); // Prevents closing the lightbox when clicking the button
  lightbox.classList.toggle("is-zoomed");
});

// 3. Close Function
const closeLightbox = () => {
  lightbox.style.display = "none";
  lightbox.classList.remove("is-zoomed");
  document.body.style.overflow = "auto";
};

closeBtn.addEventListener("click", closeLightbox);
lightbox.addEventListener("click", (e) => {
  if (e.target === lightbox) closeLightbox();
});