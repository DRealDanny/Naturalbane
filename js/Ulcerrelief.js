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

/* --- ADVANCED LIGHTBOX: ZOOM & GRAB-TO-PAN --- */
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const zoomBtn = document.getElementById("zoomBtn");
const closeBtn = document.querySelector(".close-lightbox");

let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

// 1. Open Lightbox
document.querySelectorAll(".img-preview img").forEach(image => {
  image.addEventListener("click", () => {
    resetZoom();
    lightbox.style.display = "flex";
    lightboxImg.src = image.src;
    document.body.style.overflow = "hidden";
  });
});

// 2. Toggle Zoom
zoomBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  if (lightbox.classList.contains("is-zoomed")) {
    resetZoom();
  } else {
    lightbox.classList.add("is-zoomed");
    translateX = 0;
    translateY = 0;
    updateImageTransform();
  }
});

// 3. Panning Logic (Mouse & Touch)
const startPan = (e) => {
  if (!lightbox.classList.contains("is-zoomed")) return;
  isDragging = true;
  startX = (e.pageX || e.touches[0].pageX) - translateX;
  startY = (e.pageY || e.touches[0].pageY) - translateY;
  lightboxImg.style.transition = "none"; // Remove transition for instant response
};

const doPan = (e) => {
  if (!isDragging) return;
  e.preventDefault();
  translateX = (e.pageX || e.touches[0].pageX) - startX;
  translateY = (e.pageY || e.touches[0].pageY) - startY;
  updateImageTransform();
};

const stopPan = () => {
  isDragging = false;
  lightboxImg.style.transition = "transform 0.1s ease-out";
};

// Event Listeners for Panning
lightboxImg.addEventListener("mousedown", startPan);
window.addEventListener("mousemove", doPan);
window.addEventListener("mouseup", stopPan);

lightboxImg.addEventListener("touchstart", startPan);
window.addEventListener("touchmove", doPan);
window.addEventListener("touchend", stopPan);

// 4. Helpers
function updateImageTransform() {
  const scale = lightbox.classList.contains("is-zoomed") ? 2.5 : 1;
  lightboxImg.style.transform = `scale(${scale}) translate(${translateX / scale}px, ${translateY / scale}px)`;
}

function resetZoom() {
  lightbox.classList.remove("is-zoomed");
  translateX = 0;
  translateY = 0;
  lightboxImg.style.transform = "scale(1) translate(0, 0)";
  lightboxImg.style.transition = "transform 0.3s ease";
}

const closeLightbox = () => {
  lightbox.style.display = "none";
  resetZoom();
  document.body.style.overflow = "auto";
};

closeBtn.addEventListener("click", closeLightbox);
lightbox.addEventListener("click", (e) => {
  if (e.target === lightbox) closeLightbox();
});