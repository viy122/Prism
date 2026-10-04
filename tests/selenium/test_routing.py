"""FC-22..23: stage ownership and persistence across signatory accounts."""
import re
import uuid
import pytest

pytestmark = pytest.mark.signature


def doc_selector(key):
    assert re.fullmatch(r"(?:pr|aoc|po)-[1-9][0-9]*", key), "Expected document_key such as pr-123"
    return f'tr[data-doc-key="{key}"]'


def test_fc22_other_signatory_cannot_act(app, case):
    data = case("signature_readonly", "account", "path", "document_key", "number")
    p = app(data["account"], data["path"])
    p.click(doc_selector(data["document_key"]))
    p.text_is("#fNumber", data["number"])
    assert not p.visible("#btnMarkSigned").is_enabled()
    p.text_is("#sigWaitingNote", "This document is not currently at your stage.")


@pytest.mark.mutating
@pytest.mark.e2e
def test_fc23_sign_and_verify_next_role_queue(app, case, account_credentials):
    data = case("signature_handoff", "account", "path", "document_key", "number", "next_account", "next_path", "expected_stage", "before_log_count")
    account_credentials(data["next_account"])
    row = doc_selector(data["document_key"])
    p = app(data["account"], data["path"])
    p.click(row)
    p.text_is("#fNumber", data["number"])
    assert p.visible("#btnMarkSigned").is_enabled()
    p.click("#logToggle")
    before = int(data["before_log_count"])
    assert len(p.rows("#activityLog .activity-item")) == before
    if p.rows("#thirdSignerPanel"):
        third = data.get("third_signer")
        assert third in {"accounting", "vice_chancellor"}, "Configure third_signer"
        p.click("#btnMarkSigned")
        p.contains("#prToast", "Choose who signs 3rd")
        p.click(f'input[name="third_signer"][value="{third}"]')
    remark = "UI signature handoff " + uuid.uuid4().hex[:10]
    p.fill("#remarksInput", remark)
    p.click("#btnMarkSigned")
    p.text_is(row + " [data-sig-badge]", data["expected_stage"])
    p.refresh()
    p.click(row)
    p.text_is(row + " [data-sig-badge]", data["expected_stage"])
    assert not p.visible("#btnMarkSigned").is_enabled()
    p.click("#logToggle")
    assert len(p.rows("#activityLog .activity-item")) == before + 1
    p.contains("#activityLog", remark)
    p = app(data["next_account"], data["next_path"])
    p.click(row)
    p.text_is("#fNumber", data["number"])
    assert p.visible("#btnMarkSigned").is_enabled()
