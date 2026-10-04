# Dot-source this file in the same PowerShell terminal used to run Selenium.
$credentialPath = Join-Path $PSScriptRoot 'fixtures\private\credentials.json'
if (-not (Test-Path -LiteralPath $credentialPath)) { throw 'Prepared local credentials are missing.' }
$preparedCredentials = Get-Content -LiteralPath $credentialPath -Raw | ConvertFrom-Json
foreach ($entry in $preparedCredentials.PSObject.Properties) {
    if ($entry.Name -notmatch '^PRISM_UI_(OFFICE|FINANCE|CHANCELLOR|PROCUREMENT|ADMIN|NEXT)_PASSWORD$') {
        throw 'Unexpected credential variable in local fixture file.'
    }
    [Environment]::SetEnvironmentVariable($entry.Name, [string]$entry.Value, 'Process')
}
Remove-Variable preparedCredentials, entry
Write-Host 'All six prepared Selenium password variables are set for this terminal.'
