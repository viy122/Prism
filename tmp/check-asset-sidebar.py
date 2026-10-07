from pathlib import Path
import json
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait

root = Path(__file__).resolve().parent.parent
options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.add_argument('--window-size=1440,1100')
options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})
driver_path = next((root / 'tests/selenium/.venv/selenium-cache').rglob('chromedriver.exe'))
browser = webdriver.Chrome(service=Service(str(driver_path)), options=options)
wait = WebDriverWait(browser, 20)
passed = []
def check(label, condition):
    assert condition, label
    passed.append(label)
def click(el):
    browser.execute_script("arguments[0].scrollIntoView({block:'center'});", el)
    el.click()
try:
    browser.get('http://prism.test/demo-login/office-head')
    browser.get('http://prism.test/office-head/office-assets')
    nav = browser.find_element(By.ID, 'officeAssetsNav')
    check('Dropdown opens on Asset Register', nav.get_attribute('open') is not None)
    check('Register contains no pending-receipt rows', not browser.find_elements(By.CSS_SELECTOR, '[data-ready-receipt]'))
    summary = nav.find_element(By.TAG_NAME, 'summary')
    click(summary)
    check('Dropdown collapses', nav.get_attribute('open') is None)
    summary.send_keys(Keys.ENTER)
    check('Dropdown opens with keyboard', nav.get_attribute('open') is not None)
    click(browser.find_element(By.CSS_SELECTOR, '[data-asset-nav=received]'))
    wait.until(lambda b: b.find_element(By.CSS_SELECTOR, '[data-asset-nav=received]').get_attribute('aria-current') == 'page')
    check('Received Items has its own page and URL', '/office-assets/received-items' in browser.current_url and browser.find_element(By.TAG_NAME, 'h1').text == 'Received Items')
    check('Received Items contains no registered asset controls', not browser.find_elements(By.CSS_SELECTOR, '[data-asset-select], [data-asset-bulk]'))
    browser.save_screenshot(str(root / 'tmp/asset-received-separate.png'))
    browser.execute_cdp_cmd('Emulation.setDeviceMetricsOverride', {'width':390,'height':844,'deviceScaleFactor':1,'mobile':True})
    check('Received Items works at 390px', browser.execute_script('return document.documentElement.scrollWidth <= window.innerWidth'))
    browser.execute_cdp_cmd('Emulation.clearDeviceMetricsOverride', {})
    click(browser.find_element(By.CSS_SELECTOR, '[data-asset-nav=register]'))
    wait.until(lambda b: b.find_element(By.TAG_NAME, 'h1').text == 'Asset Register')
    detail = browser.find_element(By.CSS_SELECTOR, '.receiving-table a.asset-button-small')
    click(detail)
    wait.until(lambda b: b.find_elements(By.CSS_SELECTOR, 'nav[aria-label=Breadcrumb]'))
    check('View / Update keeps Asset Register highlighted', browser.find_element(By.CSS_SELECTOR, '[data-asset-nav=register]').get_attribute('aria-current') == 'page')
    check('Detail has breadcrumb and no extra sidebar entry', 'Asset Register' in browser.find_element(By.CSS_SELECTOR, 'nav[aria-label=Breadcrumb]').text and not browser.find_elements(By.CSS_SELECTOR, '[data-asset-nav=detail]'))
    check('Full Office Head sidebar stays visible', browser.find_element(By.CSS_SELECTOR, '.sb-nav a[title="Purchase Requests"]').is_displayed())
    browser.save_screenshot(str(root / 'tmp/asset-sidebar-detail.png'))
    click(browser.find_element(By.ID, 'sbToggleBtn'))
    check('Sidebar collapses to icon rail', 'sb-collapsed' in browser.find_element(By.TAG_NAME, 'body').get_attribute('class'))
    click(browser.find_element(By.CSS_SELECTOR, '#officeAssetsNav > summary'))
    check('Assets icon expands sidebar and dropdown', 'sb-collapsed' not in browser.find_element(By.TAG_NAME, 'body').get_attribute('class') and browser.find_element(By.ID, 'officeAssetsNav').get_attribute('open') is not None)
    click(browser.find_element(By.CSS_SELECTOR, '[data-asset-nav=register]'))
    wait.until(lambda b: '/office-head/office-assets' in b.current_url)
    check('Asset Register link returns to list', not browser.find_elements(By.CSS_SELECTOR, '[data-asset-nav=detail]'))
    errors = [e for e in browser.get_log('browser') if e['level']=='SEVERE' and e.get('source')=='javascript']
    check('No JavaScript exceptions', not errors)
    print(json.dumps({'passed':passed,'javascript_errors':errors}))
finally:
    browser.quit()
