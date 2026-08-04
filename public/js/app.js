// ══════════════════════════════════════════════════════
// RH Plus — JavaScript principal
// ══════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle mot de passe ───────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            var eyeShow  = this.querySelector('.eye-show');
            var eyeHide  = this.querySelector('.eye-hide');

            if (!input) return;

            if (input.type === 'password') {
                input.type    = 'text';
                eyeShow.style.display = 'none';
                eyeHide.style.display = 'block';
            } else {
                input.type    = 'password';
                eyeShow.style.display = 'block';
                eyeHide.style.display = 'none';
            }
        });
    });

    // ── Sidebar mobile ────────────────────────────────────────
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    var toggle   = document.getElementById('menuToggle');
    var closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
        if (sidebar)  sidebar.classList.add('open');
        if (overlay)  overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar)  sidebar.classList.remove('open');
        if (overlay)  overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (toggle)   toggle.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay)  overlay.addEventListener('click', closeSidebar);

    // ── Auto-dismiss alerts ───────────────────────────────────
    var alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.remove(); }, 400);
        }, 4000);
    });

    // ── Barre d'animation au chargement ──────────────────────
    var bars = document.querySelectorAll('.dept-bar, .parite-h, .parite-f');
    if (bars.length) {
        bars.forEach(function (bar) {
            var finalW = bar.style.width;
            bar.style.width = '0';
            setTimeout(function () {
                bar.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                bar.style.width = finalW;
            }, 150);
        });
    }

});
