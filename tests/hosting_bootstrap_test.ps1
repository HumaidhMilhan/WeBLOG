param(
    [Parameter(Mandatory = $true)]
    [string]$BaseUrl
)

$BaseUrl = $BaseUrl.TrimEnd('/')
$failures = 0

function Test-HostingCondition
{
    param(
        [bool]$Condition,
        [string]$Message
    )

    if ($Condition) {
        Write-Output "PASS: $Message"
    } else {
        Write-Output "FAIL: $Message"
        $script:failures++
    }
}

$homeResponse = Invoke-WebRequest "$BaseUrl/" -SkipHttpErrorCheck
Test-HostingCondition ($homeResponse.StatusCode -eq 200) 'root page returns HTTP 200'
Test-HostingCondition ($homeResponse.Content.Contains('Hosting is connected')) 'root page contains the hosting marker'

$blockedPaths = @(
    '/backend/config/db.example.php',
    '/database/schema.sql',
    '/docs/IN2120-%20TakeHomeAssignment.pdf',
    '/.git/config'
)

foreach ($blockedPath in $blockedPaths) {
    $response = Invoke-WebRequest "$BaseUrl$blockedPath" -SkipHttpErrorCheck
    Test-HostingCondition ($response.StatusCode -in @(403, 404)) "$blockedPath is not publicly accessible"
}

if ($failures -gt 0) {
    Write-Output "FAILED: $failures hosting checks failed"
    exit 1
}

Write-Output 'PASSED: all hosting checks passed'
exit 0
