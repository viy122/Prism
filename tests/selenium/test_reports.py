"""FC-28..29: known report totals, office scope and fiscal-year changes."""
from urllib.parse import parse_qs, urlsplit
import pytest
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

pytestmark = pytest.mark.reports


def assert_stats(p, expected):
    assert expected, "Provide known expected report values"
    actual = {}
    for card in p.rows(".stats-grid .stat-card"):
        label = card.find_element(By.CSS_SELECTOR, ".stat-label").text
        actual[label] = card.find_element(By.CSS_SELECTOR, ".stat-value").text
    for label, value in expected.items():
        assert actual.get(label) == str(value), f"Wrong {label}: {actual.get(label)!r}"


def test_fc28_report_office_filter_and_totals(app, case):
    data = case("report_office", "path", "office", "expected_stats", "expected_rows")
    p = app("procurement", data["path"])
    old = p.visible("#officeFilter")
    p.select("#officeFilter", data["office"])
    p.wait.until(EC.staleness_of(old))
    assert parse_qs(urlsplit(p.driver.current_url).query)["office"] == [data["office"]]
    assert_stats(p, data["expected_stats"])
    table = p.visible(".content .table-wrap table")
    rows = table.find_elements(By.CSS_SELECTOR, "tbody tr")
    assert rows, "Prepare quarterly report rows for the chosen office"
    for row in rows:
        assert row.find_elements(By.TAG_NAME, "td")[0].text == data["office"]
    assert [[cell.text for cell in row.find_elements(By.TAG_NAME, "td")] for row in rows] == data["expected_rows"]
    export = p.visible("#exportReportBtn").get_attribute("href")
    assert parse_qs(urlsplit(export).query)["office"] == [data["office"]]
    p.refresh()
    assert_stats(p, data["expected_stats"])


def test_fc29_switch_fiscal_year_changes_report_to_expected_values(app, case):
    data = case("report_year", "path", "target_year", "before_stats", "after_stats")
    assert data["before_stats"] != data["after_stats"], "Use two years with different known totals"
    p = app("procurement", data["path"])
    assert_stats(p, data["before_stats"])
    old = p.visible("#globalFiscalYear")
    assert old.get_attribute("value") != str(data["target_year"])
    p.select("#globalFiscalYear", data["target_year"])
    p.wait.until(lambda _: parse_qs(urlsplit(p.driver.current_url).query).get("year") == [str(data["target_year"])],
                 "Fiscal-year selection did not navigate to the requested report year")
    p.wait.until(lambda _: p.visible("#globalFiscalYear").get_attribute("value") == str(data["target_year"]))
    assert parse_qs(urlsplit(p.driver.current_url).query)["year"] == [str(data["target_year"])]
    assert_stats(p, data["after_stats"])
    p.refresh()
    assert p.visible("#globalFiscalYear").get_attribute("value") == str(data["target_year"])
    assert_stats(p, data["after_stats"])
