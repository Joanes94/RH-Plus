@php
    $user = auth()->user();
@endphp

<div class="notif-bell-wrap">
    <button type="button" class="notif-bell-btn" id="notifBellBtn" onclick="toggleNotifDropdown()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($nbNonLues > 0)
            <span class="notif-bell-badge">{{ $nbNonLues > 9 ? '9+' : $nbNonLues }}</span>
        @endif
    </button>

    <div class="notif-dropdown" id="notifDropdown" style="display:none">
        <div class="notif-dropdown-header">
            <span>Notifications</span>
            @if($nbNonLues > 0)
            <form method="POST" action="{{ route('notifications.lues') }}">
                @csrf
                <button type="submit" class="notif-mark-all">Tout marquer comme lu</button>
            </form>
            @endif
        </div>

        <div class="notif-dropdown-list">
            @forelse($notifications as $n)
                @php $lue = $n->estLuePar($user); @endphp
                <div class="notif-item {{ $lue ? '' : 'notif-item-unread' }}">
                    @if($n->type === 'digest_mensuel')
                        <div class="notif-item-body" onclick="toggleDigest({{ $n->id }})" style="cursor:pointer">
                            <span class="notif-icon">{{ $n->icone }}</span>
                            <div class="notif-text">
                                <div class="notif-titre">{{ $n->titre }}</div>
                                <div class="notif-date">{{ $n->date_notification->isoFormat('D MMMM YYYY') }}</div>
                            </div>
                        </div>
                        <div class="notif-digest-detail" id="digest-{{ $n->id }}" style="display:none">
                            @foreach($n->payload ?? [] as $ligne)
                                <a href="{{ route('personnel.show', $ligne['personnel_id']) }}" class="notif-digest-row">
                                    <span>{{ $ligne['nom'] }}</span>
                                    <span class="notif-digest-type">{{ $ligne['type'] === 'bonification' ? '🎖️ Bonification' : '📈 Échelon' }} · {{ \Carbon\Carbon::parse($ligne['date_prevue'])->format('d/m') }}</span>
                                </a>
                            @endforeach
                        </div>
                        @if(!$lue)
                        <form method="POST" action="{{ route('notifications.lue', $n) }}" class="notif-mark-form">
                            @csrf
                            <button type="submit" class="notif-mark-one" title="Marquer comme lu">✓</button>
                        </form>
                        @endif
                    @else
                        <form method="POST" action="{{ route('notifications.lue', $n) }}" class="notif-item-link-form">
                            @csrf
                            <button type="submit" class="notif-item-body notif-item-btn">
                                <span class="notif-icon">{{ $n->icone }}</span>
                                <div class="notif-text">
                                    <div class="notif-titre">{{ $n->titre }}</div>
                                    <div class="notif-msg">{{ $n->message }}</div>
                                    <div class="notif-date">{{ $n->date_notification->isoFormat('D MMMM YYYY') }}</div>
                                </div>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="notif-empty">Aucune notification pour le moment.</div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .notif-bell-wrap { position: relative; }
    .notif-bell-btn {
        position: relative; background: none; border: none; cursor: pointer;
        color: var(--col-text-2); padding: 8px; border-radius: 8px; display: flex;
    }
    .notif-bell-btn:hover { background: var(--col-bg-2); }
    .notif-bell-badge {
        position: absolute; top: 2px; right: 2px; background: #e0473e; color: #fff;
        font-size: .62rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; padding: 0 3px;
    }
    .notif-dropdown {
        position: absolute; right: 0; top: calc(100% + 8px); width: 340px; max-width: 90vw;
        background: #fff; border: 1px solid var(--col-border-lg); border-radius: 10px;
        box-shadow: 0 8px 28px rgba(0,0,0,.15); z-index: 200; overflow: hidden;
    }
    .notif-dropdown-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 14px; border-bottom: 1px solid var(--col-border); font-weight: 700; font-size: .85rem;
    }
    .notif-mark-all { background: none; border: none; color: var(--col-primary); font-size: .72rem; cursor: pointer; font-weight: 600; }
    .notif-dropdown-list { max-height: 380px; overflow-y: auto; }
    .notif-item { border-bottom: 1px solid var(--col-border); position: relative; }
    .notif-item:last-child { border-bottom: none; }
    .notif-item-unread { background: var(--col-green-lt, #eef8f3); }
    .notif-item-body { display: flex; gap: 10px; padding: 12px 14px; align-items: flex-start; }
    .notif-item-btn { background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
    .notif-icon { font-size: 1.1rem; flex-shrink: 0; }
    .notif-text { min-width: 0; }
    .notif-titre { font-weight: 700; font-size: .8rem; color: var(--col-text-1); }
    .notif-msg { font-size: .74rem; color: var(--col-text-2); margin-top: 2px; line-height: 1.4; }
    .notif-date { font-size: .68rem; color: var(--col-text-3); margin-top: 3px; }
    .notif-mark-form { position: absolute; top: 10px; right: 10px; }
    .notif-mark-one { background: var(--col-bg-2); border: 1px solid var(--col-border-lg); border-radius: 50%; width: 20px; height: 20px; font-size: .65rem; cursor: pointer; }
    .notif-digest-detail { padding: 4px 14px 10px 40px; display: flex; flex-direction: column; gap: 6px; }
    .notif-digest-row { display: flex; justify-content: space-between; gap: 8px; font-size: .72rem; text-decoration: none; color: var(--col-text-2); background: var(--col-bg-2); padding: 5px 8px; border-radius: 6px; }
    .notif-digest-type { color: var(--col-text-3); white-space: nowrap; }
    .notif-empty { padding: 24px 14px; text-align: center; font-size: .8rem; color: var(--col-text-3); }
</style>

<script>
    function toggleNotifDropdown() {
        const dd = document.getElementById('notifDropdown');
        dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
    }
    function toggleDigest(id) {
        const el = document.getElementById('digest-' + id);
        if (el) el.style.display = el.style.display === 'none' ? 'flex' : 'none';
    }
    document.addEventListener('click', function (e) {
        const wrap = document.querySelector('.notif-bell-wrap');
        if (wrap && !wrap.contains(e.target)) {
            const dd = document.getElementById('notifDropdown');
            if (dd) dd.style.display = 'none';
        }
    });
</script>