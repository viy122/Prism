param(
    [Parameter(Mandatory=$true)][int]$Users,
    [int]$Iterations = 10,
    [int]$Duration = 0,
    [Parameter(Mandatory=$true)][string]$RunName
)
$ErrorActionPreference = 'Stop'
$javaExe = 'C:\Program Files\Android\Android Studio\jbr\bin\java.exe'
$jmeterJar = 'C:\Users\khriz\Downloads\apache-jmeter-5.6.3\apache-jmeter-5.6.3\bin\ApacheJMeter.jar'
$runDir = Join-Path $PSScriptRoot $RunName
if (Test-Path -LiteralPath $runDir) { throw "Refusing to overwrite existing run: $runDir" }
New-Item -ItemType Directory -Path $runDir | Out-Null
$before = Get-CimInstance Win32_OperatingSystem
$processStart = [DateTimeOffset]::UtcNow
$argsLine = '-Xms128m -Xmx512m -XX:MaxMetaspaceSize=192m -jar "' + $jmeterJar + '" -n -t "' + (Join-Path $PSScriptRoot 'dashboard.jmx') + '" -q "' + (Join-Path $PSScriptRoot 'benchmark.properties') + '" -l samples.jtl -j jmeter.log -Jusers=' + $Users + ' -Jiterations=' + $Iterations + ' -Jduration=' + $Duration
$benchmarkProcess = Start-Process -FilePath $javaExe -ArgumentList $argsLine -WorkingDirectory $runDir -WindowStyle Hidden -RedirectStandardOutput (Join-Path $runDir 'console.out.log') -RedirectStandardError (Join-Path $runDir 'console.err.log') -PassThru
$null = $benchmarkProcess.Handle
$observations = [System.Collections.Generic.List[object]]::new()
while (-not $benchmarkProcess.HasExited) {
    $os = Get-CimInstance Win32_OperatingSystem
    $observations.Add([pscustomobject]@{utc=[DateTimeOffset]::UtcNow.ToString('o');free_ram_mb=[math]::Round($os.FreePhysicalMemory/1024,2)})
    Write-Output ("RUN {0}: elapsed={1:N1}s free_RAM={2:N0}MB" -f $RunName,([DateTimeOffset]::UtcNow-$processStart).TotalSeconds,($os.FreePhysicalMemory/1024))
    $null = $benchmarkProcess.WaitForExit(10000)
    $benchmarkProcess.Refresh()
}
$benchmarkProcess.WaitForExit()
$processEnd = [DateTimeOffset]::UtcNow
$observations | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $runDir 'memory-observations.csv')
$all = @(Import-Csv (Join-Path $runDir 'samples.jtl'))
$setup = @($all | Where-Object { $_.label -like 'SETUP*' })
$samples = @($all | Where-Object { $_.label -eq 'MEASURED - Procurement dashboard FY2026' })
if ($setup.Count -ne 2*$Users -or @($setup | Where-Object success -ne 'true').Count -gt 0) { throw "Incomplete/failed authentication or warm-up in $RunName" }
if ($samples.Count -eq 0) { throw "No measured samples in $RunName" }
$perThread = @($samples | Group-Object threadName)
if ($perThread.Count -ne $Users) { throw "Expected $Users participating threads, got $($perThread.Count)" }
if ($Duration -eq 0 -and ($samples.Count -ne $Users*$Iterations -or @($perThread | Where-Object Count -ne $Iterations).Count -gt 0)) { throw "Incorrect measured iterations in $RunName" }
if (Select-String -Path (Join-Path $runDir 'jmeter.log') -Pattern 'Timed out waiting|Unsupported class file|OutOfMemoryError' -Quiet) { throw "JMeter execution error in $RunName" }
$samples | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $runDir 'measured.csv')
$elapsed = $samples | ForEach-Object { [double]$_.elapsed } | Measure-Object -Average -Minimum -Maximum -Sum
$first = ($samples | ForEach-Object { [long]$_.timeStamp } | Measure-Object -Minimum).Minimum
$last = ($samples | ForEach-Object { [long]$_.timeStamp + [long]$_.elapsed } | Measure-Object -Maximum).Maximum
$seconds = ($last-$first)/1000
$failed = @($samples | Where-Object success -ne 'true').Count
$summary = [ordered]@{
    run=$RunName;users=$Users;samples=$samples.Count;avg_ms=$elapsed.Average;min_ms=$elapsed.Minimum;max_ms=$elapsed.Maximum;
    throughput_rps=$samples.Count/$seconds;failed=$failed;error_pct=100*$failed/$samples.Count;
    measurement_seconds=$seconds;
    first_request_manila=[DateTimeOffset]::FromUnixTimeMilliseconds($first).ToOffset([TimeSpan]::FromHours(8)).ToString('o');
    last_completion_manila=[DateTimeOffset]::FromUnixTimeMilliseconds($last).ToOffset([TimeSpan]::FromHours(8)).ToString('o');
    configured_duration_seconds=$Duration;iterations_per_user=$(if ($Duration -eq 0) {$Iterations} else {$null});
    setup_samples_excluded=$setup.Count;participating_threads=$perThread.Count;
    min_samples_per_thread=($perThread | Measure-Object Count -Minimum).Minimum;
    max_samples_per_thread=($perThread | Measure-Object Count -Maximum).Maximum;
    peak_active_threads=($samples | ForEach-Object {[int]$_.allThreads} | Measure-Object -Maximum).Maximum;
    first_request_spread_ms=($perThread | ForEach-Object {($_.Group | ForEach-Object {[long]$_.timeStamp} | Measure-Object -Minimum).Minimum} | Measure-Object -Maximum).Maximum-$first;
    process_wall_seconds=($processEnd-$processStart).TotalSeconds;
    observed_min_free_ram_mb=($observations | Measure-Object free_ram_mb -Minimum).Minimum;
    command=$javaExe+' '+$argsLine;exit_code=$benchmarkProcess.ExitCode;
    failures=@($samples | Where-Object success -ne 'true' | Select-Object timeStamp,elapsed,responseCode,responseMessage,failureMessage)
}
$summary | ConvertTo-Json -Depth 5 | Set-Content -Encoding UTF8 -Path (Join-Path $runDir 'summary.json')
Write-Output ($summary | ConvertTo-Json -Depth 5)
if ($null -ne $benchmarkProcess.ExitCode -and $benchmarkProcess.ExitCode -ne 0) { throw "JMeter exited nonzero in $RunName" }
