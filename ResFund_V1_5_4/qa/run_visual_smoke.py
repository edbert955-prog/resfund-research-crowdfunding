from __future__ import annotations

import base64
import json
from pathlib import Path
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "qa" / "visual-preview"
OUT.mkdir(parents=True, exist_ok=True)
CSS = (ROOT / "public/assets/css/app.css").read_text(encoding="utf-8")
ADMIN_CSS = (ROOT / "public/assets/css/admin-dashboard.css").read_text(encoding="utf-8")
SPRITE = (ROOT / "public/assets/images/resfund-icons.svg").read_text(encoding="utf-8")
SPRITE = SPRITE.replace('style="display:none"', 'id="resfund-sprite" style="position:absolute;width:0;height:0;overflow:hidden"')


def data_uri(path: Path, mime: str) -> str:
    return f"data:{mime};base64," + base64.b64encode(path.read_bytes()).decode("ascii")


ASSETS = {
    "logo": data_uri(ROOT / "public/assets/images/resfund-logo.png", "image/png"),
    "mark": data_uri(ROOT / "public/assets/images/resfund-mark.png", "image/png"),
    "photo": data_uri(ROOT / "public/assets/images/project-battery-photo.jpg", "image/jpeg"),
}


def icon(name: str, size: int = 20) -> str:
    return (
        f'<svg class="rf-icon" width="{size}" height="{size}" viewBox="0 0 24 24" '
        f'fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" '
        f'stroke-linejoin="round"><use href="#{name}"></use></svg>'
    )


def header(active: str = "login") -> str:
    return f'''<header class="site-header"><div class="container header-inner">
      <a class="brand"><img class="brand-logo" src="{ASSETS['logo']}" alt="ResFund — Research for Impact"></a>
      <button class="mobile-menu-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="public-navigation" data-mobile-toggle>{icon('menu',22)}</button>
      <nav class="public-nav" id="public-navigation" data-mobile-menu aria-label="Navigasi utama">
        <a href="#">Beranda</a><a href="#">Jelajahi Proyek</a><a href="#">Cara Kerja</a><a href="#">Transparansi</a>
        <span class="nav-divider"></span>
        <a class="nav-auth-button {'is-active' if active == 'login' else ''}" href="#">Masuk</a>
        <a class="nav-auth-button {'is-active' if active == 'register' else ''}" href="#">Daftar</a>
      </nav></div></header>'''


LEFT = f'''<div class="auth-aside"><span class="eyebrow eyebrow-light">Research for Impact</span>
<h1>Terhubung dengan riset yang Anda dukung.</h1>
<p>ResFund mempertemukan donatur, peneliti, reviewer, dan administrator dalam satu alur pendanaan riset yang transparan.</p>
<div class="auth-aside-points">
 <div>{icon('heart')}<p>Donatur mendukung dan memantau proyek riset.</p></div>
 <div>{icon('flask')}<p>Peneliti mengajukan proposal dan melaporkan milestone.</p></div>
 <div>{icon('clipboard-check')}<p>Reviewer menilai bukti secara independen.</p></div>
 <div>{icon('shield-check')}<p>Administrator mengelola validasi, penugasan, dan audit sistem.</p></div>
</div></div>'''


