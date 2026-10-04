"""FC-13..21: PR, AOC and PO correctness, search/filtering and creation."""
from decimal import Decimal

import pytest
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

from ui import money

pytestmark = pytest.mark.procurement
MODULES = [("pr", "FC-13/16"), ("aoc", "FC-14/17"), ("po", "FC-15/18")]


def keys(p, module):
    return {e.get_attribute(f"data-{module}-id") for e in p.rows(f"tr[data-{module}-row]")}


@pytest.mark.parametrize("module", [m[0] for m in MODULES], ids=["FC-13-PR", "FC-14-AOC", "FC-15-PO"])
def test_document_details_match_known_fixture(app, case, module):
    data = case(module, "path", "id", "expected")
    p = app("procurement", data["path"])
    row = f'tr[data-{module}-row][data-{module}-id="{int(data["id"])}"]'
    p.click(row)
    p.verify_fields(data["expected"])
    if module == "aoc":
        assert data.get("expected_suppliers"), "Provide expected_suppliers for the AOC quotations"
        assert [e.text for e in p.rows("#previewBody .preview-quote-row .qs")] == data["expected_suppliers"]
    assert p.rows("#sigTimeline .sig-step"), "Missing document timeline"
    p.refresh()
    p.click(row)
    p.verify_fields(data["expected"])


@pytest.mark.parametrize("module", [m[0] for m in MODULES], ids=["FC-16-PR", "FC-17-AOC", "FC-18-PO"])
def test_search_empty_state_and_office_filter(app, case, module):
    data = case(module, "path", "search", "search_ids", "office", "office_ids")
    p = app("procurement", data["path"])
    p.fill(f"#{module}Search", data["search"])
    p.wait.until(lambda _: keys(p, module) == set(map(str, data["search_ids"])))
    p.fill(f"#{module}Search", "UI-no-such-document-98e744772f")
    p.visible(f"#{module}NoResults")
    assert keys(p, module) == set()
    p.fill(f"#{module}Search", "")
    p.select(f"#{module}OfficeFilter", data["office"])
    p.wait.until(lambda _: keys(p, module) == set(map(str, data["office_ids"])))


@pytest.mark.mutating
def test_fc19_create_pr_from_approved_ppmp_pdf(app, case, fixture_file):
    data = case("create_pr", "path", "proposal_id", "pdf", "number", "expected_items", "expected_total", "expected")
    pdf = fixture_file(data["pdf"])
    p = app("procurement", data["path"])
    assert all(data["number"] not in row.text for row in p.rows("tr[data-pr-row]")), "Use a new disposable PR number"
    p.click("#btnOpenUploadPr")
    p.click(f'.pr-ppmp-row[data-ppmp-id="{int(data["proposal_id"])}"]')
    p.upload("#prFileInput", pdf)
    WebDriverWait(p.driver, data.get("extraction_timeout", 120)).until(
        EC.element_to_be_clickable((By.ID, "prSubmitBtn")), "PDF extraction/validation did not enable Create PR")
    assert p.visible("#prNumberInput").get_attribute("value") == data["number"]
    rows = p.rows("#prItemsBody tr")
    assert len(rows) == len(data["expected_items"])
    for row, expected in zip(rows, data["expected_items"]):
        for css, field in ((".pr-item-name", "name"), (".pr-item-unit", "unit")):
            assert row.find_element(By.CSS_SELECTOR, css).get_attribute("value") == expected[field]
        for css, field in ((".pr-item-qty", "quantity"), (".pr-item-cost", "unit_cost")):
            assert Decimal(row.find_element(By.CSS_SELECTOR, css).get_attribute("value")) == Decimal(str(expected[field]))
    assert money(p.visible("#prItemsTotal").text) == Decimal(str(data["expected_total"]))
    old = p.visible("#prSubmitBtn")
    p.click("#prSubmitBtn")
    p.wait.until(EC.staleness_of(old))
    p.fill("#prSearch", data["number"])
    p.wait.until(lambda _: len(p.rows("tr[data-pr-row]")) == 1)
    p.rows("tr[data-pr-row]")[0].click()
    p.verify_fields(data["expected"])
    p.refresh()
    p.fill("#prSearch", data["number"])
    p.wait.until(lambda _: len(p.rows("tr[data-pr-row]")) == 1)
    p.rows("tr[data-pr-row]")[0].click()
    p.verify_fields(data["expected"])


@pytest.mark.mutating
def test_fc20_create_aoc_from_eligible_pr(app, case):
    data = case("create_aoc", "path", "pr_id", "expected")
    p = app("procurement", data["path"])
    before = keys(p, "aoc")
    button = f'.btn-create-aoc[data-pr-id="{int(data["pr_id"])}"]'
    p.click(button)
    p.wait.until(lambda _: len(keys(p, "aoc") - before) == 1)
    new_id = (keys(p, "aoc") - before).pop()
    p.refresh()
    p.click(f'tr[data-aoc-id="{int(new_id)}"]')
    p.verify_fields(data["expected"])
    assert not p.rows(button), "PR should no longer be offered for duplicate AOC creation"


@pytest.mark.mutating
def test_fc21_issue_po_with_supplier_and_amount(app, case):
    data = case("create_po", "path", "aoc_id", "aoc_code", "supplier", "amount", "address")
    p = app("procurement", data["path"])
    before = keys(p, "po")
    button = f'.btn-issue-po[data-aoc-id="{int(data["aoc_id"])}"]'
    p.click(button)
    p.contains("#poModalAocCode", data["aoc_code"])
    p.fill("#poSupplierName", data["supplier"])
    p.fill("#poSupplierAddr", data["address"])
    p.fill("#poAmount", data["amount"])
    if data.get("delivery_date"):
        p.date("#poDeliveryDate", data["delivery_date"])
    p.click("#btnConfirmPo")
    p.wait.until(lambda _: len(keys(p, "po") - before) == 1)
    new_id = (keys(p, "po") - before).pop()
    p.refresh()
    p.click(f'tr[data-po-id="{int(new_id)}"]')
    p.text_is("#fAocCode", data["aoc_code"])
    p.text_is("#fSupplier", data["supplier"])
    p.text_is("#fSupplierAddress", data["address"])
    assert money(p.visible("#fAmount").text) == Decimal(str(data["amount"]))
    assert not p.rows(button), "AOC should not remain eligible for another PO"
