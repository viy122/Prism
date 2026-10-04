"""Real browser fixtures. No demo-login shortcuts or application configuration changes."""

import csv
import hashlib
import json
import os
import re
import time
import warnings
from collections import deque
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urlsplit

import pytest
from selenium import webdriver
from selenium.webdriver.chrome.service import Service

from ui import UI

ROOT = Path(__file__).resolve().parent


def pytest_addoption(parser):
    group = parser.getgroup("prism")
    group.addoption("--config", default=str(ROOT / "workflow.local.json"))
    group.addoption("--base-url", default=None)
    group.addoption("--headed", action="store_true", help="Show the Chrome window")
    group.addoption("--driver-path", default=os.getenv("CHROMEDRIVER"))
    group.addoption("--allow-mutations", action="store_true")


def pytest_configure(config):
    path = Path(config.getoption("--config"))
    if not path.exists() and str(path) != str(ROOT / "workflow.local.json"):
        raise pytest.UsageError(f"Config not found: {path}")
    try:
        settings = json.loads(path.read_text(encoding="utf-8-sig")) if path.exists() else {}
    except (ValueError, OSError) as exc:
        raise pytest.UsageError(f"Cannot read configuration: {exc}") from exc
    settings["base_url"] = (config.getoption("--base-url") or settings.get("base_url", "http://prism.test")).rstrip("/")
    if urlsplit(settings["base_url"]).scheme not in {"http", "https"}:
        raise pytest.UsageError("base_url must start with http:// or https://")
    settings.setdefault("accounts", {})
    settings.setdefault("timeout", 20)
    for name, account in settings["accounts"].items():
        for field in ("email", "password_env", "dashboard_path"):
            if not account.get(field):
                raise pytest.UsageError(f"accounts.{name}.{field} is required")
    config.prism_settings = settings
    settings.setdefault("cases", {})
    stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%S.%fZ")
    config.prism_artifacts = ROOT / "artifacts" / stamp
    if not config.option.collectonly:
        config.prism_artifacts.mkdir(parents=True, exist_ok=True)
    config.prism_results = {}


def pytest_collection_modifyitems(config, items):
    # Check fixed report baselines before any actions can change their totals.
    # Read the configured notification before workflows can generate new ones.
    items.sort(key=lambda item: ("mutating" in item.keywords,
                                0 if "notifications" in item.keywords else 1))
    if not config.getoption("--allow-mutations"):
        for item in items:
            if "mutating" in item.keywords:
                item.add_marker(pytest.mark.skip(reason="Enable --allow-mutations with prepared disposable fixtures"))


@pytest.fixture(scope="session")
def settings(pytestconfig):
    return pytestconfig.prism_settings


@pytest.fixture(scope="session")
def login_slots():
    """Respect the app's 5/minute unauthenticated login throttle, sequentially."""
    attempts = deque()

    def reserve():
        now = time.monotonic()
        while attempts and now - attempts[0] >= 65:
            attempts.popleft()
        if len(attempts) >= 5:
            print("Waiting for PRISM's login rate-limit window...")
            while time.monotonic() - attempts[0] < 65:
                time.sleep(max(0, min(1, 65 - (time.monotonic() - attempts[0]))))
            while attempts and time.monotonic() - attempts[0] >= 65:
                attempts.popleft()
        attempts.append(time.monotonic())

    return reserve


@pytest.fixture
def driver(pytestconfig, request):
    options = webdriver.ChromeOptions()
    if not pytestconfig.getoption("--headed"):
        options.add_argument("--headless=new")
    options.add_argument("--window-size=1440,1000")
    options.add_argument("--disable-notifications")
    # Keep Selenium Manager's downloaded driver cache inside the test workspace.
    os.environ.setdefault("SE_CACHE_PATH", str(ROOT / ".venv" / "selenium-cache"))
    path = pytestconfig.getoption("--driver-path")
    browser = webdriver.Chrome(service=Service(executable_path=path) if path else Service(), options=options)
    request.node.prism_driver = browser
    browser.set_page_load_timeout(60)
    browser.implicitly_wait(0)
    yield browser
    browser.quit()