def auth_page(kind: str) -> str:
    is_login = kind == "login"
    title = "Masuk ke ResFund" if is_login else "Buat akun ResFund"
    sub = "Gunakan email dan kata sandi sesuai akun Anda." if is_login else "Jenis akun dipilih saat pendaftaran dan menentukan akses di dalam sistem."
    if is_login:
        form = '''<form class="auth-form"><div class="form-group"><label>Alamat email <span>*</span></label><input placeholder="nama@email.com"></div>
        <div class="form-group"><div class="label-row"><label>Kata sandi <span>*</span></label><a href="#">Lupa kata sandi?</a></div>
        <div class="password-field"><input placeholder="Masukkan kata sandi"><button class="password-toggle">Lihat</button></div></div>
        <button class="button button-primary button-full button-large">Masuk</button></form>'''
        extra = '<div class="role-access-note"><strong>Bagaimana dengan Reviewer dan Administrator?</strong><p>Keduanya tidak mendaftar secara terbuka. Reviewer menerima akses setelah ditugaskan Administrator, sedangkan akun Administrator dibuat oleh pengelola sistem.</p></div>'
        accounts = [('Donatur', 'donatur@resfund.test'), ('Peneliti', 'peneliti@resfund.test'), ('Reviewer A', 'reviewer@resfund.test'), ('Reviewer B', 'reviewer2@resfund.test'), ('Administrator', 'admin@resfund.test'), ('Kata sandi demo', 'ResFund123!')]
        extra += '<div class="demo-credentials"><div class="demo-credentials-heading">' + icon('info',17) + '<strong>Akun demo untuk mencoba prototype</strong></div>'
        extra += ''.join(f'<div><span>{name}</span><code>{email}</code></div>' for name, email in accounts) + '</div>'
        switch = 'Belum memiliki akun? <a href="#">Buat akun</a>'
    else:
        form = '''<form class="auth-form"><fieldset class="role-fieldset"><legend>Daftar sebagai <span>*</span></legend>
        <p class="role-select-hint">Pilih satu jenis akun. Pilihan ini menentukan hak akses dan informasi tambahan yang diperlukan.</p>
        <div class="role-choice-grid"><label class="role-choice"><input type="radio" name="r"><span class="role-choice-content"><span class="role-choice-title">Donatur</span><span class="role-choice-description">Menjelajahi proyek, berdonasi, dan memantau riset.</span></span></label>
        <label class="role-choice"><input type="radio" name="r"><span class="role-choice-content"><span class="role-choice-title">Peneliti</span><span class="role-choice-description">Mengajukan proposal dengan identitas akademik.</span></span></label></div></fieldset>
        <div class="form-group"><label>Nama lengkap <span>*</span></label><input placeholder="Nama sesuai identitas"></div>
        <div class="form-row-two"><div class="form-group"><label>Alamat email <span>*</span></label><input placeholder="nama@email.com"></div><div class="form-group"><label>Nomor telepon <span>*</span></label><input placeholder="08xxxxxxxxxx"></div></div>
        <div class="form-group"><label>Kata sandi <span>*</span></label><input placeholder="Buat kata sandi"></div>
        <button class="button button-primary button-full button-large">Daftar</button></form>'''
        extra = '<div class="role-access-note"><strong>Bagaimana dengan Reviewer dan Administrator?</strong><p>Keduanya tidak mendaftar secara terbuka. Reviewer menerima akses setelah ditugaskan Administrator, sedangkan akun Administrator dibuat oleh pengelola sistem.</p></div>'
        switch = 'Sudah memiliki akun? <a href="#">Masuk</a>'
    return f'''<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>{CSS}</style></head><body>
    {header(kind)}<section class="auth-section"><div class="container auth-container">{LEFT}<div class="auth-card-wrap"><div class="auth-card {'auth-card-register' if not is_login else ''}">
    <div class="auth-card-heading"><span class="auth-mark"><img src="{ASSETS['mark']}" alt="" width="48" height="48"></span><span class="eyebrow">{'Akses akun' if is_login else 'Pendaftaran akun'}</span><h2>{title}</h2><p>{sub}</p></div>
    {form}<div class="auth-separator"><span>atau</span></div><p class="auth-switch">{switch}</p>{extra}</div></div></div></section>
    <div style="position:absolute;left:-9999px">{SPRITE}</div></body></html>'''


