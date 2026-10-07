"""Run only against the disposable SQLite server prepared by support/prepare-asset-ui.php."""
import json
import sys
from pathlib import Path
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC

ROOT = Path(__file__).resolve().parents[2]
BASE = 'http://127.0.0.1:8092'
fixture = json.loads((ROOT / 'tmp/office-assets-browser-fixture.json').read_text())
driver_path = next((ROOT / 'tests/selenium/.venv/selenium-cache').rglob('chromedriver.exe'))
options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.add_argument('--window-size=1440,1100')
options.add_argument('--disable-notifications')
options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})
browser = webdriver.Chrome(service=Service(str(driver_path)), options=options)
wait = WebDriverWait(browser, 20)
results = []
js_errors = []

def check(name, condition):
    assert condition, name
    results.append(name)
    print('PASS:', name, flush=True)

def visit(path):
    browser.get(BASE + path)
    wait.until(lambda b: b.execute_script('return document.readyState') == 'complete')
    assert 'Internal Server Error' not in browser.title, browser.title

def click(element):
    browser.execute_script("arguments[0].scrollIntoView({block:'center'});", element)
    element.click()

def set_value(form, name, value):
    field = form.find_element(By.NAME, name)
    if field.tag_name == 'select':
        Select(field).select_by_value(value)
    elif field.get_attribute('type') == 'date':
        browser.execute_script("arguments[0].value=arguments[1]; arguments[0].dispatchEvent(new Event('change',{bubbles:true}));", field, value)
    else:
        field.clear()
        field.send_keys(value)

def submit(form):
    before = browser.find_element(By.TAG_NAME, 'html')
    click(form.find_element(By.CSS_SELECTOR, 'button[type=submit]'))
    wait.until(EC.staleness_of(before))
    wait.until(lambda b: b.execute_script('return document.readyState') == 'complete')
    errors = browser.find_elements(By.CSS_SELECTOR, '.asset-page .receiving-message.error')
    assert not errors, '\n'.join(e.text for e in errors)

def demo(role):
    visit('/login')
    browser.execute_async_script("fetch(arguments[0], {redirect:'manual'}).then(()=>arguments[arguments.length-1](true));", '/demo-login/' + role)

if '--verify-layout' in sys.argv:
    try:
        demo('office-head')
        visit('/office-head/office-assets')
        check('Office Assets is present in sidebar', browser.find_element(By.CSS_SELECTOR, 'nav.sb-nav summary[title="Office Assets"]').is_displayed())
        check('Only the acquisition FY filter is shown for assets', not browser.find_elements(By.ID, 'globalFiscalYear'))
        browser.save_screenshot(str(ROOT / 'tmp/office-assets-desktop.png'))
        browser.execute_cdp_cmd('Emulation.setDeviceMetricsOverride', {'width': 390, 'height': 844, 'deviceScaleFactor': 1, 'mobile': True})
        browser.save_screenshot(str(ROOT / 'tmp/office-assets-mobile.png'))
        check('390px mobile viewport has no horizontal page overflow', browser.execute_script('return document.documentElement.scrollWidth <= window.innerWidth'))
        browser.execute_cdp_cmd('Emulation.clearDeviceMetricsOverride', {})
        visit('/office-head/purchase-requests?year=2026')
        details = browser.find_element(By.ID, 'receiving-item-' + str(fixture['item']))
        browser.execute_script("arguments[0].closest('.pr-items-panel').style.display='block'; arguments[0].open=true;", details)
        click(details.find_element(By.CSS_SELECTOR, '[data-receiving-tab=warranty]'))
        check('Warranty tab shows recorded coverage', 'Under Warranty' in details.find_element(By.CSS_SELECTOR, '[data-receiving-panel=warranty]').text)
        browser.save_screenshot(str(ROOT / 'tmp/office-assets-receiving.png'))
        js_errors = [entry for entry in browser.get_log('browser') if entry['level'] == 'SEVERE' and entry.get('source') == 'javascript']
        check('No JavaScript exceptions after layout updates', not js_errors)
        (ROOT / 'tmp/office-assets-layout-results.json').write_text(json.dumps({'passed': results, 'javascript_errors': js_errors}, indent=2))
    finally:
        browser.quit()
    sys.exit(0)

