"""Browser interactions shared by the multi-module functional suite."""
from decimal import Decimal
from urllib.parse import urlsplit

from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select, WebDriverWait


def money(text):
    import re
    return Decimal(re.sub(r"[^0-9.-]", "", text))


class UI:
    def __init__(self, driver, settings):
        self.driver, self.settings = driver, settings
        self.wait = WebDriverWait(driver, settings.get("timeout", 30))

    def open(self, path):
        assert path.startswith("/") and not path.startswith("//"), "Use an application-relative path"
        self.driver.get(self.settings["base_url"].rstrip("/") + path)
        return self

    def visible(self, css):
        return self.wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, css)))

    def rows(self, css):
        return [e for e in self.driver.find_elements(By.CSS_SELECTOR, css) if e.is_displayed()]

    def click(self, css):
        self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, css))).click()

    def fill(self, css, value):
        el = self.visible(css)
        el.send_keys(Keys.CONTROL, "a")
        el.send_keys(Keys.BACKSPACE)
        el.send_keys(str(value))

    def date(self, css, iso_date):
        # Native date controls have OS-dependent keyboard formats. Set only the
        # input value; validation and submit still run through the real UI.
        el = self.visible(css)
        self.driver.execute_script(
            "arguments[0].value=arguments[1]; arguments[0].dispatchEvent(new Event('input',{bubbles:true}));"
            "arguments[0].dispatchEvent(new Event('change',{bubbles:true}));", el, iso_date)

    def select(self, css, value):
        Select(self.visible(css)).select_by_value(str(value))

    def text_is(self, css, expected):
        self.wait.until(lambda _: self.visible(css).text == str(expected), f"Expected {css} to display {expected!r}")

    def contains(self, css, expected):
        self.wait.until(lambda _: str(expected) in self.visible(css).text, f"Expected {expected!r} in {css}")

    def gone(self, css):
        self.wait.until(EC.invisibility_of_element_located((By.CSS_SELECTOR, css)))

    def path_is(self, path):
        actual = urlsplit(self.driver.current_url)
        expected = urlsplit(self.settings["base_url"].rstrip("/") + path)
        return (actual.netloc, actual.path.rstrip("/")) == (expected.netloc, expected.path.rstrip("/"))

    def refresh(self):
        self.driver.refresh()

    def logout(self):
        self.click("button.sb-logout")
        self.wait.until(lambda _: self.path_is("/login"))
        self.visible("#loginForm")

    def login(self, email, password):
        self.open("/login")
        self.fill("#emailInput", email)
        self.fill("#passwordInput", password)
        self.click("#loginBtn")

    def invalid(self, css, flag):
        el = self.visible(css)
        assert self.driver.execute_script("return arguments[0].validity[arguments[1]]", el, flag), f"Expected {flag} on {css}"

    def upload(self, css, path):
        # WebDriver send_keys is the standard way to use a hidden file input.
        self.driver.find_element(By.CSS_SELECTOR, css).send_keys(str(path))

    def verify_fields(self, expected):
        assert expected, "Provide independent expected values, not an empty assertion map"
        for css, text in expected.items():
            self.text_is(css, text)
