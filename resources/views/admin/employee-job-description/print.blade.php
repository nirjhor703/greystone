<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $designation->name }} — Job Description</title>
    <style>
        @page { size: A4 portrait; margin: 18mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font: 14px/1.6 Arial, sans-serif; background: #fff; }
        main { max-width: 174mm; margin: 0 auto; }
        header { padding-bottom: 18px; border-bottom: 2px solid #1f2937; }
        h1 { margin: 0 0 4px; font-size: 18px; letter-spacing: .08em; text-transform: uppercase; }
        h2 { margin: 22px 0 4px; font-size: 28px; line-height: 1.15; }
        .eyebrow { margin: 0; color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin: 22px 0 28px; }
        .meta p { margin: 0; }
        h3 { margin: 0 0 12px; font-size: 17px; }
        ol { margin: 0; padding-left: 25px; }
        li { margin-bottom: 10px; padding-left: 5px; page-break-inside: avoid; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 42px; margin-top: 62px; }
        .signature { padding-top: 8px; border-top: 1px solid #475569; font-size: 12px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">
    <main>
        <header><p class="eyebrow">Grey Stone</p><h1>Job Description &amp; Responsibilities</h1></header>
        <h2>{{ $designation->name }}</h2>
        <div class="meta">
            <p><strong>Department:</strong> {{ $department->name }}</p>
            <p><strong>Print date:</strong> {{ $printDate->format('Y-m-d') }}</p>
            <p><strong>Candidate / Employee:</strong> {{ $personName ?: '________________________________' }}</p>
        </div>
        <h3>Responsibilities</h3>
        @if($responsibilities->isNotEmpty())
            <ol>@foreach($responsibilities as $responsibility)<li style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $responsibility->description }}</li>@endforeach</ol>
        @else
            <p>No job responsibilities added yet.</p>
        @endif
        <div class="signatures"><div class="signature">Candidate signature</div><div class="signature">Authorized signature</div></div>
    </main>
</body>
</html>