def home_page() -> str:
    return f'''<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>{CSS}</style></head><body>
    <header class="site-header"><div class="container header-inner"><a class="brand"><img class="brand-logo" src="{ASSETS['logo']}" alt="ResFund — Research for Impact"></a><button class="mobile-menu-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="public-navigation" data-mobile-toggle>{icon('menu',22)}</button>
    <nav class="public-nav" id="public-navigation" data-mobile-menu><a aria-current="page">Beranda</a><a>Jelajahi Proyek</a><a>Cara Kerja</a><a>Transparansi</a><span class="nav-divider"></span><a class="nav-role-dashboard">Dashboard Peneliti</a>
    <span class="nav-user"><span class="avatar-small">E</span><span class="nav-user-copy">Halo, Edbert Frederick<small class="nav-role-label">Peneliti · menunggu verifikasi</small></span></span><button class="button button-outline button-small">Keluar</button></nav></div></header>
    <section class="hero-section"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Pendanaan riset yang lebih terbuka</span><h1>Dukung penelitian.<br><span>Wujudkan inovasi.</span></h1>
    <p class="hero-lead">Temukan proyek riset akademik, dukung gagasan yang bermanfaat, dan ikuti perkembangan penggunaan dananya melalui satu platform.</p>
    <div class="hero-actions"><a class="button button-primary button-large">Jelajahi proyek {icon('arrow-right',16)}</a><a class="button button-quiet button-large">Pelajari cara kerja</a></div>
    <div class="hero-trust-row"><span class="trust-mark">{icon('check-circle',18)}</span><span>Proses pendanaan bertahap dan informasi keuangan yang dapat ditelusuri.</span></div></div>
    <div class="hero-visual hero-photo-visual"><img src="{ASSETS['photo']}" alt="Peneliti bekerja di laboratorium"><div class="hero-photo-shade"><span>RESEARCH FOR IMPACT</span><strong>Riset yang terbuka.<br>Inovasi yang berdampak.</strong></div>
    <div class="floating-note note-top"><span class="note-icon">{icon('check-circle',18)}</span><span><strong>Transparansi</strong><small>Informasi dapat ditelusuri</small></span></div>
    <div class="floating-note note-bottom"><span class="note-icon note-icon-blue">{icon('activity',18)}</span><span><strong>Milestone riset</strong><small>Progres bertahap</small></span></div></div></div></section>
    <div style="position:absolute;left:-9999px">{SPRITE}</div></body></html>'''


