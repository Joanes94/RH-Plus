@extends('layouts.app')
@section('title', 'Configuration RH')
@section('page-title', 'Configuration RH')

@section('content')

<div class="config-layout">

    {{-- ── Colonne gauche : DRH + Signature ─────────────────────────────── --}}
    <div class="config-col-main">

        <div class="dash-card">
            <div class="card-header">
                <h3>Directeur des Ressources Humaines</h3>
            </div>
            <p style="font-size:.83rem;color:var(--col-text-2);margin-bottom:18px">
                Ces informations apparaissent sur tous les documents officiels générés.
            </p>

            <form method="POST" action="{{ route('config-rh.save') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid" style="max-width:640px">
                    <div class="form-group fg-6">
                        <label>Nom complet du DRH <span class="req">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="drh_nom"
                                   value="{{ old('drh_nom', $config['drh_nom']) }}"
                                   placeholder="Ex: Abbé Wilfried KOUTOUKLOUI" required>
                        </div>
                    </div>
                    <div class="form-group fg-6">
                        <label>Titre / Fonction <span class="req">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="drh_titre"
                                   value="{{ old('drh_titre', $config['drh_titre']) }}"
                                   placeholder="Directeur des Ressources Humaines" required>
                        </div>
                    </div>
                    <div class="form-group fg-6">
                        <label>Nom de l'organisation</label>
                        <div class="input-wrapper">
                            <input type="text" name="organisation"
                                   value="{{ old('organisation', $config['organisation']) }}">
                        </div>
                    </div>
                    <div class="form-group fg-3">
                        <label>Ville</label>
                        <div class="input-wrapper">
                            <input type="text" name="ville"
                                   value="{{ old('ville', $config['ville']) }}"
                                   placeholder="Cotonou">
                        </div>
                    </div>

                    {{-- Upload image de signature --}}
                    <div class="form-group fg-6" style="border-top:1px solid var(--col-border);padding-top:16px;margin-top:4px">
                        <label>Signature — Upload image (PNG/JPG, fond transparent recommandé)</label>
                        <div class="input-wrapper" style="margin-top:6px">
                            <input type="file" name="signature" accept="image/png,image/jpeg" class="file-input-std">
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:20px">
                    <button type="submit" class="btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Enregistrer la configuration
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Pad de signature ─────────────────────────────────────────────── --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Signature numérique — Pad de dessin</h3>
            </div>

            @if($config['signature_path'])
            <div class="sig-current-wrap">
                @php
                    $sigFullPath = storage_path('app/public/' . $config['signature_path']);
                    $sigB64 = file_exists($sigFullPath)
                        ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigFullPath))
                        : null;
                @endphp
                @if($sigB64)
                <div class="sig-current">
                    <p class="sig-current-label">Signature actuelle :</p>
                    <img src="{{ $sigB64 }}" alt="Signature actuelle" class="sig-preview-img">
                </div>
                @endif
            </div>
            @endif

            <p style="font-size:.83rem;color:var(--col-text-2);margin-bottom:14px">
                Dessinez votre signature dans le cadre ci-dessous avec la souris ou le doigt (écran tactile).
                Elle sera utilisée sur tous les documents approuvés.
            </p>

            {{-- Canvas pad --}}
            <div class="sig-pad-wrap">
                <canvas id="signaturePad" class="sig-pad-canvas" width="540" height="180"></canvas>
                <div class="sig-pad-actions">
                    <button type="button" id="sigClear" class="btn-ghost btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                        Effacer
                    </button>
                    <button type="button" id="sigSave" class="btn-primary btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Enregistrer la signature
                    </button>
                </div>
                <div id="sigFeedback" class="sig-feedback" style="display:none"></div>
            </div>
        </div>

    </div>

    {{-- ── Colonne droite : Jours fériés ─────────────────────────────────── --}}
    <div class="config-col-aside">

        <div class="dash-card">
            <div class="card-header">
                <h3>Jours fériés</h3>
                <form method="GET" style="display:flex;gap:6px">
                    <div class="input-wrapper select-wrapper" style="min-width:90px">
                        <select name="annee" onchange="this.form.submit()">
                            @foreach(range(date('Y') - 1, date('Y') + 2) as $y)
                                <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="import-fixes-bar">
                <div>
                    <p class="text-sm">Importer les jours fériés officiels du Bénin pour {{ $annee }}</p>
                    <p style="font-size:.73rem;color:var(--col-text-3)">{{ count($joursFixesSuggeres) }} jours fixes</p>
                </div>
                <form method="POST" action="{{ route('config-rh.feries.import') }}">
                    @csrf
                    <input type="hidden" name="annee" value="{{ $annee }}">
                    <button type="submit" class="btn-ghost btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Importer
                    </button>
                </form>
            </div>

            <div class="feries-list">
                @forelse($feries as $f)
                <div class="ferie-item">
                    <div class="ferie-date">
                        <span class="fd-day">{{ $f->date->format('d') }}</span>
                        <span class="fd-month">{{ $f->date->isoFormat('MMM') }}</span>
                    </div>
                    <div class="ferie-info">
                        <span class="ferie-label">{{ $f->libelle }}</span>
                        <span class="ferie-type {{ $f->type === 'mobile' ? 'ft-mobile' : 'ft-fixe' }}">
                            {{ $f->type === 'mobile' ? 'Mobile' : 'Fixe' }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('config-rh.feries.destroy', $f) }}"
                          onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn icon-btn-danger" title="Supprimer">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                        </button>
                    </form>
                </div>
                @empty
                <div class="empty-state" style="padding:24px">
                    <p style="font-size:.83rem;color:var(--col-text-2)">Aucun jour férié pour {{ $annee }}.</p>
                </div>
                @endforelse
            </div>

            {{-- Ajouter un jour férié --}}
            <div style="border-top:1px solid var(--col-border);margin-top:12px;padding-top:16px">
                <p style="font-size:.82rem;font-weight:600;margin-bottom:10px">Ajouter un jour férié</p>
                <form method="POST" action="{{ route('config-rh.feries.store') }}">
                    @csrf
                    <input type="hidden" name="annee" value="{{ $annee }}">
                    <div class="form-grid" style="grid-template-columns:repeat(4,1fr);gap:8px">
                        <div class="form-group fg-2">
                            <div class="input-wrapper">
                                <input type="date" name="date" required
                                       min="{{ $annee }}-01-01" max="{{ $annee }}-12-31">
                            </div>
                        </div>
                        <div class="form-group fg-4">
                            <div class="input-wrapper">
                                <input type="text" name="libelle" required placeholder="Libellé">
                            </div>
                        </div>
                        <div class="form-group fg-2">
                            <div class="input-wrapper select-wrapper">
                                <select name="type">
                                    <option value="fixe">Fixe</option>
                                    <option value="mobile">Mobile</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group fg-2" style="display:flex;align-items:flex-end">
                            <button type="submit" class="btn-primary btn-sm" style="width:100%">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
