"""FC-05..12: budget item validation/CRUD and multi-role proposal decisions."""
from decimal import Decimal
from urllib.parse import parse_qs, urlsplit
import uuid

import pytest
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

from ui import money

pytestmark = pytest.mark.planning


def proposal(app, case, name="draft"):
    data = case(name, "path", "title")
    page = app("office_head", data["path"])
    assert page.visible("#ppmpTitle").get_attribute("value") == data["title"]
    return page, data


def edit_table(p):
    if not p.driver.find_element(By.ID, "ppmpEditWrap").is_displayed():
        p.click("#togglePpmpViewBtn")


def test_fc05_budget_item_required_description(app, case):
    p, _ = proposal(app, case)
    p.fill("#itemDescription", "")
    p.click("#saveItemButton")
    p.invalid("#itemDescription", "valueMissing")


def test_fc06_budget_item_rejects_zero_quantity(app, case):
    p, _ = proposal(app, case)
    p.fill("#itemDescription", "UI validation only")
    p.fill("#itemQuantity", 0)
    p.click("#saveItemButton")
    p.invalid("#itemQuantity", "rangeUnderflow")


@pytest.mark.mutating
def test_fc07_budget_item_add_edit_remove_and_totals(app, case):
    p, data = proposal(app, case, "draft_crud")
    assert "before_total" in data, "Configure draft_crud.before_total from fixture data"
    before = Decimal(str(data["before_total"]))
    edit_table(p)  # The total belongs to the initially hidden Edit Items panel.
    assert money(p.visible("#proposalSummaryTotal").text) == before
    name = "UI test item " + uuid.uuid4().hex[:10]
    p.fill("#itemDescription", name)
    p.fill("#itemQuantity", 2)
    p.fill("#itemUnitCost", 125)
    p.click("#saveItemButton")
    p.text_is("#itemFormMsg", "Item added.")
    p.refresh()
    edit_table(p)
    row = next(r for r in p.rows("#encodedItemsTable tr[data-item-row]") if name in r.text)
    item_id = row.get_attribute("data-item-row")
    selector = f'#encodedItemsTable tr[data-item-row="{item_id}"]'
    assert money(p.visible("#proposalSummaryTotal").text) == before + Decimal(250)
    p.click(selector + ' [title="Edit item"]')
    p.fill("#itemQuantity", 3)
    p.click("#saveItemButton")
    p.text_is("#itemFormMsg", "Item updated.")
    p.refresh()
    edit_table(p)
    assert money(p.visible("#proposalSummaryTotal").text) == before + Decimal(375)
    p.click(selector + ' [title="Edit item"]')
    assert p.visible("#itemQuantity").get_attribute("value") == "3"
    p.click("#cancelItemEditBtn")
    p.click(selector + ' [title="Remove item"]')
    p.click("#prismConfirmOkBtn")
    p.text_is("#itemFormMsg", "Item removed.")
    p.refresh()
    edit_table(p)
    assert not p.driver.find_elements(By.CSS_SELECTOR, selector)
    assert money(p.visible("#proposalSummaryTotal").text) == before


def test_fc08_proposal_missing_sources_cannot_submit(app, case):
    p, _ = proposal(app, case, "missing_sources")
    assert int(p.visible("#proposalSummaryItems").text) > 0
    assert int(p.visible("#proposalSummaryMissing").text) > 0
    assert not p.visible("#submitProposalButton").is_enabled()


@pytest.mark.mutating
def test_fc09_finance_return_reaches_office_with_remarks(app, case, account_credentials):
    data = case("finance_return", "finance_path", "office_path", "timeline_path", "proposal_id", "proposal_code", "title")
    account_credentials("office_head")  # Preflight receiver before changing the record.
    p = app("finance", data["finance_path"])
    p.contains(".page-shell", data["title"])
    remark = "UI finance return " + uuid.uuid4().hex[:10]
    p.fill("#financeOverallRemarks", remark)
    form = p.visible("#financeReviewForm")
    p.click("#financeReviewForm .btn-return")
    p.wait.until(EC.staleness_of(form))
    p = app("office_head", data["timeline_path"])
    # myProposals() maps the UI key to proposal.code, not the database ID.
    timeline_row = f'[data-proposal-row][data-proposal-id="{data["proposal_code"]}"]'
    p.click(timeline_row)
    p.contains("#timelineTitle", data["title"])
    p.text_is("#timelineStatusBadge", "Returned")
    p.contains("#timelineContent", remark)
    detail_url = p.visible('#timelineContent a[href*="budget-proposal"]').get_attribute("href")
    assert parse_qs(urlsplit(detail_url).query)["proposal"] == [str(data["proposal_id"])]
    p.refresh()
    p.click(timeline_row)
    p.contains("#timelineContent", remark)
    p.open(data["office_path"])
    assert p.visible("#ppmpTitle").get_attribute("value") == data["title"]
    assert p.visible("#saveItemButton").is_enabled()