def run():
    results = []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True, executable_path="/usr/bin/chromium", args=["--no-sandbox", "--disable-dev-shm-usage", "--disable-gpu"])
        page = browser.new_page(viewport={"width": 1600, "height": 1000}, device_scale_factor=1)
        for name, html in [("home", home_page()), ("login", auth_page("login")), ("register", auth_page("register"))]:
            page.set_content(html, wait_until="load", timeout=20000)
            page.screenshot(path=str(OUT / f"{name}-desktop.png"), full_page=True)
            metrics = page.evaluate('''() => ({viewport:innerWidth, bodyWidth:document.body.scrollWidth, documentWidth:document.documentElement.scrollWidth, headerHeight:Math.round(document.querySelector('.site-header').getBoundingClientRect().height), images:[...document.images].map(i=>({alt:i.alt,loaded:i.complete&&i.naturalWidth>0,width:Math.round(i.getBoundingClientRect().width),height:Math.round(i.getBoundingClientRect().height)})), buttons:[...document.querySelectorAll('.nav-auth-button')].map(a=>({label:a.textContent.trim(),background:getComputedStyle(a).backgroundColor,active:a.classList.contains('is-active')}))})''')
            metrics["page"] = name
            metrics["viewportName"] = "desktop"
            results.append(metrics)
            assert metrics["bodyWidth"] == metrics["viewport"] and metrics["documentWidth"] == metrics["viewport"], f"Horizontal overflow on {name} desktop: {metrics}"
            assert all(item["loaded"] for item in metrics["images"]), f"Image missing on {name} desktop: {metrics['images']}"
            if name in ("login", "register"):
                active = "Masuk" if name == "login" else "Daftar"
                inactive = "Daftar" if name == "login" else "Masuk"
                active_style = next(b for b in metrics["buttons"] if b["label"] == active)
                inactive_style = next(b for b in metrics["buttons"] if b["label"] == inactive)
                assert active_style["active"] and active_style["background"] == "rgb(36, 95, 206)", f"Active auth button not blue: {metrics['buttons']}"
                assert not inactive_style["active"] and inactive_style["background"] == "rgb(255, 255, 255)", f"Inactive auth button not neutral: {metrics['buttons']}"
                aside_top = page.locator(".auth-aside").bounding_box()["y"]
                card_top = page.locator(".auth-card").bounding_box()["y"]
                wrap_box = page.locator(".auth-card-wrap").bounding_box()
                card_box = page.locator(".auth-card").bounding_box()
                metrics["authAsideTop"] = aside_top
                metrics["authCardTop"] = card_top
                metrics["authCardHeight"] = card_box["height"]
                metrics["authCardCenterOffset"] = round(abs((wrap_box["x"] + wrap_box["width"] / 2) - (card_box["x"] + card_box["width"] / 2)), 1)
                metrics["authButtonHeights"] = page.locator(".nav-auth-button").evaluate_all("els => els.map(el => Math.round(el.getBoundingClientRect().height))")
                assert all(44 <= height <= 50 for height in metrics["authButtonHeights"]), f"Auth nav buttons too tall: {metrics['authButtonHeights']}"
                assert metrics["authCardCenterOffset"] <= 2, f"Auth card is not centered in its content column: {metrics}"
            print(name, "desktop", metrics)
        # Mobile layout smoke checks.
        page.set_viewport_size({"width": 390, "height": 844})
        for name, html in [("home", home_page()), ("login", auth_page("login")), ("register", auth_page("register"))]:
            page.set_content(html, wait_until="load", timeout=20000)
            page.screenshot(path=str(OUT / f"{name}-mobile.png"), full_page=True)
            metrics = page.evaluate('''() => ({viewport:innerWidth, bodyWidth:document.body.scrollWidth, documentWidth:document.documentElement.scrollWidth, headerHeight:Math.round(document.querySelector('.site-header').getBoundingClientRect().height), authAsideBottom:document.querySelector('.auth-aside') ? Math.round(document.querySelector('.auth-aside').getBoundingClientRect().bottom - document.querySelector('.auth-section').getBoundingClientRect().top) : null, images:[...document.images].map(i=>({alt:i.alt,loaded:i.complete&&i.naturalWidth>0}))})''')
            metrics["page"] = name
            metrics["viewportName"] = "mobile"
            results.append(metrics)
            assert metrics["bodyWidth"] == metrics["viewport"] and metrics["documentWidth"] == metrics["viewport"], f"Horizontal overflow on {name} mobile: {metrics}"
            assert all(item["loaded"] for item in metrics["images"]), f"Image missing on {name} mobile: {metrics['images']}"
            if name in ("login", "register"):
                assert metrics["authAsideBottom"] <= 470, f"Auth left-panel text extends past blue surface: {metrics}"
            if name == "home":
                page.add_script_tag(content=(ROOT / "public/assets/js/app.js").read_text(encoding="utf-8"))
                toggle = page.locator("[data-mobile-toggle]")
                menu = page.locator("[data-mobile-menu]")
                toggle.click()
                assert menu.evaluate("el => el.classList.contains('is-open')"), "Mobile navigation did not open"
                assert toggle.get_attribute("aria-expanded") == "true", "Mobile nav aria-expanded not updated"
                page.keyboard.press("Escape")
                assert not menu.evaluate("el => el.classList.contains('is-open')"), "Mobile navigation did not close on Escape"
                assert toggle.get_attribute("aria-expanded") == "false", "Mobile nav aria-expanded not reset"
                metrics["mobileMenuOpenClose"] = "PASS"
            print(name, "mobile", metrics)
        # Intermediate viewport checks for the public/authentication layout.
        viewport_widths = [1600, 1440, 1366, 1280, 1180, 1060, 1024, 960, 800, 760, 540, 430, 390, 360, 320]
        viewport_results = []
        for width in viewport_widths:
            page.set_viewport_size({"width": width, "height": 900})
            for name, html in [("home", home_page()), ("login", auth_page("login")), ("register", auth_page("register"))]:
                page.set_content(html, wait_until="load", timeout=20000)
                measured = page.evaluate("""() => ({viewport:innerWidth, body:document.body.scrollWidth, document:document.documentElement.scrollWidth, navButtonHeights:[...document.querySelectorAll('.nav-auth-button')].map(el=>Math.round(el.getBoundingClientRect().height))})""")
                measured.update({"width": width, "page": name})
                viewport_results.append(measured)
                assert measured["body"] <= width and measured["document"] <= width, f"Horizontal overflow at {width}px on {name}: {measured}"
                if name in ("login", "register") and width > 1060:
                    assert all(44 <= h <= 50 for h in measured["navButtonHeights"]), f"Auth navbar button height issue at {width}px: {measured}"
        results.append({"viewportMatrix": viewport_results})

        # Admin monitoring KPI overflow preview using the production stylesheet.
        admin_html = f'''<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>{ADMIN_CSS}</style></head><body style="margin:0;padding:28px;background:#f3f6fb"><section class="admin-kpi-grid" style="max-width:1500px;margin:auto"><article class="admin-kpi-card"><span class="admin-kpi-icon icon-blue">▦</span><div><span>Proyek terdaftar</span><strong>4</strong><small>Data proyek demonstrasi</small></div></article><article class="admin-kpi-card"><span class="admin-kpi-icon icon-amber">◷</span><div><span>Kampanye aktif</span><strong>2</strong><small>Sedang menggalang dana</small></div></article><article class="admin-kpi-card"><span class="admin-kpi-icon icon-green">✓</span><div><span>Target tercapai</span><strong>1</strong><small>Menunggu proses milestone</small></div></article><article class="admin-kpi-card admin-kpi-card--money"><span class="admin-kpi-icon icon-purple">Rp</span><div class="admin-kpi-content"><span>Dana terhimpun bersih</span><strong class="admin-kpi-currency">Rp105.250.000</strong><small>Refund kampanye gagal dikecualikan: Rp21.000.000</small></div></article></section></body></html>'''
        admin_results = []
        for width in [1600, 1440, 1366, 1280, 1180, 1024, 760, 540, 390, 320]:
            page.set_viewport_size({"width": width, "height": 900})
            page.set_content(admin_html, wait_until="load", timeout=20000)
            measured = page.evaluate("""() => {const card=document.querySelector('.admin-kpi-card--money');const amount=document.querySelector('.admin-kpi-currency');const content=document.querySelector('.admin-kpi-content');return {viewport:innerWidth,body:document.body.scrollWidth,document:document.documentElement.scrollWidth,cardWidth:Math.round(card.getBoundingClientRect().width),contentWidth:Math.round(content.getBoundingClientRect().width),amountWidth:Math.round(amount.getBoundingClientRect().width),amountScroll:amount.scrollWidth,amountClient:amount.clientWidth,text:getComputedStyle(amount).fontSize}}""")
            measured["width"] = width
            admin_results.append(measured)
            assert measured["body"] <= width and measured["document"] <= width, f"Admin page overflow at {width}px: {measured}"
            assert measured["amountScroll"] <= measured["amountClient"] + 1, f"Admin currency amount overflows KPI at {width}px: {measured}"
            if width == 1600:
                page.screenshot(path=str(OUT / "admin-monitoring-kpi-desktop.png"), full_page=True)
        results.append({"adminKpiViewportMatrix": admin_results})
        browser.close()
    (OUT / "visual-smoke-results.json").write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Wrote {OUT / 'visual-smoke-results.json'}")


if __name__ == "__main__":
    run()