.sig-pad-wrap {
    display: flex; flex-direction: column; gap: 10px;
}
.sig-pad-canvas {
    border: 2px solid var(--col-border-lg);
    border-radius: var(--radius);
    background: white;
    cursor: crosshair;
    display: block;
    width: 100%; height: 180px;
    touch-action: none;
}
.sig-pad-canvas.active { border-color: var(--col-primary); }
.sig-pad-actions {
    display: flex; gap: 10px;
}
.sig-feedback {
    padding: 10px 14px; border-radius: var(--radius);
    font-size: .83rem; font-weight: 500;
}
.sig-feedback.success { background: var(--col-green-lt); color: #065f46; }
.sig-feedback.error   { background: var(--col-red-lt);   color: #991b1b; }
.sig-current-wrap { margin-bottom: 16px; }
.sig-current-label { font-size: .78rem; color: var(--col-text-2); font-weight: 600; margin-bottom: 6px; }
.sig-preview-img {
    max-height: 80px; max-width: 260px;
    border: 1px dashed var(--col-border-lg);
    border-radius: 6px; padding: 6px;
    background: white; display: block;
}
</style>
@endpush

@push('scripts')
<script>
// ── Pad de signature ─────────────────────────────────────────────────────────
(function() {
    const canvas  = document.getElementById('signaturePad');
    const ctx     = canvas.getContext('2d');
    const btnClear= document.getElementById('sigClear');
    const btnSave = document.getElementById('sigSave');
    const feedback= document.getElementById('sigFeedback');

    let drawing  = false;
    let hasDrawn = false;

    // Adapter la résolution canvas (haute densité écran)
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const dpr  = window.devicePixelRatio || 1;
        canvas.width  = rect.width  * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);
        ctx.lineWidth   = 2.5;
        ctx.strokeStyle = '#1a1916';
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
    }
    resizeCanvas();

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const src  = e.touches ? e.touches[0] : e;
        return {
            x: src.clientX - rect.left,
            y: src.clientY - rect.top,
        };
    }

    function startDraw(e) {
        e.preventDefault();
        drawing = true;
        canvas.classList.add('active');
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!drawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasDrawn = true;
    }

    function stopDraw(e) {
        drawing = false;
        canvas.classList.remove('active');
    }

    // Souris
    canvas.addEventListener('mousedown',  startDraw);
    canvas.addEventListener('mousemove',  draw);
    canvas.addEventListener('mouseup',    stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    // Tactile
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove',  draw,      { passive: false });
    canvas.addEventListener('touchend',   stopDraw);

    // Effacer
    btnClear.addEventListener('click', function() {
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        hasDrawn = false;
        hideFeedback();
    });

    // Enregistrer
    btnSave.addEventListener('click', function() {
        if (!hasDrawn) {
            showFeedback('Veuillez d\'abord dessiner votre signature.', 'error');
            return;
        }

        btnSave.disabled = true;
        btnSave.textContent = 'Enregistrement…';

        const dataUrl = canvas.toDataURL('image/png');

        fetch('{{ route("config-rh.signature-pad") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ signature_data: dataUrl }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFeedback('✓ Signature enregistrée avec succès. Rechargez la page pour voir l\'aperçu.', 'success');
            } else {
                showFeedback('Erreur : ' + (data.error || 'inconnue'), 'error');
            }
        })
        .catch(() => showFeedback('Erreur réseau. Réessayez.', 'error'))
        .finally(() => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Enregistrer la signature';
        });
    });

    function showFeedback(msg, type) {
        feedback.textContent = msg;
        feedback.className   = 'sig-feedback ' + type;
        feedback.style.display = 'block';
    }
    function hideFeedback() {
        feedback.style.display = 'none';
    }
})();
</script>
@endpush
