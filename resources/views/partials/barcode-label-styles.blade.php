<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: ui-sans-serif, system-ui, sans-serif; color: #111; background: #f1f5f9; }
    .toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; padding: 12px 16px; background: #363E48; color: #fff; font-size: 14px; }
    .toolbar a, .toolbar button { color: #fff; background: rgba(255,255,255,.15); border: 0; border-radius: 6px; padding: 6px 12px; font-size: 13px; text-decoration: none; cursor: pointer; }
    .toolbar input { width: 64px; padding: 4px 6px; border-radius: 4px; border: 0; }
    .sheet { display: flex; flex-wrap: wrap; gap: 8px; padding: 16px; }
    .label { width: {{ $width }}mm; font-size: {{ $scale }}em; padding: 3mm; background: #fff; border: 1px dashed #94a3b8; text-align: center; page-break-inside: avoid; break-inside: avoid; }
    .label .name { font-size: .7em; font-weight: 600; line-height: 1.2; max-height: 2.4em; overflow: hidden; }
    .label svg { display: block; margin: 2mm auto 0; width: 100%; height: auto; }
    .label .code { font-family: ui-monospace, monospace; font-size: .7em; letter-spacing: .12em; margin-top: 1mm; }
    .label .price { font-size: .8em; font-weight: 700; margin-top: 1mm; }
    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .sheet { padding: 0; gap: 0; }
        .label { border: 1px dashed #bbb; }
        @page { margin: 8mm; }
    }
</style>
