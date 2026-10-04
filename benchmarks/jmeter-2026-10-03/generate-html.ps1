$ErrorActionPreference = 'Stop'
$javaExe = 'C:\Program Files\Android\Android Studio\jbr\bin\java.exe'
$jmeterJar = 'C:\Users\khriz\Downloads\apache-jmeter-5.6.3\apache-jmeter-5.6.3\bin\ApacheJMeter.jar'
foreach ($run in 'final-01','final-05','final-10','final-30','final-50','sustained-50') {
    $dir = Join-Path $PSScriptRoot $run
    $html = Join-Path $dir 'report'
    if (Test-Path -LiteralPath $html) { throw "Refusing to overwrite HTML report: $html" }
    & $javaExe -Xms128m -Xmx512m -jar $jmeterJar -g (Join-Path $dir 'measured-report.jtl') -o $html -j (Join-Path $dir 'report.log') -Jjmeter.reportgenerator.overall_granularity=1000
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path (Join-Path $html 'index.html'))) { throw "Report generation failed: $run" }
    Write-Output "Generated HTML report: $run"
}
