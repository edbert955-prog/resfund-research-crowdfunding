from __future__ import annotations
import json
from pathlib import Path
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[1]
CSS = '\n'.join((ROOT / 'public/assets/css' / name).read_text(encoding='utf-8') for name in ['app.css', 'donor-dashboard.css', 'researcher-dashboard.css', 'reviewer-dashboard.css', 'admin-dashboard.css'])
MOTION_CSS = (ROOT / 'public/assets/css/motion.css').read_text(encoding='utf-8')
APP_JS = (ROOT / 'public/assets/js/app.js').read_text(encoding='utf-8')
MOTION_JS = (ROOT / 'public/assets/js/motion.js').read_text(encoding='utf-8')
OUT = ROOT / 'qa/motion-preview'
OUT.mkdir(parents=True, exist_ok=True)

HTML = f'''<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<style>{CSS}\n{MOTION_CSS}</style></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand">ResFund</a><button class="mobile-menu-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="public-navigation" data-mobile-toggle><span>Menu</span></button><nav class="public-nav" id="public-navigation" data-mobile-menu><a href="#home">Beranda</a><a href="#projects">Jelajahi Proyek</a></nav></div></header>
<main id="home"><section class="hero-section"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Pendanaan riset</span><h1>Dukung penelitian.<br>Wujudkan inovasi.</h1><p class="hero-lead">Riset yang terbuka dan dapat ditelusuri.</p><a class="button button-primary" href="#projects">Jelajahi proyek</a></div>
<div class="hero-visual hero-photo-visual" style="min-height:300px;background:#dce8fa;border-radius:20px"><div class="floating-note note-top">Transparansi</div><div class="floating-note note-bottom">Milestone riset</div></div></div></section>
<section class="section" id="projects"><div class="container"><div class="section-heading"><h2>Proyek pilihan</h2></div><div class="project-grid">
<article class="project-card"><div class="project-card-body"><h3>Proyek satu</h3><div class="progress-track"><span style="width:65%"></span></div></div></article>
<article class="project-card"><div class="project-card-body"><h3>Proyek dua</h3><div class="progress-track"><span style="width:40%"></span></div></div></article>
<article class="project-card"><div class="project-card-body"><h3>Proyek tiga</h3><div class="progress-track"><span style="width:100%"></span></div></div></article>
</div></div></section>
<section class="section"><div class="container donor-stat-grid">
<article class="donor-stat-card"><strong>Rp42.500.000</strong><small>Total donasi</small></article>
<article class="donor-stat-card"><strong>3</strong><small>Proyek didukung</small></article>
</div></section>
<section class="section"><div class="container admin-kpi-grid">
<article class="admin-kpi-card"><div><strong>Rp105.250.000</strong><small>Dana terhimpun bersih</small></div></article>
<article class="admin-kpi-card"><div><strong>4</strong><small>Proyek terdaftar</small></div></article>
</div></section>
<section class="section"><div class="container reviewer-kpi-grid">
<article class="reviewer-kpi-card"><div><strong>2</strong><small>Perlu ditinjau</small></div></article>
</div><div class="container reviewer-panel"><h2>Penilaian reviewer</h2></div></section>
<section class="section"><div class="container"><div class="form-group"><label for="qa-password">Kata sandi</label><div class="password-field"><input id="qa-password" type="password" value="ResFund123!"><button type="button" data-password-toggle="qa-password" class="password-toggle">Lihat</button></div></div>
<fieldset><legend>Jenis akun</legend><label><input type="radio" name="role" value="donatur" checked> Donatur</label><label><input type="radio" name="role" value="peneliti"> Peneliti</label><div data-researcher-fields hidden><input data-researcher-required placeholder="NIDN/NIM"></div></fieldset></div></section>
</main><script>{APP_JS}</script><script>{MOTION_JS}</script></body></html>'''


