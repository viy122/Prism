{{--
    Notification Bell — included in both layouts (app.blade.php + office-head.blade.php).
    Outputs its own <style>, HTML, and <script> so it is fully self-contained.
--}}

<style>
    /* ── Bell wrapper ──────────────────────────────────────────────────── */
    .notif-wrap { position: relative; display: inline-flex; align-items: center; }

    .notif-btn {
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.35);
        color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: background .15s, transform .15s; position: relative; flex-shrink: 0;
    }
    .notif-btn:hover { background: rgba(255,255,255,.28); transform: scale(1.07); }
    .notif-btn i { font-size: 18px; pointer-events: none; }

    .notif-badge {
        position: absolute; top: -5px; right: -5px;
        background: #EF4444; color: #fff;
        font-size: 9px; font-weight: 800; font-family: 'Poppins', sans-serif;
        min-width: 17px; height: 17px; border-radius: 99px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 4px; border: 2px solid #8B1A1C;
        pointer-events: none;
    }

    /* ── Dropdown ──────────────────────────────────────────────────────── */
    .notif-dropdown {
        position: absolute; top: calc(100% + 10px); right: 0;
        width: 340px; background: #fff; border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,.18), 0 2px 8px rgba(0,0,0,.08);
        overflow: hidden; z-index: 300;
        animation: notifFadeIn .15s ease;
    }
    @keyframes notifFadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

    .notif-drop-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 13px 16px 12px; border-bottom: 1px solid #F0E8E8;
        background: #FFF8F8;
    }
    .notif-drop-title { font-size: 13px; font-weight: 800; color: #1C1010; letter-spacing: -.2px; }
    .notif-mark-all {
        font-size: 11px; font-weight: 700; color: #8B1A1C;
        border: none; background: none; cursor: pointer; padding: 0;
        font-family: 'Poppins', sans-serif; transition: opacity .15s;
    }
    .notif-mark-all:hover { opacity: .7; }

    /* ── Notification list ─────────────────────────────────────────────── */
    .notif-list { max-height: 380px; overflow-y: auto; }

    .notif-item {
        display: flex; align-items: flex-start; gap: 11px;
        padding: 12px 16px; border-bottom: 1px solid #F8F0F0;
        cursor: pointer; transition: background .12s; text-decoration: none;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #FDF5F5; }
    .notif-item.unread { background: #FFF0F0; }
    .notif-item.unread:hover { background: #FFE8E8; }

    .notif-item-icon {
        width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; margin-top: 1px;
    }
    .notif-item-icon i { font-size: 16px; }

    .notif-item-body { flex: 1; min-width: 0; }
    .notif-item-title { font-size: 12px; font-weight: 700; color: #1C1010; margin-bottom: 2px; line-height: 1.3; }
    .notif-item-msg   { font-size: 11px; font-weight: 500; color: #6B4F50; line-height: 1.45; }
    .notif-item-time  { font-size: 10px; font-weight: 600; color: #A88B8C; margin-top: 4px; }

    .notif-unread-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #EF4444;
        flex-shrink: 0; margin-top: 6px;
    }

    /* ── Empty / loading state ─────────────────────────────────────────── */
    .notif-state {
        display: flex; flex-direction: column; align-items: center;
        gap: 8px; padding: 36px 20px; text-align: center;
    }
    .notif-state i { font-size: 34px; color: #D1B4B4; }
    .notif-state p { font-size: 12px; color: #A88B8C; font-weight: 600; line-height: 1.5; }
    @keyframes notifSpin { to { transform: rotate(360deg); } }
</style>

{{-- ── Bell button + dropdown ── --}}
<div class="notif-wrap" id="notifWrap">

    <button class="notif-btn" id="notifBtn" type="button" title="Notifications">
        <i class="ti ti-bell" id="notifBellIcon"></i>
        <span class="notif-badge" id="notifBadge" style="display:none">0</span>
    </button>

    <div class="notif-dropdown" id="notifDropdown" style="display:none">
        <div class="notif-drop-head">
            <span class="notif-drop-title">Notifications</span>
            <button class="notif-mark-all" id="notifMarkAll" type="button">Mark all as read</button>
        </div>
        <div class="notif-list" id="notifList">
            <div class="notif-state">
                <i class="ti ti-bell"></i>
                <p>Your notifications will appear here.</p>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const URL_COUNT    = '{{ route("notifications.count") }}';
    const URL_LIST     = '{{ route("notifications.index") }}';
    const URL_READ_ALL = '{{ route("notifications.read-all") }}';
    const URL_READ_ONE = '{{ url("notifications") }}'; // + '/{id}/read'

    const $badge   = document.getElementById('notifBadge');
    const $btn     = document.getElementById('notifBtn');
    const $icon    = document.getElementById('notifBellIcon');
    const $drop    = document.getElementById('notifDropdown');
    const $list    = document.getElementById('notifList');
    const $markAll = document.getElementById('notifMarkAll');

    let isOpen = false;

    // ── Icon mapping per notification type ───────────────────────────────
    const TYPE_META = {
        proposal_submitted: { icon: 'ti-file-text',    bg: '#DBEAFE', color: '#1D4ED8' },
        proposal_endorsed:  { icon: 'ti-circle-check', bg: '#DCFCE7', color: '#15803D' },
        proposal_approved:  { icon: 'ti-trophy',       bg: '#DCFCE7', color: '#15803D' },
        proposal_returned:  { icon: 'ti-arrow-back-up',bg: '#FEE2E2', color: '#DC2626' },
        pr_uploaded:        { icon: 'ti-upload',       bg: '#FEF9C3', color: '#A16207' },
        pr_status_updated:  { icon: 'ti-refresh',      bg: '#EDE9FE', color: '#6D28D9' },
    };

    // ── Helpers ───────────────────────────────────────────────────────────
    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function timeAgo(iso) {
        const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
        if (diff < 60)    return 'Just now';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function post(url) {
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    // ── Unread count refresh ─────────────────────────────────────────────
    async function refreshCount() {
        try {
            const data = await fetch(URL_COUNT).then(r => r.json());
            if (data.count > 0) {
                $badge.textContent  = data.count > 99 ? '99+' : data.count;
                $badge.style.display = 'flex';
                $icon.className     = 'ti ti-bell-ringing';
            } else {
                $badge.style.display = 'none';
                $icon.className     = 'ti ti-bell';
            }
        } catch {}
    }

    refreshCount();
    setInterval(refreshCount, 30000);   // poll every 30 s

    // ── Toggle dropdown ──────────────────────────────────────────────────
    $btn.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen = !isOpen;
        $drop.style.display = isOpen ? 'block' : 'none';
        if (isOpen) loadList();
    });

    document.addEventListener('click', function () {
        if (isOpen) { isOpen = false; $drop.style.display = 'none'; }
    });

    $drop.addEventListener('click', e => e.stopPropagation());

    // ── Load notification list ────────────────────────────────────────────
    async function loadList() {
        $list.innerHTML = '<div class="notif-state"><i class="ti ti-loader-2" style="animation:notifSpin .8s linear infinite"></i><p>Loading…</p></div>';
        try {
            const items = await fetch(URL_LIST).then(r => r.json());
            renderList(items);
        } catch {
            $list.innerHTML = '<div class="notif-state"><p>Could not load notifications.</p></div>';
        }
    }

    function renderList(items) {
        if (!items.length) {
            $list.innerHTML = '<div class="notif-state"><i class="ti ti-bell-off"></i><p>No notifications yet.<br>You\'re all caught up!</p></div>';
            return;
        }
        $list.innerHTML = items.map(n => {
            const m    = TYPE_META[n.type] || { icon: 'ti-info-circle', bg: '#F0E8E8', color: '#8B1A1C' };
            const unrd = !n.read_at ? 'unread' : '';
            return `<div class="notif-item ${unrd}" data-id="${n.id}" data-url="${esc(n.action_url || '')}">
                <div class="notif-item-icon" style="background:${m.bg}">
                    <i class="ti ${m.icon}" style="color:${m.color}"></i>
                </div>
                <div class="notif-item-body">
                    <p class="notif-item-title">${esc(n.title)}</p>
                    <p class="notif-item-msg">${esc(n.message)}</p>
                    <p class="notif-item-time">${timeAgo(n.created_at)}</p>
                </div>
                ${!n.read_at ? '<div class="notif-unread-dot"></div>' : ''}
            </div>`;
        }).join('');
    }

    // ── Click notification item ───────────────────────────────────────────
    $list.addEventListener('click', async function (e) {
        const item = e.target.closest('.notif-item[data-id]');
        if (!item) return;
        const id  = item.dataset.id;
        const url = item.dataset.url;
        item.classList.remove('unread');
        item.querySelector('.notif-unread-dot')?.remove();
        await post(`${URL_READ_ONE}/${id}/read`);
        refreshCount();
        if (url) window.location.href = url;
    });

    // ── Mark all as read ─────────────────────────────────────────────────
    $markAll.addEventListener('click', async function () {
        await post(URL_READ_ALL);
        $list.querySelectorAll('.notif-item.unread').forEach(el => {
            el.classList.remove('unread');
            el.querySelector('.notif-unread-dot')?.remove();
        });
        $badge.style.display = 'none';
        $icon.className = 'ti ti-bell';
    });
})();
</script>
