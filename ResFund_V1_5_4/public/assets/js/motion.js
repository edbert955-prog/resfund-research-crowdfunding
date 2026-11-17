/**
 * ResFund Motion System
 * Lightweight, dependency-free interactions for the public area and all role dashboards.
 * Designed to feel calm and purposeful; respects prefers-reduced-motion.
 */
(() => {
    'use strict';

    const doc = document;
    const root = doc.documentElement;
    const body = doc.body;
    if (!body || root.dataset.rfMotionInitialized === 'true') return;
    root.dataset.rfMotionInitialized = 'true';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    // Mark the page as enhanced only after JavaScript is available. Content remains
    // fully visible when JS is disabled or when reduced motion is requested.
    if (!reduceMotion) {
        root.classList.add('rf-motion-ready');
        body.classList.add('rf-page-enter');
        // Remove the transform after the entrance so fixed-position UI (dialogs,
        // mobile backdrops and sidebars) is never trapped in a transformed body.
        body.addEventListener('animationend', (event) => {
            if (event.animationName === 'rf-page-enter') body.classList.remove('rf-page-enter');
        });
    }

    const raf = window.requestAnimationFrame || ((callback) => window.setTimeout(callback, 16));
    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    /** 1. Scroll reveal with a restrained stagger for repeated cards. */
    const revealGroups = [
        '.project-grid', '.steps-grid', '.donor-stat-grid', '.reviewer-kpi-grid',
        '.admin-kpi-grid', '.researcher-stat-grid', '.admin-quick-grid',
        '.admin-reviewer-grid', '.finance-overview-stats', '.finance-report-grid',
        '.role-choice-grid', '.payment-methods'
    ];
    const revealSingles = [
        '.hero-copy', '.hero-visual', '.quick-search-panel', '.section-heading-row',
        '.section-heading', '.page-hero .container', '.detail-content-block',
        '.funding-panel', '.auth-aside', '.auth-card', '.auth-card-wrap',
        '.donor-page-intro', '.donor-panel', '.donor-content-grid > *',
        '.reviewer-page-heading', '.reviewer-panel', '.reviewer-assignment-card',
        '.reviewer-decision-card', '.admin-page-heading', '.admin-panel',
        '.admin-reviewer-card', '.admin-assignment-card', '.researcher-panel',
        '.researcher-status-card', '.researcher-expense-summary',
        '.finance-report-card', '.finance-state-panel', '.checkout-form',
        '.checkout-sidebar', '.donation-confirmation-card', '.refund-info-card',
        '.step-card', '.project-card'
    ];

    const revealElements = new Set();
    revealGroups.forEach((selector) => {
        doc.querySelectorAll(selector).forEach((group) => {
            Array.from(group.children).forEach((child, index) => {
                if (child instanceof HTMLElement) {
                    child.dataset.rfReveal = 'group';
                    child.style.setProperty('--rf-reveal-delay', `${Math.min(index, 5) * 65}ms`);
                    revealElements.add(child);
                }
            });
        });
    });
    revealSingles.forEach((selector) => {
        doc.querySelectorAll(selector).forEach((element) => {
            // Avoid adding a second reveal wrapper to a child already in a staggered group.
            if (!element.dataset.rfReveal) element.dataset.rfReveal = 'single';
            revealElements.add(element);
        });
    });

    if (!reduceMotion && 'IntersectionObserver' in window && revealElements.size) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('rf-is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -36px 0px' });

        revealElements.forEach((element) => {
            element.classList.add('rf-reveal');
            element.addEventListener('transitionend', (event) => {
                if (event.target !== element || event.propertyName !== 'transform' || !element.classList.contains('rf-is-visible')) return;
                // Drop the transform after motion to avoid creating a permanent
                // containing block for any fixed-position descendants.
                element.classList.remove('rf-reveal', 'rf-is-visible');
                element.style.removeProperty('--rf-reveal-delay');
            });
            revealObserver.observe(element);
        });
    } else {
        revealElements.forEach((element) => element.classList.add('rf-is-visible'));
    }

    /** 2. Gentle count-up for dashboard KPIs and finance summaries. */
    const counterSelector = [
        '.donor-stat-card > strong', '.reviewer-kpi-card strong',
        '.admin-kpi-card strong', '.finance-stat strong',
        '.finance-overview-stat strong', '.researcher-stat-grid .donor-stat-card > strong'
    ].join(',');

    const parseIndonesianNumber = (text) => {
        const clean = text.trim();
        const match = clean.match(/^(\D*?)([\d.]+)(\s*%?)$/u);
        if (!match) return null;
        const prefix = match[1] || '';
        const suffix = match[3] || '';
        const raw = Number.parseInt(match[2].replace(/\./g, ''), 10);
        if (!Number.isFinite(raw) || raw < 0 || raw > 9_000_000_000_000_000) return null;
        return { prefix, suffix, value: raw, original: clean };
    };

    const counters = [];
    doc.querySelectorAll(counterSelector).forEach((element) => {
        if (!(element instanceof HTMLElement) || element.dataset.rfCounted === 'true') return;
        const parsed = parseIndonesianNumber(element.textContent || '');
        if (!parsed || parsed.value === 0) return;
        element.dataset.rfCounted = 'true';
        counters.push({ element, ...parsed });
    });

    const runCounter = (counter) => {
        if (counter.done) return;
        counter.done = true;
        const duration = 760;
        const start = performance.now();
        const format = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        const tick = (now) => {
            const progress = clamp((now - start) / duration, 0, 1);
            const eased = 1 - Math.pow(1 - progress, 4);
            const current = Math.round(counter.value * eased);
            counter.element.textContent = `${counter.prefix}${format.format(current)}${counter.suffix}`;
            if (progress < 1) raf(tick);
            else counter.element.textContent = counter.original;
        };
        raf(tick);
    };

    /** 3. Animate native progress bars from zero to their server-rendered value. */
    const progressBars = [];
    doc.querySelectorAll('.progress-track > span, .finance-project-progress > span, [data-rf-progress]').forEach((bar) => {
        if (!(bar instanceof HTMLElement) || bar.dataset.rfProgressReady === 'true') return;
        const target = bar.style.width || bar.getAttribute('aria-valuenow') && `${bar.getAttribute('aria-valuenow')}%`;
        if (!target) return;
        bar.dataset.rfProgressReady = 'true';
        progressBars.push({ bar, target });
    });

    const animateProgress = (item) => {
        if (item.done) return;
        item.done = true;
        item.bar.style.transition = 'none';
        item.bar.style.width = '0%';
        // Force layout before restoring the target width for a deterministic transition.
        void item.bar.offsetWidth;
        item.bar.style.transition = 'width 1050ms cubic-bezier(.22,.72,.2,1)';
        raf(() => { item.bar.style.width = item.target; });
    };

    /** 4. Reveal counters/progress only as the KPI or chart enters the viewport. */
    const metricTargets = [
        ...counters.map((counter) => counter.element),
        ...progressBars.map((item) => item.bar)
    ];
    if (!reduceMotion && 'IntersectionObserver' in window && metricTargets.length) {
        const metricObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                counters.filter((item) => item.element === entry.target).forEach(runCounter);
                progressBars.filter((item) => item.bar === entry.target).forEach(animateProgress);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.25 });
        metricTargets.forEach((element) => metricObserver.observe(element));
    } else {
        counters.forEach((counter) => { counter.element.textContent = counter.original; });
        progressBars.forEach((item) => { item.bar.style.width = item.target; });
    }

    /** 5. Sticky header depth cue. */
    const header = doc.querySelector('.site-header');
    let scrollTicking = false;
    const updateHeaderDepth = () => {
        if (header) header.classList.toggle('rf-header-scrolled', window.scrollY > 8);
        scrollTicking = false;
    };
    window.addEventListener('scroll', () => {
        if (scrollTicking) return;
        scrollTicking = true;
        raf(updateHeaderDepth);
    }, { passive: true });
    updateHeaderDepth();

    /** 6. Tactile press feedback on buttons and important action links. */
    doc.querySelectorAll('.button, .nav-auth-button, .donor-nav-link, .admin-table-action, .reviewer-text-link').forEach((control) => {
        control.addEventListener('pointerdown', () => control.classList.add('rf-is-pressed'));
        ['pointerup', 'pointercancel', 'pointerleave', 'blur'].forEach((eventName) => {
            control.addEventListener(eventName, () => control.classList.remove('rf-is-pressed'));
        });
    });

    /** 7. Subtle pointer parallax on the public hero only; disabled on touch/reduced motion. */
    const heroVisual = doc.querySelector('.hero-photo-visual');
    if (heroVisual && finePointer && !reduceMotion) {
        let rect = null;
        let pointerFrame = 0;
        heroVisual.addEventListener('pointerenter', () => {
            rect = heroVisual.getBoundingClientRect();
            heroVisual.classList.add('rf-is-tilting');
        });
        heroVisual.addEventListener('pointermove', (event) => {
            if (!rect || pointerFrame) return;
            pointerFrame = raf(() => {
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                heroVisual.style.setProperty('--rf-tilt-x', `${(-y * 1.6).toFixed(2)}deg`);
                heroVisual.style.setProperty('--rf-tilt-y', `${(x * 1.8).toFixed(2)}deg`);
                pointerFrame = 0;
            });
        });
        heroVisual.addEventListener('pointerleave', () => {
            rect = null;
            heroVisual.style.setProperty('--rf-tilt-x', '0deg');
            heroVisual.style.setProperty('--rf-tilt-y', '0deg');
            heroVisual.classList.remove('rf-is-tilting');
        });
    }

    /** 8. Form focus cue; purely visual and never changes validation or submission. */
    doc.querySelectorAll('.form-group, .donor-form-field, .admin-field, .reviewer-form-field').forEach((field) => {
        const input = field.querySelector('input, select, textarea');
        if (!input) return;
        input.addEventListener('focus', () => field.classList.add('rf-field-focused'));
        input.addEventListener('blur', () => field.classList.remove('rf-field-focused'));
    });

    /** 9. Flash messages enter smoothly; they remain until the user dismisses them. */
    doc.querySelectorAll('.flash-message, .form-alert').forEach((message) => {
        if (!reduceMotion) message.classList.add('rf-message-enter');
    });

    /** 10. Optional details/accordion content gets a smooth, accessible state cue. */
    doc.querySelectorAll('details').forEach((details) => {
        details.addEventListener('toggle', () => details.classList.toggle('rf-details-open', details.open));
    });

    // Expose a tiny, read-only state marker for the QA smoke test; no application data is exposed.
    root.dataset.rfMotionMode = reduceMotion ? 'reduced' : 'full';
})();
