from pathlib import Path
import json
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait

root = Path(__file__).resolve().parent.parent
driver_path = next((root / 'tests/selenium/.venv/selenium-cache').rglob('chromedriver.exe'))
options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.add_argument('--window-size=1440,1100')
options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})
browser = webdriver.Chrome(service=Service(str(driver_path)), options=options)
wait = WebDriverWait(browser, 20)
try:
    browser.get('http://prism.test/demo-login/office-head')
    browser.get('http://prism.test/office-head/purchase-requests')
    browser.save_screenshot(str(root / 'tmp/asset-style-reference.png'))
    browser.get('http://prism.test/office-head/office-assets')
    wait.until(lambda b: b.find_element(By.CLASS_NAME, 'asset-page-header').is_displayed())
    browser.save_screenshot(str(root / 'tmp/asset-style-desktop.png'))
    assert len(browser.find_elements(By.CSS_SELECTOR, '.asset-stat')) == 5
    assert browser.find_elements(By.CSS_SELECTOR, '[data-ready-receipt]')
    assert browser.execute_script('return document.documentElement.scrollWidth <= window.innerWidth')
    registration = browser.find_element(By.CSS_SELECTOR, '.asset-registration summary')
    browser.execute_script("arguments[0].scrollIntoView({block:'center'});", registration)
    registration.click()
    checkbox = browser.find_elements(By.CSS_SELECTOR, '[data-asset-select]')
    if checkbox:
        browser.execute_script("arguments[0].scrollIntoView({block:'center'});", checkbox[0])
        checkbox[0].click()
        assert all(button.is_enabled() for button in browser.find_elements(By.CSS_SELECTOR, '[data-asset-bulk] button[type=submit]'))
        disclosure = browser.find_element(By.CSS_SELECTOR, '.asset-disclosure summary')
        browser.execute_script("arguments[0].scrollIntoView({block:'center'});", disclosure)
        disclosure.click()
        assert browser.find_element(By.CSS_SELECTOR, '.asset-disclosure[open] input[name=location]').is_displayed()
        disclosure.click()
        checkbox[0].click()
    assert browser.find_element(By.CSS_SELECTOR, '.asset-registration[open] form').is_displayed()
    browser.save_screenshot(str(root / 'tmp/asset-style-registration.png'))
    registration.click()
    browser.execute_cdp_cmd('Emulation.setDeviceMetricsOverride', {'width':390,'height':844,'deviceScaleFactor':1,'mobile':True})
    browser.execute_script("document.querySelector('.main').scrollTop=0;window.scrollTo(0,0);")
    browser.save_screenshot(str(root / 'tmp/asset-style-mobile.png'))
    assert browser.execute_script('return document.documentElement.scrollWidth <= window.innerWidth')
    errors = [e for e in browser.get_log('browser') if e['level']=='SEVERE' and e.get('source')=='javascript']
    assert not errors, errors
    browser.execute_cdp_cmd('Emulation.clearDeviceMetricsOverride', {})
    detail_links = browser.find_elements(By.CSS_SELECTOR, 'a.asset-button-small[href*="/office-assets/"]')
    if detail_links:
        browser.get(detail_links[0].get_attribute('href'))
        assert browser.find_element(By.CLASS_NAME, 'asset-detail-grid').is_displayed()
        browser.save_screenshot(str(root / 'tmp/asset-style-detail.png'))
    print(json.dumps({'desktop':'passed','mobile_390px':'passed','registration_form':'passed','summary_cards':5,'javascript_errors':errors}))
finally:
    browser.quit()
