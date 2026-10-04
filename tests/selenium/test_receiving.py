"""FC-24..25: invalid receipt input and saved delivery quantities."""
from datetime import date, timedelta
from decimal import Decimal
import uuid

import pytest
from selenium.webdriver.common.by import By

pytestmark = pytest.mark.receiving


def open_receipt(p, item_id):
    css = f"#receiving-item-{int(item_id)}"
    el = p.driver.find_element(By.CSS_SELECTOR, css)
    cards = el.find_elements(By.XPATH, './ancestor::div[contains(concat(" ",normalize-space(@class)," ")," pr-card ")]')
    for card in cards:
        if "open" not in card.get_attribute("class").split():
            card.find_element(By.CSS_SELECTOR, ".pr-card-header").click()
    if not el.get_attribute("open"):
        p.click(css + " > summary")
    # Only the create form, not correction forms nested in receipt history.
    return css + " > form[data-receipt-form]"


def test_fc24_receipt_rejects_excess_quantity_and_future_date(app, case):
    data = case("receiving", "path", "item_id", "remaining_quantity")
    p = app("office_head", data["path"])
    form = open_receipt(p, data["item_id"])
    quantity = form + ' [name="quantity"]'
    arrival = form + ' [name="arrival_date"]'
    maximum = Decimal(str(data["remaining_quantity"]))
    assert Decimal(p.visible(quantity).get_attribute("max")) == maximum
    latest = p.visible(arrival).get_attribute("max")
    p.date(arrival, latest)
    p.fill(quantity, maximum + 1)
    p.click(form + ' button[type="submit"]')
    p.invalid(quantity, "rangeOverflow")
    p.fill(quantity, "0.01")
    p.date(arrival, (date.fromisoformat(latest) + timedelta(days=1)).isoformat())
    p.click(form + ' button[type="submit"]')
    p.invalid(arrival, "rangeOverflow")


@pytest.mark.mutating
def test_fc25_record_receipt_persists_quantity_date_and_recipient(app, case):
    data = case("record_receipt", "path", "item_id", "arrival_date", "quantity", "recipient", "before_quantity", "after_quantity", "after_status")
    p = app("office_head", data["path"])
    form = open_receipt(p, data["item_id"])
    cell = f'[data-receiving-item="{int(data["item_id"])}"]'
    p.text_is(cell + '[data-receiving-field="quantity"]', data["before_quantity"])
    remark = "UI receipt " + uuid.uuid4().hex[:10]
    p.date(form + ' [name="arrival_date"]', data["arrival_date"])
    p.fill(form + ' [name="quantity"]', data["quantity"])
    p.fill(form + ' [name="received_by_name"]', data["recipient"])
    p.fill(form + ' [name="remarks"]', remark)
    p.click(form + ' button[type="submit"]')
    p.text_is("#receivingSuccessTitle", "Receipt saved")
    p.refresh()
    open_receipt(p, data["item_id"])
    p.text_is(cell + '[data-receiving-field="quantity"]', data["after_quantity"])
    p.text_is(cell + '[data-receiving-field="status"]', data["after_status"])
    history = p.rows(f'#receiving-item-{int(data["item_id"])} .receiving-history')
    matches = [row for row in history if remark in row.text]
    assert len(matches) == 1, "Expected one persisted receipt, no duplicate"
    assert data["recipient"] in matches[0].text
    assert data["arrival_date"] in matches[0].text
