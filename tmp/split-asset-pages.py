from pathlib import Path
root = Path(__file__).resolve().parent.parent / 'prism'
view = root / 'resources/views/prism/shared/office-assets.blade.php'
text = view.read_text(encoding='utf-8')
start = text.index("    @if($assetPageRole === 'office-head')\n    <section class=\"asset-card\" id=\"received-items\"")
end = text.index('    <section class="asset-card asset-filter-card"', start)
section = text[start:end]
section = section.replace("    @if($assetPageRole === 'office-head')\n", '', 1)
section = section.rsplit('    @endif\n', 1)[0]
section = section.replace('Register received equipment here, then assign its location and accountable person below.', 'Register received equipment here, then assign its location and accountable person in Asset Register.')
section = section.replace('These units will appear below for assignment and warranty entry.', 'After registration, you will go to Asset Register to assign these units and record warranty coverage.')
section = section.replace('Existing registered units are listed below.', 'Registered units are available in Asset Register.')
header = '''@extends('prism.layouts.office-head')
@section('title', 'Received Items')
@include('prism.shared.office-asset-styles')
@section('content')
<main class="asset-page">
    <header class="asset-page-header">
        <div><p class="asset-eyebrow">Office Assets · Receiving &amp; registration</p><h1>Received Items</h1><p class="asset-subtitle">Received equipment awaiting registration, across all acquisition years.</p></div>
        <a class="asset-button" href="{{ route('office-head.office-assets') }}"><i class="ti ti-devices" aria-hidden="true"></i> Asset Register</a>
    </header>
    @if(session('receiving_success'))<p class="receiving-message" role="status">{{ session('receiving_success') }}</p>@endif
    @if($errors->any())<div class="receiving-message error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
'''
(root / 'resources/views/prism/shared/received-items.blade.php').write_text(header + section + '</main>\n@endsection\n', encoding='utf-8')
text = text[:start] + text[end:]
text = text.replace("route('office-head.purchase-requests')", "route('office-head.office-assets.received')", 1).replace('View purchase requests', 'Received Items', 1)
text = text.replace("@if($assetPageRole === 'office-head' && $readyUnitCount > 0) Start with <a href=\"#received-items\">the received items above</a> to register units for allocation.", "@if($assetPageRole === 'office-head') Open <a href=\"{{ route('office-head.office-assets.received') }}\">Received Items</a> to register equipment for allocation.")
view.write_text(text, encoding='utf-8')
service = root / 'app/Services/OfficeAssetService.php'
text = service.read_text(encoding='utf-8')
start = text.index('        $ready = $this->registrationCandidates')
end = text.index('        $assets = $this->visible', start)
ready = text[start:end]
text = text[:start] + text[end:]
text = text.replace("'incoming', 'readyReceipts', 'readyUnitCount'", "'incoming'")
method = "    public function receivedPageData(Request $request): array\n    {\n" + ready + "        return compact('readyReceipts', 'readyUnitCount');\n    }\n\n"
text = text.replace('    public function registrationCandidates', method + '    public function registrationCandidates', 1)
service.write_text(text, encoding='utf-8')
