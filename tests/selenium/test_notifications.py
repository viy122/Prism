"""FC-30: notification destination, unread counter and persisted read state."""
import pytest

pytestmark = [pytest.mark.notifications, pytest.mark.mutating]


def test_fc30_notification_navigates_and_stays_read(app, case):
    data = case("notification", "account", "path", "id", "title", "destination", "unread_before")
    notification_id = str(int(data["id"]))
    before = int(data["unread_before"])
    assert before > 0, "Fixture must be unread"
    p = app(data["account"], data["path"])
    p.text_is("#notifBadge", "99+" if before > 99 else before)
    p.click("#notifBtn")
    row = f'.notif-item[data-id="{notification_id}"]'
    p.contains(row, data["title"])
    assert "unread" in p.visible(row).get_attribute("class").split()
    p.click(row)
    p.wait.until(lambda _: p.path_is(data["destination"]))
    p.visible("button.sb-logout")
    p.open(data["path"])
    p.click("#notifBtn")
    p.contains(row, data["title"])
    assert "unread" not in p.visible(row).get_attribute("class").split()
    remaining = before - 1
    if remaining:
        p.text_is("#notifBadge", "99+" if remaining > 99 else remaining)
    else:
        p.gone("#notifBadge")