def test_fc10_chancellor_return_requires_remarks(app, case):
    data = case("chancellor_return", "path", "proposal_id", "title")
    p = app("chancellor", data["path"])
    row = f'tr[data-chancellor-proposal-row][data-proposal-id="{int(data["proposal_id"])}"]'
    p.click(row)
    p.contains("#chancellorProposalTitle", data["title"])
    p.fill("#chancellorRemarks", "")
    p.click("#btnReturn")
    assert p.driver.switch_to.active_element.get_attribute("id") == "chancellorRemarks"
    assert p.visible("#btnReturn").is_enabled()
    p.refresh()
    p.visible(row)  # Still pending; the validation did not move it to the archive.


@pytest.mark.mutating
def test_fc11_chancellor_return_persists_in_archive(app, case):
    data = case("chancellor_return", "path", "proposal_id", "title")
    p = app("chancellor", data["path"])
    key = str(int(data["proposal_id"]))
    p.click(f'tr[data-chancellor-proposal-row][data-proposal-id="{key}"]')
    p.contains("#chancellorProposalTitle", data["title"])
    remark = "UI chancellor return " + uuid.uuid4().hex[:10]
    p.fill("#chancellorRemarks", remark)
    p.click("#btnReturn")
    archived = f'tr[data-chancellor-archive-row][data-proposal-id="{key}"]'
    p.contains(archived, "Returned")
    p.refresh()
    p.contains(archived, "Returned")
    p.click(archived)
    p.click("#approvalTrailToggle")
    p.contains("#approvalTrailLog", remark)


@pytest.mark.mutating
@pytest.mark.e2e
def test_fc12_submit_endorse_approve_across_three_roles(app, case, account_credentials):
    data = case("planning_chain", "path", "title", "finance_path", "approval_path", "proposal_id")
    for role in ("office_head", "finance", "chancellor"):
        account_credentials(role)
    p = app("office_head", data["path"])
    assert p.visible("#ppmpTitle").get_attribute("value") == data["title"]
    assert int(p.visible("#proposalSummaryItems").text) > 0
    assert p.visible("#proposalSummaryMissing").text == "0"
    p.click("#submitProposalButton")
    p.text_is("#prismSuccessTitle", "PPMP Submitted")
    old = p.visible("#prismSuccessOkBtn")
    p.click("#prismSuccessOkBtn")
    p.wait.until(EC.staleness_of(old))

    p = app("finance", data["finance_path"])
    p.contains(".page-shell", data["title"])
    p.fill("#financeOverallRemarks", "UI workflow endorsement")
    old = p.visible("#financeReviewForm")
    p.click("#btnEndorse")
    p.wait.until(EC.staleness_of(old))

    p = app("chancellor", data["approval_path"])
    key = str(int(data["proposal_id"]))
    p.click(f'tr[data-chancellor-proposal-row][data-proposal-id="{key}"]')
    p.contains("#chancellorProposalTitle", data["title"])
    p.fill("#chancellorRemarks", "UI workflow approval")
    p.click("#btnApprove")
    archived = f'tr[data-chancellor-archive-row][data-proposal-id="{key}"]'
    p.contains(archived, "Approved")
    p.refresh()
    p.contains(archived, "Approved")
    p = app("office_head", data["path"])
    assert p.visible("#ppmpTitle").get_attribute("value") == data["title"]
    p.contains(".submitted-banner-title", "Approved")
    assert not p.rows("#saveItemButton"), "Approved proposal should no longer be editable"
