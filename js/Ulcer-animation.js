/* 
  ULCER RELIEF MASTERCLASS - PREMIUM HERO ENTRANCE (V3)
  Style: Editorial Masked Reveal with 3D Depth.
*/

window.addEventListener('load', function() {
    if (typeof gsap === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    let mm = gsap.matchMedia();

    // --- VISIBILITY BACKUP ---
    // Only hides content if GSAP is active and running
    gsap.set(".hero h1, .hero .subhead, .scroll-nudge", { opacity: 0 });
    gsap.set(".gsap-reveal", { opacity: 0, y: 30 });
    gsap.set(".gsap-silk img", { opacity: 0, scale: 1.1 });

    // --- RESPONSIVE ANIMATIONS ---
    mm.add("(min-width: 768px)", () => {
        
        // THE HERO "SILK" TIMELINE
        const heroTl = gsap.timeline({ defaults: { ease: "expo.out" } });

        heroTl.fromTo(".hero h1", 
            { 
                clipPath: "inset(100% 0% 0% 0%)", 
                y: 100, 
                opacity: 0,
                scale: 1.05
            }, 
            { 
                clipPath: "inset(0% 0% 0% 0%)", 
                y: 0, 
                opacity: 1, 
                scale: 1,
                duration: 2,
                force3D: true 
            }
        )
        .fromTo(".hero .subhead", 
            { 
                y: 40, 
                opacity: 0 
            }, 
            { 
                y: 0, 
                opacity: 1, 
                duration: 1.5 
            }, 
            "-=1.4"
        )
        .fromTo(".scroll-nudge", 
            { 
                y: -20, 
                opacity: 0 
            }, 
            { 
                y: 0, 
                opacity: 1, 
                duration: 1 
            }, 
            "-=1.0"
        );

        // SILK IMAGE REVEALS
        gsap.utils.toArray(".gsap-silk img").forEach(img => {
            gsap.to(img, {
                scrollTrigger: { 
                    trigger: img, 
                    start: "top 85%", 
                    toggleActions: "play none none none" 
                },
                opacity: 1,
                scale: 1,
                clipPath: "inset(0% 0% 0% 0%)",
                duration: 2,
                ease: "expo.inOut"
            });
        });
    });

    mm.add("(max-width: 767px)", () => {
        
        // SNAPPY MOBILE HERO[cite: 3]
        const heroTl = gsap.timeline();

        heroTl.fromTo(".hero h1", 
            { 
                y: 50, 
                opacity: 0 
            }, 
            { 
                y: 0, 
                opacity: 1, 
                duration: 1.2, 
                ease: "power3.out" 
            }
        )
        .fromTo(".hero .subhead", 
            { 
                y: 20, 
                opacity: 0 
            }, 
            { 
                y: 0, 
                opacity: 1, 
                duration: 1 
            }, 
            "-=0.9"
        );

        // MOBILE IMAGE FADE (Lighter on GPU)[cite: 3]
        gsap.utils.toArray(".gsap-silk img").forEach(img => {
            gsap.to(img, {
                scrollTrigger: { 
                    trigger: img, 
                    start: "top 90%" 
                },
                opacity: 1,
                scale: 1,
                duration: 1.2
            });
        });
    });

    // --- SHARED TEXT REVEALS ---
    gsap.utils.toArray(".gsap-reveal").forEach(el => {
        gsap.to(el, {
            scrollTrigger: { 
                trigger: el, 
                start: "top 92%", 
                toggleActions: "play none none none" 
            },
            y: 0,
            opacity: 1,
            duration: 1,
            ease: "power2.out"
        });
    });

    // STAGGERED LISTS[cite: 2]
    gsap.to(".signs li, .class-list li", {
        scrollTrigger: { 
            trigger: ".signs, .class-list", 
            start: "top 80%" 
        },
        x: 0,
        opacity: 1,
        stagger: 0.12,
        duration: 0.8,
        ease: "power2.out"
    });
});