@pytest.fixture
def account_credentials(settings):
    def resolve(name):
        if name not in settings["accounts"]:
            pytest.skip(f"Configure accounts.{name} in workflow.local.json")
        account = settings["accounts"][name]
        password = os.getenv(account["password_env"])
        if not password:
            pytest.fail(f"Set environment variable {account['password_env']} before running authenticated tests")
        return account, password
    return resolve


@pytest.fixture
def case(settings):
    def require(name, *fields):
        data = settings["cases"].get(name)
        if data is None:
            pytest.skip(f"Configure cases.{name} in workflow.local.json")
        for field in fields:
            assert data.get(field) not in (None, "", {}, []), f"cases.{name}.{field} needs real fixture data"
        return data
    return require


@pytest.fixture
def app(request, settings, login_slots, account_credentials):
    def open_as(name=None, path=None):
        # Validate configuration before creating Chrome, and log out between roles.
        credentials = account_credentials(name) if name else None
        browser = request.getfixturevalue("driver")
        page = UI(browser, settings)
        if credentials:
            if browser.find_elements("css selector", "button.sb-logout"):
                page.logout()
            account, password = credentials
            login_slots()
            page.login(account["email"], password)
            page.wait.until(lambda _: page.path_is(account["dashboard_path"]), "Password login did not reach the expected role dashboard")
            page.visible("button.sb-logout")
        if path:
            page.open(path)
        return page
    return open_as


@pytest.fixture
def fixture_file():
    def resolve(value):
        path = Path(value)
        if not path.is_absolute():
            path = ROOT / path
        assert path.is_file(), f"Missing fixture file: {path}"
        return path.resolve()
    return resolve


@pytest.hookimpl(hookwrapper=True)
def pytest_runtest_makereport(item, call):
    outcome = yield
    report = outcome.get_result()
    results = item.config.prism_results
    entry = results.setdefault(item.nodeid, {
        "test": item.nodeid, "result": "", "seconds": 0.0, "actual": "", "screenshot": ""
    })
    entry["seconds"] += report.duration
    if report.failed:
        entry["result"] = "FAIL"
        entry["actual"] = str(report.longrepr)
        browser = getattr(item, "prism_driver", None)
        if browser and report.when != "teardown":
            filename = re.sub(r"[^a-zA-Z0-9_-]", "_", item.name)[:90]
            filename += "-" + hashlib.sha256(item.nodeid.encode()).hexdigest()[:8] + ".png"
            target = item.config.prism_artifacts / filename
            try:
                if browser.save_screenshot(str(target)):
                    entry["screenshot"] = filename
            except Exception as exc:
                warnings.warn(f"Failure screenshot unavailable: {type(exc).__name__}")
    elif entry["result"] != "FAIL":
        if report.skipped:
            entry.update(result="SKIP", actual=str(report.longrepr))
        elif report.when == "call":
            entry.update(result="PASS", actual="All assertions passed")


def pytest_sessionfinish(session, exitstatus):
    config = session.config
    if config.option.collectonly or not hasattr(config, "prism_results"):
        return
    rows = list(config.prism_results.values())
    for row in rows:
        row["seconds"] = round(row["seconds"], 3)
    (config.prism_artifacts / "results.json").write_text(
        json.dumps({"exit_code": int(exitstatus), "tests": rows}, indent=2), encoding="utf-8"
    )
    with (config.prism_artifacts / "results.csv").open("w", newline="", encoding="utf-8-sig") as output:
        writer = csv.DictWriter(output, fieldnames=["test", "result", "seconds", "actual", "screenshot"])
        writer.writeheader()
        writer.writerows(rows)


def pytest_terminal_summary(terminalreporter, exitstatus, config):
    if config.option.collectonly:
        terminalreporter.write_line("Collection only: no browser execution or PASS/FAIL results generated.")
    else:
        terminalreporter.write_line(f"PRISM UI evidence: {config.prism_artifacts}")
