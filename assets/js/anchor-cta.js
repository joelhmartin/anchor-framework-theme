/**
 * Anchor Framework — Expanding CTA (anchor-dental)
 *
 * Extracted from the DEKA export's scroll-reveal.js. When the
 * .anchor-cta-expand-card scrolls into a trigger zone, a fixed full-viewport
 * background image (injected into <body>) fades in while the card scales up,
 * drops its radius, and goes transparent — so the card "expands" to full-bleed.
 * Reverses on scroll-out. No-ops when the card is absent.
 */
(function () {
    'use strict';

    var ctaCard = document.querySelector('.anchor-cta-expand-card');
    if (!ctaCard) return;

    var imageUrl  = ctaCard.dataset.expandImage || '';
    var ctaActive = false;
    var ticking   = false;

    // Create and inject the fixed background element into <body>.
    var ctaBg = document.createElement('div');
    ctaBg.className = 'anchor-cta-expand-bg';
    ctaBg.setAttribute('aria-hidden', 'true');

    if (imageUrl) {
        var bgImg = document.createElement('img');
        bgImg.src = imageUrl;
        bgImg.alt = '';
        bgImg.setAttribute('aria-hidden', 'true');
        ctaBg.appendChild(bgImg);
    }

    var bgOverlay = document.createElement('div');
    bgOverlay.className = 'anchor-cta-expand-bg__overlay';
    ctaBg.appendChild(bgOverlay);

    document.body.appendChild(ctaBg);

    // The section is the card's closest .anchor-section ancestor.
    var ctaSection = ctaCard.closest('.anchor-section') || ctaCard.parentElement;

    // True once the footer should be revealed: either the next section (the footer)
    // has begun entering the viewport, OR the page is scrolled to its very bottom.
    // The bottom guarantee matters because a footer shorter than ~15% of the
    // viewport can never push its top above the threshold alone (the page can't
    // scroll far enough) — without it the fixed CTA background stays over the footer.
    function footerEntered() {
        var vh   = window.innerHeight;
        var next = ctaSection ? ctaSection.nextElementSibling : null;
        if (next && next.getBoundingClientRect().top < vh * 0.9) return true;
        var doc = document.documentElement;
        return (window.scrollY + vh) >= (doc.scrollHeight - 2);
    }

    function updateCTA() {
        var rect      = ctaCard.getBoundingClientRect();
        var vh        = window.innerHeight;
        var topPct    = rect.top / vh;
        var bottomPct = rect.bottom / vh;

        if (!ctaActive) {
            if (topPct < 0.66 && bottomPct > 0.33 && !footerEntered()) {
                ctaActive = true;
                ctaBg.style.visibility = 'visible';
                ctaBg.style.opacity = '1';
                ctaCard.style.transform = 'scale(1.08)';
                ctaCard.style.borderRadius = '0';
                ctaCard.style.backgroundColor = 'transparent';
            }
        } else {
            var shouldExit = footerEntered() || topPct > 0.75;
            if (shouldExit) {
                ctaActive = false;
                ctaBg.style.opacity = '0';
                ctaBg.style.visibility = 'hidden';
                ctaCard.style.transform = '';
                ctaCard.style.borderRadius = '';
                ctaCard.style.backgroundColor = '';
            }
        }
        ticking = false;
    }

    function onCTAScroll() {
        if (!ticking) {
            requestAnimationFrame(updateCTA);
            ticking = true;
        }
    }

    window.addEventListener('scroll', onCTAScroll, { passive: true });
    window.addEventListener('resize', onCTAScroll, { passive: true });

    // Run once on init in case the card is already in view.
    updateCTA();

})();