try:
    if '--resume-transfer' not in sys.argv:
        demo('office-head')
        visit('/office-head/purchase-requests?year=2026')
        details = browser.find_element(By.ID, 'receiving-item-' + str(fixture['item']))
        browser.execute_script("arguments[0].closest('.pr-items-panel').style.display='block'; arguments[0].open=true;", details)
        click(details.find_element(By.CSS_SELECTOR, '[data-receiving-tab=allocation]'))
        check('Receiving allocation tab opens', details.find_element(By.CSS_SELECTOR, '[data-receiving-panel=allocation]').is_displayed())
        form = details.find_element(By.CSS_SELECTOR, 'form[action*="/register"]')
        click(form.find_element(By.NAME, 'equipment_confirmed'))
        submit(form)
        check('Registers three received units and opens Office Assets', len(browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]')) == 3)
        for checkbox in browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]')[:2]:
            click(checkbox)
        form = browser.find_element(By.CSS_SELECTOR, '[data-asset-bulk] input[value=allocation]').find_element(By.XPATH, '..')
        click(form.find_element(By.XPATH, '../summary'))
        for name, value in {'location': "Dean's Office", 'accountable_person': 'TEST Employee A', 'assigned_on': '2026-09-19', 'usage_status': 'in_use', 'usage_started_on': '2026-09-20', 'reason': 'Browser test physical assignment'}.items():
            set_value(form, name, value)
        submit(form)
        check('Bulk assignment and actual usage saved', len(browser.find_elements(By.CSS_SELECTOR, '.asset-pill[data-status="In Use"]')) == 2)
        for checkbox in browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]'):
            click(checkbox)
        form = browser.find_element(By.CSS_SELECTOR, '[data-asset-bulk] input[value=warranty]').find_element(By.XPATH, '..')
        click(form.find_element(By.XPATH, '../summary'))
        for name, value in {'warranty_coverage': 'covered', 'warranty_start': '2026-09-18', 'warranty_months': '12', 'supplier_contact': 'TEST Supplier', 'reason': 'Browser test warranty certificate'}.items():
            set_value(form, name, value)
        submit(form)
        check('Bulk warranty calculates one year coverage', len(browser.find_elements(By.CSS_SELECTOR, '.asset-pill[data-status="Under Warranty"]')) == 3 and '2027-09-18' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
        browser.save_screenshot(str(ROOT / 'tmp/office-assets-desktop.png'))
        visit('/office-head/office-assets?year=2027&usage=in_use')
        check('Prior-year assets remain visible and usage filter works', len(browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]')) == 2)
        link = browser.find_element(By.CSS_SELECTOR, '.receiving-table a[href*="/office-assets/"]')
        asset_url = link.get_attribute('href')
        click(link)
        wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, 'form[action*="/transfer"]')))
        check('Asset detail shows recorded history', 'Browser test physical assignment' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
        form = browser.find_element(By.CSS_SELECTOR, 'form[action*="/transfer"]')
        set_value(form, 'to_office_id', str(fixture['destination']))
        set_value(form, 'reason', 'Browser test office transfer')
        submit(form)
        check('Transfer request keeps ownership pending', 'Office transfer pending' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
        browser.save_screenshot(str(ROOT / 'tmp/office-asset-detail.png'))
    else:
        results.extend(json.loads((ROOT / 'tmp/office-assets-browser-results.json').read_text())['passed'])
    # Real login for the receiving office; suppress redirect to its unrelated dashboard.
    visit('/login')
    browser.execute_async_script("const done=arguments[arguments.length-1]; const body=new FormData(); body.set('_token',document.querySelector('input[name=_token]').value); body.set('email','asset_recipient@asset.test'); body.set('password','AssetBrowserTestOnly!2026'); fetch('/login',{method:'POST',body,redirect:'manual'}).then(()=>done(true));")
    visit('/office-head/office-assets')
    check('Receiving office sees incoming transfer', 'Incoming office transfers' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
    form = browser.find_element(By.CSS_SELECTOR, 'form[action*="/resolve"]')
    set_value(form, 'reason', 'Browser test physically received')
    submit(form)
    check('Accepted unit becomes available in receiving office', len(browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]')) == 1)
    check('Accepted unit needs a new assignment and retains warranty', 'Unassigned' in browser.find_element(By.CLASS_NAME, 'asset-page').text and 'Under Warranty' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
    browser.set_window_size(390, 844)
    browser.save_screenshot(str(ROOT / 'tmp/office-assets-mobile.png'))
    check('Mobile page has no document-level horizontal overflow', browser.execute_script('return document.documentElement.scrollWidth <= window.innerWidth'))
    browser.set_window_size(1440, 1100)
    for role in ['procurement-office', 'chancellor', 'vice-chancellor']:
        demo(role)
        visit('/' + role + '/office-assets')
        check(role + ' monitoring is read-only', not browser.find_elements(By.CSS_SELECTOR, 'form[data-asset-bulk]') and 'TEST Laptop' in browser.find_element(By.CLASS_NAME, 'asset-page').text)
    js_errors = [entry for entry in browser.get_log('browser') if entry['level'] == 'SEVERE' and entry.get('source') == 'javascript']
    check('No JavaScript exceptions in the tested flow', not js_errors)
finally:
    (ROOT / 'tmp/office-assets-browser-results.json').write_text(json.dumps({'passed': results, 'javascript_errors': js_errors}, indent=2))
    browser.save_screenshot(str(ROOT / 'tmp/office-assets-last.png'))
    browser.quit()
