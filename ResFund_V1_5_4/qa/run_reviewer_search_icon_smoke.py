from __future__ import annotations
import json
from pathlib import Path
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'qa' / 'visual-preview'
OUT.mkdir(parents=True, exist_ok=True)
CSS = '\n'.join((ROOT / p).read_text(encoding='utf-8') for p in [
    'public/assets/css/app.css',
    'public/assets/css/donor-dashboard.css',
    'public/assets/css/reviewer-dashboard.css',
])
sprite = (ROOT / 'public/assets/images/resfund-icons.svg').read_text(encoding='utf-8')
sprite = sprite.replace(
    '<svg xmlns="http://www.w3.org/2000/svg">',
    '<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden">',
    1,
)


def page_html(page_kind: str) -> str:
    if page_kind == 'assignments':
        label, input_id, select_label, select_id, option = (
            'Cari penugasan', 'reviewer-search', 'Filter status', 'reviewer-status', 'Semua status saya'
        )
    else:
        label, input_id, select_label, select_id, option = (
            'Cari riwayat keputusan', 'decision-search', 'Filter keputusan', 'decision-status', 'Semua keputusan'
        )
    return f'''<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>{CSS}</style></head>
<body class="donor-app-body reviewer-app-body" style="margin:0;padding:40px;background:#f3f6fb">
{sprite}
<main class="reviewer-main-content" style="max-width:1250px;margin:0 auto">
<section class="reviewer-panel reviewer-filter-panel">
<form class="reviewer-filter-form">
<div class="reviewer-search-field">
<label class="sr-only" for="{input_id}">{label}</label>
<svg class="rf-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#search"></use></svg>
<input id="{input_id}" type="search" placeholder="Cari proyek, peneliti, atau milestone...">
</div>
<div><label class="sr-only" for="{select_id}">{select_label}</label><select id="{select_id}"><option>{option}</option></select></div>
<button class="button button-primary" type="button">Terapkan</button>
<a class="button button-quiet" href="#">Reset</a>
</form></section></main></body></html>'''


results = []
with sync_playwright() as p:
    browser = p.chromium.launch(headless=True, executable_path='/usr/bin/chromium', args=['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'])
    page = browser.new_page()
    viewport_widths = [1600, 1440, 1366, 1280, 1024, 800, 760, 540, 390, 360, 320]
    for page_kind in ['assignments', 'history']:
        html = page_html(page_kind)
        input_id = 'reviewer-search' if page_kind == 'assignments' else 'decision-search'
        for width in viewport_widths:
            page.set_viewport_size({'width': width, 'height': 900})
            page.set_content(html, wait_until='load', timeout=20000)
            data = page.evaluate('''(inputId) => {
              const field = document.querySelector('.reviewer-search-field');
              const input = document.getElementById(inputId);
              const icon = field.querySelector('svg.rf-icon');
              const f = field.getBoundingClientRect(), i = input.getBoundingClientRect(), x = icon.getBoundingClientRect();
              return {
                viewport: innerWidth,
                body: document.body.scrollWidth,
                document: document.documentElement.scrollWidth,
                field: {x:f.x,y:f.y,width:f.width,height:f.height},
                input: {x:i.x,y:i.y,width:i.width,height:i.height},
                icon: {x:x.x,y:x.y,width:x.width,height:x.height,display:getComputedStyle(icon).display,position:getComputedStyle(icon).position},
                verticalCenterOffset: Math.round(Math.abs((x.y+x.height/2)-(i.y+i.height/2))*100)/100,
                iconLeftInset: Math.round((x.x-i.x)*100)/100,
                iconRightWithinPadding: Math.round((i.x+48-(x.x+x.width))*100)/100
              };
            }''', input_id)
            data.update({'width': width, 'page': page_kind})
            results.append(data)
            assert data['body'] <= width and data['document'] <= width, f'horizontal overflow: {data}'
            assert data['icon']['position'] == 'absolute', f'icon is not positioned inside field: {data}'
            assert data['verticalCenterOffset'] <= 1.0, f'icon is not vertically centered: {data}'
            assert data['iconLeftInset'] >= 8 and data['iconRightWithinPadding'] >= 0, f'icon is not inside the input leading area: {data}'
            assert data['input']['y'] <= data['icon']['y'] <= data['input']['y'] + data['input']['height'] - data['icon']['height'] + 1, f'icon outside input bounds: {data}'
            if width == 1440:
                page.screenshot(path=str(OUT / f'reviewer-{page_kind}-search-icon-fixed-desktop.png'), full_page=True)
            if width == 390:
                page.screenshot(path=str(OUT / f'reviewer-{page_kind}-search-icon-fixed-mobile.png'), full_page=True)
    browser.close()

(OUT / 'reviewer-search-icon-test-results.json').write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding='utf-8')
print('PASS: Reviewer search icon tested on both screens at', len(results), 'viewport combinations')
