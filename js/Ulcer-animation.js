/* 
  ULCER RELIEF MASTERCLASS - GSAP ANIMATION ENGINE (V6)
  Update: Added a 0.8s load delay for a silkier, more professional start.
*/

window.addEventListener('load', function() {
    if (typeof gsap === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    let mm = gsap.matchMedia();

    // --- VISIBILITY BACKUP ---
    gsap.set(".hero-title, .hero-subhead, .hero-nudge", { opacity: 0 });
    gsap.set(".gsap-reveal", { opacity: 0, y: 30 });
    gsap.set(".gsap-silk img", { opacity: 0, scale: 1.05 });

    // --- DESKTOP & TABLET LOGIC ---
    mm.add("(min-width: 768px)", () => {
        
        // Added 'delay: 0.8' so it doesn't start too fast on load
        const heroTl = gsap.timeline({ 
            defaults: { ease: "expo.out" },
            delay: 0.8 
        });

        // 1. Headline: Slower duration (2.5s) for a silkier feel
        heroTl.fromTo(".hero-title", 
            { 
                clipPath: "inset(100% 0% 0% 0%)", 
                y: 100, 
                opacity: 0 
            }, 
            { 
                clipPath: "inset(0% 0% 0% 0%)", 
                y: 0, 
                opacity: 1, 
                duration: 2.5, 
                force3D: true 
            }
        )
        // 2. Subhead: Cascades in after the title is well underway[cite: 2]
        .fromTo(".hero-subhead", 
            { y: 30, opacity: 0 }, 
            { y: 0, opacity: 1, duration: 1.8 }, 
            "-=1.5" 
        )
        // 3. Nudge Icon: Soft fade at the end[cite: 2]
        .fromTo(".hero-nudge", 
            { opacity: 0, scale: 0.9 }, 
            { opacity: 1, scale: 1, duration: 1.2 }, 
            "-=1.0"
        );

        // Silk Image Reveals (Clip-path mask)
        gsap.utils.toArray(".gsap-silk img").forEach(img => {
            gsap.to(img, {
                scrollTrigger: { 
                    trigger: img, 
                    start: "top 88%", 
                    toggleActions: "play none none none" 
                },
                opacity: 1, 
                scale: 1, 
                clipPath: "inset(0% 0% 0% 0%)",
                duration: 2.2, 
                ease: "expo.inOut"
            });
        });
    });

    // --- MOBILE LOGIC ---
    mm.add("(max-width: 767px)", () => {
        
        const heroTl = gsap.timeline({ delay: 0.5 }); // Slight delay for mobile too

        heroTl.fromTo(".hero-title", { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 1.2, ease: "power2.out" })
              .fromTo(".hero-subhead", { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 1 }, "-=0.8")
              .fromTo(".hero-nudge", { opacity: 0 }, { opacity: 1, duration: 0.8 }, "-=0.6");

        gsap.utils.toArray(".gsap-silk img").forEach(img => {
            gsap.to(img, {
                scrollTrigger: { trigger: img, start: "top 92%" },
                opacity: 1, 
                duration: 1.5
            });
        });
    });

    // --- SHARED TEXT REVEALS ---
    gsap.utils.toArray(".gsap-reveal").forEach(el => {
        gsap.to(el, {
            scrollTrigger: { 
                trigger: el, 
                start: "top 94%", 
                toggleActions: "play none none none" 
            },
            y: 0, 
            opacity: 1, 
            duration: 1.2, 
            ease: "power2.out"
        });
    });

    // Staggered List Items
    gsap.to(".signs li, .class-list li", {
        scrollTrigger: { 
            trigger: ".signs, .class-list", 
            start: "top 85%" 
        },
        x: 0, 
        opacity: 1, 
        stagger: 0.12, 
        duration: 0.8, 
        ease: "power2.out"
    });
});