<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $proposalForm['code'] ?: 'PPMP' }} — {{ $pageTitle }}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #1C1010; background: #f0f0f0; margin: 0; padding: 24px; }

    .print-bar { max-width: 950px; margin: 0 auto 14px; display: flex; justify-content: flex-end; }
    .print-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 38px; padding: 0 16px; border-radius: 9px;
        border: 1px solid #8B1A1C; background: #8B1A1C; color: #fff;
        font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit;
    }
    .print-btn:hover { background: #6B1315; }

    .ppmp-doc { max-width: 950px; margin: 0 auto; border: 1px solid rgba(0,0,0,.06); border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.12); }

    .ppmp-letterhead { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 16px 20px 10px; border-bottom: 2px solid #000; }
    .ppmp-letterhead-logo { width: 62px; height: 62px; object-fit: contain; flex-shrink: 0; }
    .ppmp-letterhead-text { text-align: center; }
    .ppmp-letterhead-text p { margin: 0; font-size: 11px; line-height: 1.4; color: #111; }
    .ppmp-letterhead-uni { font-size: 16px !important; font-weight: 800; color: #7a0019; }
    .ppmp-letterhead-sub { font-weight: 700; color: #7a0019; }
    .ppmp-letterhead-campus { font-weight: 700; }
    .ppmp-letterhead-addr { color: #444 !important; }
    .ppmp-office-label { margin: 0; padding: 7px 20px; font-size: 12px; font-weight: 700; border-bottom: 1px solid rgba(0,0,0,.06); }
    .ppmp-doc-head { text-align: center; padding: 18px 16px 12px; border-bottom: 1px solid rgba(0,0,0,.06); }
    .ppmp-doc-title { font-size: 14px; font-weight: 800; letter-spacing: .04em; color: #1C1010; }
    .ppmp-checkbox-row { display: flex; justify-content: center; gap: 32px; margin-top: 10px; font-size: 12px; font-weight: 700; letter-spacing: .03em; }
    .ppmp-checkbox { display: inline-flex; align-items: center; justify-content: center; width: 12px; height: 12px; border: 1.5px solid #000; margin-right: 6px; vertical-align: middle; position: relative; top: -1px; font-size: 9px; line-height: 1; color: #000; }
    .ppmp-meta-row { padding: 10px 20px; font-size: 12px; border-bottom: 1px solid rgba(0,0,0,.06); }
    .ppmp-meta-row div { margin-bottom: 3px; color: #6B4F50; }
    .ppmp-meta-row div:last-child { margin-bottom: 0; }

    .table-scroll { overflow-x: auto; }
    .ppmp-preview-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .ppmp-preview-table thead tr:first-child th { text-align: center; }
    .ppmp-preview-table thead th { background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,.06); padding: 9px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #A88B8C; text-align: left; white-space: nowrap; }
    .ppmp-preview-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #6B4F50; }
    .ppmp-col-number-row th { background: #fff !important; font-size: 9px !important; font-weight: 600 !important; text-transform: none !important; color: #A88B8C !important; text-align: center !important; white-space: nowrap; border-top: 1px solid rgba(0,0,0,.06); }
    .ppmp-total-label { text-align: right; font-weight: 800; font-size: 12px; padding: 10px 12px; border-top: 2px solid rgba(0,0,0,.06); }
    .ppmp-total-amount { font-weight: 800; font-size: 12px; padding: 10px 12px; border-top: 2px solid rgba(0,0,0,.06); }

    .ppmp-signoff { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; padding: 26px 24px 20px; }
    .ppmp-signoff-label { display: block; font-size: 12px; font-weight: 600; color: #6B4F50; margin-bottom: 26px; }
    .ppmp-signoff-name { font-size: 13px; font-weight: 800; text-align: center; text-decoration: underline; text-underline-offset: 3px; color: #1C1010; }
    .ppmp-signoff-title { font-size: 11px; text-align: center; color: #A88B8C; margin-top: 2px; min-height: 14px; }
    .ppmp-signoff-date { font-size: 11px; text-align: center; color: #A88B8C; margin-top: 12px; }

    @media print {
        @page { size: landscape; margin: 10mm; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { background: #fff; padding: 0; }
        .print-bar { display: none; }
        .ppmp-doc { box-shadow: none; border: none; max-width: 100%; }
        .table-scroll { overflow: visible !important; }
        .ppmp-preview-table { width: 100% !important; table-layout: fixed; font-size: 10px; }
        .ppmp-preview-table th, .ppmp-preview-table td { white-space: normal !important; overflow-wrap: break-word; word-break: normal; padding: 6px 8px; }
    }
</style>
</head>
<body>

<div class="print-bar">
    <button type="button" class="print-btn" onclick="window.print()"><span>🖶</span> Print / Save as PDF</button>
</div>

<div class="ppmp-doc">
    <div class="ppmp-letterhead">
        <img src="{{ asset('images/bsulogo.png') }}" alt="BSU Logo" class="ppmp-letterhead-logo">
        <div class="ppmp-letterhead-text">
            <p>Republic of the Philippines</p>
            <p class="ppmp-letterhead-uni">BATANGAS STATE UNIVERSITY</p>
            <p class="ppmp-letterhead-sub">The National Engineering University</p>
            <p class="ppmp-letterhead-campus">ARASOF-Nasugbu Campus</p>
            <p class="ppmp-letterhead-addr">R. Martinez St, Brgy. Bucana, Nasugbu, Batangas, Philippines 4231</p>
            <p class="ppmp-letterhead-addr">Tel Nos.: (+63 43) 416-0350 local 101; (+63 43) 416-0068</p>
            <p class="ppmp-letterhead-addr">E-mail Address: nasugbu@g.batstate-u.edu.ph | Website Address: http://www.batstate-u.edu.ph</p>
        </div>
    </div>
    <p class="ppmp-office-label">Office of the Chancellor</p>

    <div class="ppmp-doc-head">
        <p class="ppmp-doc-title">PROJECT PROCUREMENT MANAGEMENT PLAN (PPMP) NO. {{ $proposalForm['code'] ?: '___' }}</p>
        <div class="ppmp-checkbox-row">
            <span><span class="ppmp-checkbox">{{ $proposalForm['isFinal'] ? '' : '■' }}</span>INDICATIVE</span>
            <span><span class="ppmp-checkbox">{{ $proposalForm['isFinal'] ? '■' : '' }}</span>FINAL</span>
        </div>
    </div>

    <div class="ppmp-meta-row">
        <div><strong>Fiscal Year :</strong> {{ $proposalForm['fiscalYear'] }}</div>
        <div><strong>End-User/Implementing Unit:</strong> {{ $proposalForm['officeName'] }}</div>
    </div>

    <div class="table-scroll">
        <table class="ppmp-preview-table">
            <thead>
                <tr>
                    <th colspan="5">Procurement Project Details</th>
                    <th colspan="3">Projected Timeline (MM/YYYY)</th>
                    <th colspan="2">Funding Details</th>
                    <th rowspan="2" title="Attached Supporting Document/s">Attached Supporting Document/s</th>
                    <th rowspan="2" title="Remarks">Remarks</th>
                </tr>
                <tr>
                    <th title="General Description and Objective of the Project to be Procured">General Description and Objective</th>
                    <th title="Type of the Project to be Procured (whether Goods, Infrastructure and Consulting Services)">Type</th>
                    <th title="Quantity and Size of the Project to be Procured">Qty &amp; Size</th>
                    <th title="Recommended Mode of Procurement">Recommended Mode of Procurement</th>
                    <th title="Pre-Procurement Conference, if applicable (Yes/No)">Pre-Proc. Conference</th>
                    <th title="Start of Procurement Activity">Start of Procurement Activity</th>
                    <th title="End of Procurement Activity">End of Procurement Activity</th>
                    <th title="Expected Delivery/Implementation Period">Expected Delivery / Implementation</th>
                    <th title="Source of Funds">Source of Funds</th>
                    <th title="Estimated Budget / Authorized Budgetary Allocation">Estimated Budget</th>
                </tr>
                <tr class="ppmp-col-number-row">
                    <th>Column 1</th><th>Column 2</th><th>Column 3</th><th>Column 4</th><th>Column 5</th>
                    <th>Column 6</th><th>Column 7</th><th>Column 8</th><th>Column 9</th><th>Column 10</th>
                    <th>Column 11</th><th>Column 12</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $uploadLinks = collect($item['attachments'])->map(fn ($f) => '<a href="'.e($f['url']).'" target="_blank" rel="noopener">'.e($f['name']).'</a>');
                        $scopingLinks = collect($item['scoping'])->map(fn ($ref) => e($ref['supplierName'] ?? 'Market reference'));
                        $attachCell = $uploadLinks->concat($scopingLinks)->implode('<br>');
                    @endphp
                    <tr>
                        <td><strong>{{ $item['description'] }}</strong></td>
                        <td>{{ $item['projectType'] }}</td>
                        <td>{{ number_format($item['quantity'], (int) $item['quantity'] == $item['quantity'] ? 0 : 2) }} {{ $item['unit'] }}</td>
                        <td>{{ $item['procurementMode'] }}</td>
                        <td>{{ $item['preProcurementConference'] ? 'Yes' : 'No' }}</td>
                        <td>{{ $item['procurementStartDate'] ?? '' }}</td>
                        <td>—</td>
                        <td>{{ $item['dateNeeded'] ?? '' }}</td>
                        <td>{{ $item['sourceOfFund'] ?? '' }}</td>
                        <td><strong>PHP {{ number_format($item['totalCost'], 2) }}</strong></td>
                        <td>{!! $attachCell !!}</td>
                        <td>{{ $item['justification'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="12" style="text-align:center;padding:26px;color:#A88B8C;">No items encoded for this PPMP.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9" class="ppmp-total-label">TOTAL BUDGET:</td>
                    <td class="ppmp-total-amount">PHP {{ number_format($proposalTotal, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="ppmp-signoff">
        <div>
            <span class="ppmp-signoff-label">Prepared by:</span>
            <div class="ppmp-signoff-name">{{ $proposalForm['preparedByName'] ?: '—' }}</div>
            <div class="ppmp-signoff-title">{{ $proposalForm['preparedByTitle'] ?: '' }}</div>
            <div class="ppmp-signoff-date">Date: {{ $proposalForm['preparedDate'] ?: '_____________' }}</div>
        </div>
        <div>
            <span class="ppmp-signoff-label">Reviewed by:</span>
            <div class="ppmp-signoff-name">{{ $proposalForm['reviewedByName'] ?: '—' }}</div>
            <div class="ppmp-signoff-title">{{ $proposalForm['reviewedByTitle'] ?: '' }}</div>
            <div class="ppmp-signoff-date">Date: {{ $proposalForm['reviewedDate'] ?: '_____________' }}</div>
        </div>
        <div>
            <span class="ppmp-signoff-label">Approved by:</span>
            <div class="ppmp-signoff-name">{{ $proposalForm['approvedByName'] ?: '—' }}</div>
            <div class="ppmp-signoff-title">{{ $proposalForm['approvedByTitle'] ?: '' }}</div>
            <div class="ppmp-signoff-date">Date: {{ $proposalForm['approvedDate'] ?: '_____________' }}</div>
        </div>
    </div>
</div>

</body>
</html>
