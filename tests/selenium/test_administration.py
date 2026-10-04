"""FC-26..27: account validation and a complete disposable-user lifecycle."""
import uuid
import pytest
from selenium.webdriver.support import expected_conditions as EC

pytestmark = pytest.mark.admin


def test_fc26_new_user_requires_identity_fields(app):
    p = app("admin", "/admin/user-management")
    p.click("#newUserBtn")
    p.click("#umSubmitBtn")
    message = p.visible("#umStatus.error").text.lower()
    for field in ("name", "username", "email"):
        assert field in message
    p.visible("#userModalBackdrop.open")


@pytest.mark.mutating
def test_fc27_create_edit_deactivate_user_and_reject_login(app, case, login_slots):
    data = case("new_user", "role_id", "office_id")
    p = app("admin", "/admin/user-management")
    suffix = uuid.uuid4().hex[:12]
    username = "ui_" + suffix
    email = username + "@prism.test"
    p.click("#newUserBtn")
    p.fill("#umName", "UI disposable " + suffix)
    p.fill("#umUsername", username)
    p.fill("#umEmail", email)
    p.select("#umRole", data["role_id"])
    p.select("#umOffice", data["office_id"])
    password = p.visible("#umPassword").get_attribute("value")
    assert password
    old = p.visible("#umSubmitBtn")
    p.click("#umSubmitBtn")
    p.wait.until(EC.staleness_of(old))
    row = f'#usersTable tbody tr[data-search*="{username}"]'
    p.contains(row, email)
    p.contains(row, "Active")
    p.click(row + " .btn-edit-user")
    edited = "UI edited " + suffix
    p.fill("#umName", edited)
    old = p.visible("#umSubmitBtn")
    p.click("#umSubmitBtn")
    p.wait.until(EC.staleness_of(old))
    p.contains(row, edited)
    old = p.visible(row)
    p.click(row + ' .btn-toggle-user[data-action="deactivate"]')
    p.click("#prismConfirmOkBtn")
    p.wait.until(EC.staleness_of(old))
    p.refresh()
    p.contains(row, "Inactive")
    p.logout()
    login_slots()
    p.login(email, password)
    p.contains(".server-error", "Your account is not active.")
    assert p.path_is("/login")
