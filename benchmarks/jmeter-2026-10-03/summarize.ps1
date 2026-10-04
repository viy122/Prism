$ErrorActionPreference = 'Stop'
$normal = @(1,5,10,30,50 | ForEach-Object {
    Get-Content (Join-Path $PSScriptRoot ('final-{0:00}/summary.json' -f $_)) -Raw | ConvertFrom-Json
})
$sustained = Get-Content (Join-Path $PSScriptRoot 'sustained-50/summary.json') -Raw | ConvertFrom-Json
$baseline = $normal[0].avg_ms
$table1 = $normal | ForEach-Object {
    [pscustomobject]@{ConcurrentUsers=$_.users;Samples=$_.samples;AverageMs=$_.avg_ms;MinimumMs=$_.min_ms;MaximumMs=$_.max_ms;ThroughputRPS=$_.throughput_rps;FailedRequests=$_.failed;ErrorRatePercent=$_.error_pct;MeasurementSeconds=$_.measurement_seconds}
}
$table2 = $normal | ForEach-Object {
    [pscustomobject]@{ConcurrentUsers=$_.users;AverageMs=$_.avg_ms;DegradationPercent=100*($_.avg_ms-$baseline)/$baseline}
}
$table1 | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $PSScriptRoot 'table-1-load-results.csv')
$table2 | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $PSScriptRoot 'table-2-scalability.csv')
$allFailed = ($normal | Measure-Object failed -Sum).Sum
$allSamples = ($normal | Measure-Object samples -Sum).Sum
$audit = @()
foreach ($run in @($normal) + @($sustained)) {
    $dir = Join-Path $PSScriptRoot $run.run
    $raw = @(Import-Csv (Join-Path $dir 'samples.jtl'))
    $measured = @($raw | Where-Object label -eq 'MEASURED - Procurement dashboard FY2026')
    if ($measured.Count -ne $run.samples) { throw 'Summary/sample-count mismatch.' }
    # Preserve JMeter's exact unquoted header and CSV serialization for its report generator.
    Get-Content (Join-Path $dir 'samples.jtl') | Where-Object {
        $_ -like 'timeStamp,*' -or $_ -like '*,MEASURED - Procurement dashboard FY2026,*'
    } | Set-Content -Encoding ASCII -Path (Join-Path $dir 'measured-report.jtl')
    $audit += [pscustomobject]@{
        run=$run.run; sample_count=$measured.Count; distinct_threads=@($measured.threadName | Sort-Object -Unique).Count;
        response_codes=@($measured | Group-Object responseCode | ForEach-Object {[pscustomobject]@{code=$_.Name;count=$_.Count}});
        raw_sha256=(Get-FileHash (Join-Path $dir 'samples.jtl') -Algorithm SHA256).Hash
    }
}
$audit | ConvertTo-Json -Depth 5 | Set-Content -Encoding UTF8 -Path (Join-Path $PSScriptRoot 'sample-audit.json')
$result = [ordered]@{normal=$table1;scalability=$table2;normal_failed=$allFailed;normal_samples=$allSamples;normal_error_pct=100*$allFailed/$allSamples;sustained=$sustained}
$result | ConvertTo-Json -Depth 6 | Set-Content -Encoding UTF8 -Path (Join-Path $PSScriptRoot 'results.json')
Write-Output ($result | ConvertTo-Json -Depth 6)
