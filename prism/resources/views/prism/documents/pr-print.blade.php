<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pr->number }} – Purchase Request</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt;
    color: #111;
    background: #f0f0f0;
    padding: 24px;
  }

  .page {
    background: #fff;
    max-width: 210mm;
    margin: 0 auto;
    padding: 18mm 18mm 20mm;
    box-shadow: 0 2px 12px rgba(0,0,0,.15);
  }

  /* ── Header ── */
  .header { text-align: center; margin-bottom: 14px; border-bottom: 3px solid #800000; padding-bottom: 10px; }
  .header .republic { font-size: 9pt; color: #555; letter-spacing: .5px; }
  .header .university { font-size: 15pt; font-weight: bold; color: #800000; line-height: 1.3; margin: 4px 0 2px; }
  .header .campus { font-size: 10pt; color: #444; }
  .header .address { font-size: 8.5pt; color: #666; margin-top: 2px; }

  /* ── Document title bar ── */
  .doc-title-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 14px 0 12px;
    gap: 12px;
  }
  .doc-title {
    font-size: 15pt;
    font-weight: bold;
    color: #800000;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom: 2px solid #800000;
    padding-bottom: 3px;
  }
  .doc-meta-box { text-align: right; font-size: 9.5pt; line-height: 1.8; }
  .doc-meta-box .meta-row { display: flex; gap: 8px; justify-content: flex-end; }
  .doc-meta-box .meta-label { color: #555; }
  .doc-meta-box .meta-val { font-weight: bold; min-width: 120px; border-bottom: 1px solid #aaa; }

  /* ── Info grid ── */
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; margin-bottom: 14px; font-size: 9.5pt; }
  .info-field { display: flex; flex-direction: column; }
  .info-label { font-size: 8pt; color: #777; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 1px; }
  .info-value { border-bottom: 1px solid #bbb; padding-bottom: 2px; font-weight: 600; }
  .info-field.full { grid-column: 1 / -1; }

  /* ── Items table ── */
  table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9pt; }
  th {
    background: #800000;
    color: #fff;
    padding: 6px 5px;
    text-align: center;
    font-size: 8.5pt;
    letter-spacing: .3px;
    border: 1px solid #600000;
  }
  td {
    padding: 5px 5px;
    border: 1px solid #ccc;
    vertical-align: top;
  }
  tr:nth-child(even) td { background: #fdf5f5; }
  td.num { text-align: center; }
  td.right { text-align: right; }
  td.desc { min-width: 180px; }
  .total-row td {
    font-weight: bold;
    background: #f9f0f0;
    border-top: 2px solid #800000;
    font-size: 10pt;
  }

  /* ── Purpose section ── */
  .purpose-section { margin-bottom: 18px; font-size: 9.5pt; }
  .section-label { font-size: 8pt; color: #777; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 4px; }
  .purpose-box {
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 8px 10px;
    min-height: 48px;
    line-height: 1.5;
    background: #fafafa;
  }

  /* ── Signature blocks ── */
  .sig-section { margin-top: 20px; }
  .sig-section-title {
    font-size: 9pt;
    color: #800000;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1.5px solid #800000;
    padding-bottom: 4px;
    margin-bottom: 14px;
  }
  .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 28px; }
  .sig-block {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 10px 12px 8px;
    background: #fafafa;
  }
  .sig-block.center { grid-column: 1 / -1; max-width: 50%; margin: 0 auto; }
  .sig-role { font-size: 8pt; color: #800000; font-weight: bold; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
  .sig-line {
    border-bottom: 1.5px solid #333;
    height: 38px;
    margin-bottom: 4px;
  }
  .sig-name { font-size: 9pt; font-weight: bold; text-align: center; border-bottom: 1px solid #888; padding-bottom: 2px; margin-bottom: 2px; }
  .sig-designation { font-size: 8pt; color: #555; text-align: center; margin-bottom: 6px; }
  .sig-date { font-size: 7.5pt; color: #888; display: flex; gap: 6px; }
  .sig-date .date-label { white-space: nowrap; }
  .sig-date .date-line { flex: 1; border-bottom: 1px solid #bbb; }

  /* ── Footer ── */
  .doc-footer {
    margin-top: 20px;
    padding-top: 8px;
    border-top: 1px solid #ddd;
    font-size: 7.5pt;
    color: #aaa;
    display: flex;
    justify-content: space-between;
  }

  /* ── Print button (screen only) ── */
  .print-btn-bar {
    text-align: center;
    margin-bottom: 18px;
  }
  .print-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #800000;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 28px;
    font-size: 12pt;
    font-weight: bold;
    cursor: pointer;
    letter-spacing: .3px;
  }
  .print-btn:hover { background: #600000; }

  /* ── Print media ── */
  @media print {
    body { background: #fff; padding: 0; }
    .page { box-shadow: none; padding: 10mm 12mm 14mm; max-width: 100%; }
    .print-btn-bar { display: none; }
    @page { size: A4; margin: 10mm 12mm; }
  }
</style>
</head>
<body>

<div class="print-btn-bar">
  <button class="print-btn" onclick="window.print()">&#128438; Print This Document</button>
</div>

<div class="page">

  {{-- ── Letterhead ── --}}
  <div class="header">
    <div class="republic">Republic of the Philippines</div>
    <div class="university">Batangas State University</div>
    <div class="campus">ARASOF – Nasugbu Campus</div>
    <div class="address">Bucana Road, Nasugbu, Batangas &nbsp;|&nbsp; www.batstate-u.edu.ph</div>
  </div>

  {{-- ── Title bar ── --}}
  <div class="doc-title-bar">
    <div class="doc-title">Purchase Request</div>
    <div class="doc-meta-box">
      <div class="meta-row">
        <span class="meta-label">PR No.:</span>
        <span class="meta-val">{{ $pr->number }}</span>
      </div>
      <div class="meta-row">
        <span class="meta-label">Date:</span>
        <span class="meta-val">{{ $pr->created_at->format('F d, Y') }}</span>
      </div>
      <div class="meta-row">
        <span class="meta-label">Fiscal Year:</span>
        <span class="meta-val">{{ $pr->fiscal_year }}</span>
      </div>
    </div>
  </div>

  {{-- ── Info grid ── --}}
  <div class="info-grid">
    <div class="info-field">
      <span class="info-label">Requesting Office / Section</span>
      <span class="info-value">{{ $pr->office?->name ?? '—' }}</span>
    </div>
    <div class="info-field">
      <span class="info-label">Office Code</span>
      <span class="info-value">{{ $pr->office?->code ?? '—' }}</span>
    </div>
    <div class="info-field full">
      <span class="info-label">Document Title</span>
      <span class="info-value">{{ $pr->title }}</span>
    </div>
  </div>

  {{-- ── Items table ── --}}
  <table>
    <thead>
      <tr>
        <th style="width:4%">No.</th>
        <th style="width:8%">Stock No.</th>
        <th style="width:8%">Unit</th>
        <th class="desc">Item Description</th>
        <th style="width:6%">Qty</th>
        <th style="width:12%">Unit Cost</th>
        <th style="width:12%">Total Cost</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($pr->items as $i => $item)
      <tr>
        <td class="num">{{ $i + 1 }}</td>
        <td class="num">—</td>
        <td class="num">{{ $item->unit }}</td>
        <td class="desc">
          <strong>{{ $item->name }}</strong>
          @if ($item->description)
            <br><span style="color:#555;font-size:8pt">{{ $item->description }}</span>
          @endif
        </td>
        <td class="num">{{ $item->quantity }}</td>
        <td class="right">{{ number_format($item->estimated_unit_cost, 2) }}</td>
        <td class="right">{{ number_format($item->estimated_total_cost, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="6" class="right" style="padding-right:8px;color:#800000;">TOTAL AMOUNT</td>
        <td class="right" style="color:#800000;">{{ number_format($pr->total_amount, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- ── Purpose ── --}}
  @if ($pr->description)
  <div class="purpose-section">
    <div class="section-label">Purpose / Justification</div>
    <div class="purpose-box">{{ $pr->description }}</div>
  </div>
  @endif

  {{-- ── Signature Blocks ── --}}
  <div class="sig-section">
    <div class="sig-section-title">Signatories</div>
    <div class="sig-grid">

      {{-- 1. End User / Office Head --}}
      <div class="sig-block">
        <div class="sig-role">1. Requested By</div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ strtoupper($pr->createdBy?->name ?? '________________________________') }}</div>
        <div class="sig-designation">{{ $pr->office?->name ?? 'Office Head / End User' }}</div>
        <div class="sig-date">
          <span class="date-label">Date Signed:</span>
          <span class="date-line"></span>
        </div>
      </div>

      {{-- 2. Vice Chancellor --}}
      <div class="sig-block">
        <div class="sig-role">2. Recommended By</div>
        <div class="sig-line"></div>
        <div class="sig-name">________________________________</div>
        <div class="sig-designation">Vice Chancellor for Administration &amp; Finance</div>
        <div class="sig-date">
          <span class="date-label">Date Signed:</span>
          <span class="date-line"></span>
        </div>
      </div>

      {{-- 3. Accounting / VC (3rd signer) --}}
      <div class="sig-block">
        <div class="sig-role">3. Reviewed By</div>
        <div class="sig-line"></div>
        <div class="sig-name">________________________________</div>
        <div class="sig-designation">Accounting Office</div>
        <div class="sig-date">
          <span class="date-label">Date Signed:</span>
          <span class="date-line"></span>
        </div>
      </div>

      {{-- 4. VC / Accounting (4th signer) --}}
      <div class="sig-block">
        <div class="sig-role">4. Noted By</div>
        <div class="sig-line"></div>
        <div class="sig-name">________________________________</div>
        <div class="sig-designation">Vice Chancellor / Accounting Office</div>
        <div class="sig-date">
          <span class="date-label">Date Signed:</span>
          <span class="date-line"></span>
        </div>
      </div>

      {{-- 5. Chancellor (centered, full width) --}}
      <div class="sig-block center">
        <div class="sig-role">5. Approved By</div>
        <div class="sig-line"></div>
        <div class="sig-name">________________________________</div>
        <div class="sig-designation">University Chancellor</div>
        <div class="sig-date">
          <span class="date-label">Date Signed:</span>
          <span class="date-line"></span>
        </div>
      </div>

    </div>
  </div>

  {{-- ── Footer ── --}}
  <div class="doc-footer">
    <span>PRISM – Procurement and Resource Information System for Management</span>
    <span>Generated: {{ now()->format('Y-m-d H:i') }}</span>
  </div>

</div>{{-- .page --}}

</body>
</html>
