/* CSS commun documents officiels CSVH Saint Luc
   Inclure : <style>@include('partials._doc_css')</style>
   OU copier dans chaque template. */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; background: #eee; }
.page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 12mm 18mm 22mm; box-shadow: 0 4px 24px rgba(0,0,0,.15); position: relative; }
/* Letterhead */
.lh { display: flex; align-items: center; gap: 14px; padding-bottom: 10px; border-bottom: 2px solid #000; margin-bottom: 16px; }
.lh-img-left  { width: 64px; height: 64px; object-fit: cover; flex-shrink: 0; }
.lh-img-right { width: 70px; height: 70px; object-fit: contain; flex-shrink: 0; }
.lh-center { flex: 1; text-align: center; line-height: 1.5; }
.lh-l1 { font-size: 11px; font-weight: 700; letter-spacing: .04em; }
.lh-l2 { font-size: 10px; }
.lh-l3 { font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 2px 0; }
.lh-l4 { font-size: 11px; font-weight: 700; }
.lh-l5 { font-size: 9px; color: #444; margin-top: 3px; }
/* Référence & titre */
.doc-ref  { font-size: 11px; font-style: italic; font-weight: 700; margin: 12px 0 6px; }
.doc-title-box { text-align: center; margin: 10px 0 18px; }
.doc-title-box h1 { display: inline-block; border: 2px solid #000; padding: 5px 28px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
/* Corps */
.doc-body { font-size: 12px; line-height: 1.9; text-align: justify; }
.doc-body p { margin-bottom: 10px; }
.doc-body strong { font-weight: 700; }
.indent { text-indent: 48px; }
/* Destinataire */
.doc-date-right { text-align: right; font-style: italic; font-size: 11px; margin-bottom: 12px; }
.doc-dest { text-align: right; padding-right: 30px; margin-bottom: 14px; line-height: 1.6; }
.doc-dest .dest-a { font-weight: 700; }
.doc-dest .dest-name { font-weight: 700; }
.doc-dest .dest-fn { font-style: italic; }
/* Objet */
.doc-object { font-weight: 700; font-size: 12px; margin-bottom: 14px; }
/* Tableau de congé */
.conge-table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 11px; }
.conge-table th { background: #f0f0ee; padding: 6px 9px; border: 1px solid #999; font-size: 10px; text-transform: uppercase; font-weight: 700; text-align: left; }
.conge-table td { padding: 7px 9px; border: 1px solid #bbb; }
.conge-table .val { font-weight: 700; }
/* Signature */
.doc-sig-area { margin-top: 36px; text-align: right; padding-right: 20px; }
.doc-fait { font-style: italic; font-size: 11px; margin-bottom: 8px; }
.sig-titre { font-weight: 700; font-size: 12px; margin-bottom: 4px; }
.sig-img { height: 130px; width: auto; max-width: 300px; object-fit: contain; display: block; margin-left: auto; }
.sig-line { height: 80px; width: 220px; border-bottom: 1.5px solid #000; margin: 0 0 4px auto; }
.sig-nom { font-weight: 700; text-decoration: underline; font-size: 12px; }
/* Footer */
.doc-footer { position: absolute; bottom: 10mm; left: 18mm; right: 18mm; border-top: 1.5px solid #000; padding-top: 5px; text-align: center; font-size: 8px; font-weight: 700; }
.doc-footer .ft2 { display: flex; justify-content: center; gap: 28px; margin-top: 2px; }
/* No-print */
.no-print { max-width: 210mm; margin: 14px auto; display: flex; gap: 10px; justify-content: center; }
.btn-print { padding: 9px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-back  { padding: 9px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; }
@media print { body { background: white; } .page { margin: 0; box-shadow: none; } .no-print { display: none !important; } }
