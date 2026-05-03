const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('visible');
    });
  }, { threshold: 0.1 });
  
  document.querySelectorAll('.doctor, .signs, .bonus-block').forEach(el => {
    el.classList.add('reveal');
    observer.observe(el);
  });