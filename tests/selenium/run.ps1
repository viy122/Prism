param(
    [switch]$Install,
    [switch]$Headed,
    [switch]$AllowMutations,
    [switch]$CollectOnly,
    [string]$Filter = '',
    [string]$Config = (Join-Path $PSScriptRoot 'workflow.local.json')
)

$ErrorActionPreference = 'Stop'
$testPython = Join-Path $PSScriptRoot '.venv\Scripts\python.exe'
Push-Location $PSScriptRoot
try {
    if ($Install) {
        if (-not (Test-Path -LiteralPath $testPython)) {
            python -m venv .venv
            if ($LASTEXITCODE -ne 0) { throw 'Could not create the Python virtual environment.' }
        }
        & $testPython -m pip install -r requirements.txt
        if ($LASTEXITCODE -ne 0) { throw 'Could not install Selenium test dependencies.' }
    }
    if (-not (Test-Path -LiteralPath $testPython)) {
        throw 'Run this script with -Install once to prepare its virtual environment.'
    }
    $testArgs = @('-m', 'pytest', '--config', $Config)
    if ($Headed) { $testArgs += '--headed' }
    if ($AllowMutations) { $testArgs += '--allow-mutations' }
    if ($CollectOnly) { $testArgs += '--collect-only' }
    if ($Filter) { $testArgs += @('-m', $Filter) }
    & $testPython @testArgs
    $testExitCode = $LASTEXITCODE
}
finally {
    Pop-Location
}
exit $testExitCode
