/*
  ULCER RELIEF MASTERCLASS - GSAP ANIMATION ENGINE (V7)
  
  PERFORMANCE FIXES from PageSpeed audit:
  
  1. FORCED REFLOW FIX (Image 3 in audit)
     The reflow was caused by GSAP's ScrollTrigger calling offsetWidth/getBoundingClientRect
     inside scroll callbacks while styles were mid-update.
     Fix: All ScrollTrigger instances now use lazy:true so GSAP batches
     layout reads outside the style invalidation cycle.
  
  2. REMOVED window.addEventListener('load') WRAPPER
     With defer on the script tags, the scripts already execute after
     the DOM is ready — the load wrapper was adding an unnecessary extra
     wait on top of defer.
*/

(function() {
    if (typeof gsap === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    // Tell ScrollTrigger to batch DOM reads — prevents forced reflow
    ScrollTrigger.config({ limitCallbacks: true });

    const mm = gsap.matchMedia();

    // Initial hidden states
    gsap.set('.hero-title, .hero-subhead, .hero-nudge', { opacity: 0 });
    gsap.set('.gsap-reveal', { opacity: 0, y: 30 });
    gsap.set('.gsap-silk img', { opacity: 0, scale: 1.05 });

    // ── DESKTOP & TABLET ──
    mm.add('(min-width: 768px)', () => {

        const heroTl = gsap.timeline({
            defaults: { ease: 'expo.out' },
            delay: 0.8,
        });

        heroTl
            .fromTo('.hero-title',
                { clipPath: 'inset(100% 0% 0% 0%)', y: 100, opacity: 0 },
                { clipPath: 'inset(0% 0% 0% 0%)', y: 0, opacity: 1, duration: 2.5, force3D: true }
            )
            .fromTo('.hero-subhead',
                { y: 30, opacity: 0 },
                { y: 0, opacity: 1, duration: 1.8 },
                '-=1.5'
            )
            .fromTo('.hero-nudge',
                { opacity: 0, scale: 0.9 },
                { opacity: 1, scale: 1, duration: 1.2 },
                '-=1.0'
            );

        // Silk image reveals — lazy:true batches the DOM read
        gsap.utils.toArray('.gsap-silk img').forEach(img => {
            gsap.to(img, {
                scrollTrigger: {
                    trigger: img,
                    start: 'top 88%',
                    toggleActions: 'play none none none',
                    lazy: true,          // ← prevents forced reflow
                },
                opacity: 1,
                scale: 1,
                clipPath: 'inset(0% 0% 0% 0%)',
                duration: 2.2,
                ease: 'expo.inOut',
            });
        });
    });

    // ── MOBILE ──
    mm.add('(max-width: 767px)', () => {

        const heroTl = gsap.timeline({ delay: 0.5 });

        heroTl
            .fromTo('.hero-title',
                { y: 30, opacity: 0 },
                { y: 0, opacity: 1, duration: 1.2, ease: 'power2.out' }
            )
            .fromTo('.hero-subhead',
                { y: 20, opacity: 0 },
                { y: 0, opacity: 1, duration: 1 },
                '-=0.8'
            )
            .fromTo('.hero-nudge',
                { opacity: 0 },
                { opacity: 1, duration: 0.8 },
                '-=0.6'
            );

        gsap.utils.toArray('.gsap-silk img').forEach(img => {
            gsap.to(img, {
                scrollTrigger: {
                    trigger: img,
                    start: 'top 92%',
                    lazy: true,          // ← prevents forced reflow
                },
                opacity: 1,
                duration: 1.5,
            });
        });
    });

    // ── SHARED TEXT REVEALS ──
    gsap.utils.toArray('.gsap-reveal').forEach(el => {
        gsap.to(el, {
            scrollTrigger: {
                trigger: el,
                start: 'top 94%',
                toggleActions: 'play none none none',
                lazy: true,              // ← prevents forced reflow
            },
            y: 0,
            opacity: 1,
            duration: 1.2,
            ease: 'power2.out',
        });
    });

    // ── STAGGERED LIST ITEMS ──
    gsap.utils.toArray('.signs li, .class-list li').forEach(li => {
        gsap.set(li, { opacity: 0, x: -20 });
    });

    // Use a single ScrollTrigger for the whole list rather than one per item
    // — fewer triggers = fewer layout reads = less reflow
    ScrollTrigger.batch('.signs li, .class-list li', {
        start: 'top 90%',
        lazy: true,
        onEnter: batch => {
            gsap.to(batch, {
                opacity: 1,
                x: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: 'power2.out',
            });
        },
    });

})();