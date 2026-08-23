@extends('layouts.app')
@section('title', 'Configuration DDIS')
@section('page-title', 'Configuration DDIS')

@section('content')

<div class="config-layout">
    <div class="config-col-main" style="max-width: 900px; margin: 0 auto;">

        @if(session('success'))
            <div class="alert alert-success" style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- ── Identifiants du Signataire DDIS ─────────────────────────────── --}}
        <div class="dash-card" style="background: white; border-radius: 16px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 24px;">
            <div class="card-header" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 14px; margin-bottom: 18px;">
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #111827; margin: 0;">🏥 Direction Diocésaine de la Santé (DDIS) — Identifiants Officiels</h3>
            </div>
            <p style="font-size: 0.88rem; color: #6b7280; margin-bottom: 20px; line-height: 1.5;">
                La DDIS est l'autorité diocésaine chargée de valider officiellement les <strong>avancements d'échelon</strong> et les <strong>bonifications d'ancienneté</strong>. Configurez ci-dessous le triptyque officiel qui figurera sur les documents d'avancement après validation.
            </p>

            <form method="POST" action="{{ route('config-ddis.save') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #374151; margin-bottom: 6px;">
                            1. Mention / Entité Diocésaine <span style="color: red;">*</span>
                        </label>
                        <input type="text" name="ddis_titre" value="{{ old('ddis_titre', $config['ddis_titre']) }}" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 0.9rem;" placeholder="Ex: Pour la Direction Diocésaine de la Santé (DDIS)" required>
                        <span style="font-size: 0.76rem; color: #6b7280;">Mention affichée au-dessus de la signature (ex: Pour la Direction Diocésaine de la Santé).</span>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #374151; margin-bottom: 6px;">
                            2. Nom complet du Valideur DDIS <span style="color: red;">*</span>
                        </label>
                        <input type="text" name="ddis_nom" value="{{ old('ddis_nom', $config['ddis_nom']) }}" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 0.9rem;" placeholder="Ex: Abbé Wilfried KOUTOUKLOUI" required>
                        <span style="font-size: 0.76rem; color: #6b7280;">Nom affiché sous la signature officielle sur l'acte d'avancement.</span>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #374151; margin-bottom: 6px;">
                            3. Nom du Directeur Diocésain de la Santé
                        </label>
                        <input type="text" name="ddis_directeur_nom" value="{{ old('ddis_directeur_nom', $config['ddis_directeur_nom']) }}" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 0.9rem;" placeholder="Ex: Abbé Paul HESSOU">
                    </div>

                    {{-- Upload image de signature DDIS --}}
                    <div class="form-group" style="grid-column: span 2; border-top: 1px solid #f3f4f6; padding-top: 16px; margin-top: 8px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #374151; margin-bottom: 6px;">
                            4. Signature Officielle DDIS — Importer une image (PNG/JPG)
                        </label>
                        <input type="file" name="signature" accept="image/png,image/jpeg" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 10px;">
                        <span style="font-size: 0.76rem; color: #6b7280;">Privilégiez une image au format PNG avec fond transparent pour un rendu net sur les lettres officiellement validées.</span>
                    </div>
                </div>

                <div style="margin-top: 24px; text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
                        ✓ Enregistrer la Configuration DDIS
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Pad de signature DDIS ─────────────────────────────────────────── --}}
        <div class="dash-card" style="background: white; border-radius: 16px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div class="card-header" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 14px; margin-bottom: 18px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #111827; margin: 0;">✍️ Pad de dessin — Tracer la Signature DDIS en direct</h3>
            </div>

            @if($config['ddis_signature_path'])
                @php
                    $sigFullPath = storage_path('app/public/' . $config['ddis_signature_path']);
                    $sigB64 = file_exists($sigFullPath)
                        ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigFullPath))
                        : null;
                @endphp
                @if($sigB64)
                    <div style="margin-bottom: 18px; padding: 14px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 20px;">
                        <span style="font-weight: 600; font-size: 0.85rem; color: #374151;">Signature DDIS actuelle :</span>
                        <img src="{{ $sigB64 }}" alt="Signature DDIS actuelle" style="max-height: 70px; object-fit: contain; border: 1px solid #ddd; border-radius: 8px; background: white; padding: 4px;">
                    </div>
                @endif
            @endif

            <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 14px;">
                Vous pouvez également dessiner votre signature DDIS ci-dessous avec la souris ou un stylet/doigt sur écran tactile :
            </p>

            <div style="border: 2px dashed #9ca3af; border-radius: 12px; background: #fafafa; position: relative; text-align: center;">
                <canvas id="sigCanvasDdis" width="600" height="180" style="touch-action: none; cursor: crosshair; max-width: 100%;"></canvas>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 14px;">
                <button type="button" id="btnClearPad" style="padding: 8px 18px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; font-weight: 600; cursor: pointer;">Effacer</button>
                <button type="button" id="btnSavePad" style="padding: 8px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Enregistrer la signature dessinée</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('sigCanvasDdis');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let drawing = false;

    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDraw(e) {
        drawing = true;
        const p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        const p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDraw() { drawing = false; }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    document.getElementById('btnClearPad').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    document.getElementById('btnSavePad').addEventListener('click', function () {
        const dataUrl = canvas.toDataURL('image/png');
        fetch('{{ route("config-ddis.signature-pad") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ signature_data: dataUrl })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('Signature DDIS enregistrée avec succès !');
                window.location.reload();
            } else {
                alert(res.error || 'Erreur lors de la sauvegarde.');
            }
        })
        .catch(() => alert('Erreur réseau.'));
    });
});
</script>
@endpush

@endsection