def run():
    results = []
    console_errors = []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True, executable_path='/usr/bin/chromium', args=['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'])
        page = browser.new_page(viewport={"width": 1366, "height": 900}, device_scale_factor=1)
        page.on('pageerror', lambda exc: console_errors.append(str(exc)))
        page.set_content(HTML, wait_until='load')
        page.wait_for_timeout(550)
        assert page.locator('html').get_attribute('data-rf-motion-mode') == 'full'
        assert page.locator('body').evaluate("el => getComputedStyle(el).opacity === '1'")
        assert page.locator('.project-card').count() == 3
        page.evaluate('window.scrollTo(0, document.body.scrollHeight)')
        page.wait_for_timeout(1250)
        assert page.locator('.project-card').count() == 3
        assert page.locator('.project-card').evaluate_all("els => els.every(el => getComputedStyle(el).opacity === '1' && getComputedStyle(el).transform === 'none')")
        assert page.locator('.donor-stat-card strong').nth(0).inner_text() == 'Rp42.500.000'
        assert page.locator('.admin-kpi-card strong').nth(0).inner_text() == 'Rp105.250.000'
        assert page.locator('.reviewer-kpi-card strong').first.inner_text() == '2'
        assert page.locator('.reviewer-panel').evaluate("el => getComputedStyle(el).opacity") == '1'
        bar_width = page.locator('.progress-track > span').nth(0).evaluate("el => el.style.width")
        assert bar_width == '65%'
        page.evaluate('window.scrollTo(0, 25)')
        page.wait_for_timeout(80)
        assert page.locator('.site-header').evaluate("el => el.classList.contains('rf-header-scrolled')")
        button = page.locator('.button').first
        button.dispatch_event('pointerdown')
        assert button.evaluate("el => el.classList.contains('rf-is-pressed')")
        button.dispatch_event('pointerup')
        assert not button.evaluate("el => el.classList.contains('rf-is-pressed')")
        # Existing shared interactions continue to work with the motion layer.
        password_toggle = page.locator('[data-password-toggle]')
        password_toggle.click()
        assert page.locator('#qa-password').get_attribute('type') == 'text'
        page.locator('input[name="role"][value="peneliti"]').check()
        assert page.locator('[data-researcher-fields]').is_visible()
        page.locator('input[name="role"][value="donatur"]').check()
        assert not page.locator('[data-researcher-fields]').is_visible()
        page.screenshot(path=str(OUT / 'motion-desktop.png'), full_page=True)
        width = page.evaluate('document.documentElement.scrollWidth')
        assert width <= 1366
        results.append({"viewport": "1366x900", "scroll_reveal": "PASS", "counter": "PASS", "progress": "PASS", "header_depth": "PASS", "button_feedback": "PASS", "horizontal_overflow": "PASS"})

        # Mobile layout check.
        page.set_viewport_size({"width": 390, "height": 844})
        page.evaluate('window.scrollTo(0,0)')
        page.wait_for_timeout(150)
        width = page.evaluate('document.documentElement.scrollWidth')
        assert width <= 390
        toggle = page.locator('[data-mobile-toggle]')
        toggle.click()
        assert page.locator('[data-mobile-menu]').evaluate("el => el.classList.contains('is-open')")
        assert toggle.get_attribute('aria-expanded') == 'true'
        page.keyboard.press('Escape')
        assert not page.locator('[data-mobile-menu]').evaluate("el => el.classList.contains('is-open')")
        assert toggle.get_attribute('aria-expanded') == 'false'
        page.screenshot(path=str(OUT / 'motion-mobile.png'), full_page=True)
        results.append({"viewport": "390x844", "horizontal_overflow": "PASS", "mobile_menu_escape": "PASS", "shared_form_interactions": "PASS"})

        # prefers-reduced-motion must keep every element visible and disable the motion mode.
        reduced = browser.new_page(viewport={"width": 1280, "height": 850}, reduced_motion='reduce')
        reduced.on('pageerror', lambda exc: console_errors.append(str(exc)))
        reduced.set_content(HTML, wait_until='load')
        reduced.wait_for_timeout(100)
        assert reduced.locator('html').get_attribute('data-rf-motion-mode') == 'reduced'
        assert reduced.locator('.project-card.rf-reveal').count() == 0
        assert reduced.locator('.project-card').first.evaluate("el => getComputedStyle(el).opacity") == '1'
        results.append({"viewport": "1280x850 reduced-motion", "motion_disabled": "PASS", "content_visible": "PASS"})
        browser.close()

    assert not console_errors, console_errors
    output = {"results": results, "console_errors": console_errors, "status": "PASS"}
    (OUT / 'results.json').write_text(json.dumps(output, indent=2), encoding='utf-8')
    print(json.dumps(output, indent=2))

if __name__ == '__main__':
    run()
