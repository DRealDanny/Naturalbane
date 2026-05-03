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

/* --- LIGHTBOX PREVIEW LOGIC --- */
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const closeBtn = document.querySelector(".close-lightbox");

// Logic to open the lightbox
document.querySelectorAll(".img-preview img").forEach(image => {
  image.addEventListener("click", () => {
    lightbox.style.display = "flex";
    lightboxImg.src = image.src;
    document.body.style.overflow = "hidden"; // Stops the page from scrolling behind the image
  });
});

// Logic to close the lightbox
const closeLightbox = () => {
  lightbox.style.display = "none";
  document.body.style.overflow = "auto"; // Re-enables page scroll
};

if (closeBtn) {
  closeBtn.addEventListener("click", closeLightbox);
}

// Close if the user clicks the dark background area
lightbox.addEventListener("click", (e) => {
  if (e.target === lightbox) closeLightbox();
});