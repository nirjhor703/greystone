@php
    static $infoHelpCounts = [];

    $helpKey = $key ?? md5($text ?? '');
    $infoHelpCounts[$helpKey] = ($infoHelpCounts[$helpKey] ?? 0) + 1;
    $helpId = 'adminInfoHelp'.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $helpKey))).$infoHelpCounts[$helpKey];
    $canEditInfo = auth()->user()?->hasAdminPermission('info.editing');
@endphp

<button type="button" class="investment-info-button" onclick="const box=document.getElementById('{{ $helpId }}'); if(box){ box.hidden = !box.hidden; }" aria-label="Help">i</button><small id="{{ $helpId }}" class="investment-help-text" data-investment-editable-help="adminInfoHelpText_{{ $helpKey }}" hidden><span data-investment-help-copy>{{ $text ?? '' }}</span>@if($canEditInfo)<button type="button" class="investment-help-edit-button">Edit</button>@endif</small>
