"""FC-01..04: authentication across protected modules."""
import uuid
import pytest

pytestmark = pytest.mark.auth


@pytest.mark.smoke
def test_fc01_required_login_fields(app):
    p = app(path="/login")
    p.click("#loginBtn")
    p.text_is("#emailError", "Please enter your email address.")
    p.text_is("#passwordError", "Please enter your password.")
    assert p.path_is("/login")


@pytest.mark.smoke
def test_fc02_guest_cannot_access_multiple_modules(app):
    p = app()
    for path in ("/office-head/budget-proposal", "/procurement-office/purchase-orders", "/admin/user-management"):
        p.open(path)
        p.wait.until(lambda _: p.path_is("/login"))
        p.visible("#loginForm")


def test_fc03_password_login_role_access_and_logout(app, account_credentials):
    p = app("office_head")
    dashboard = account_credentials("office_head")[0]["dashboard_path"]
    p.open("/admin/user-management")
    p.wait.until(lambda _: p.path_is(dashboard))
    p.visible("button.sb-logout")
    p.logout()
    p.open(dashboard)
    p.wait.until(lambda _: p.path_is("/login"))
    p.visible("#loginForm")


def test_fc04_wrong_password(app, account_credentials, login_slots):
    account, _ = account_credentials("office_head")
    p = app()
    login_slots()
    p.login(account["email"], "invalid-" + uuid.uuid4().hex)
    p.text_is(".server-error", "The provided password is incorrect.")
    assert p.path_is("/login")
