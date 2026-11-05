<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Lebo · {{ $lot->code }}</title>
  <style>
    body { margin:0; background:#fff; color:#14251A; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; }
    .label { width:62mm; margin:16px auto; padding:4mm; border:1px dashed #9AA79E; text-align:center; }
    .label svg { width:100%; height:auto; display:block; }
    .brand { font-size:13px; font-weight:800; letter-spacing:.06em; }
    .code { font-size:20px; font-weight:800; letter-spacing:.12em; margin:2mm 0 1mm; font-family:ui-monospace,Menlo,monospace; }
    .crop { font-size:14px; }
    .actions { text-align:center; margin:16px; }
    .actions button { min-height:48px; padding:0 24px; font-size:16px; font-weight:700; border-radius:12px; border:none; background:#1F6B3A; color:#fff; cursor:pointer; }
    @media print { .actions { display:none; } .label { border:none; margin:0 auto; } }
  </style>
</head>
<body>
  <div class="label">
    <div class="brand">MKULIMA TRACE · Shamba hadi Soko</div>
    {!! $svg !!}
    <div class="code">{{ $lot->code }}</div>
    <div class="crop">{{ $lot->product ?: $lot->crop }}</div>
  </div>
  <div class="actions"><button type="button" onclick="window.print()">Chapisha lebo · Print label</button></div>
</body>
</html>
