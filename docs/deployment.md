# WeBLOG Deployment

The public application runs at [https://humaidh.alwaysdata.net](https://humaidh.alwaysdata.net) from the GitHub `main` branch. Deploy only commits that have passed local verification.

## Verify Locally

Run in PowerShell from the repository root:

```powershell
$publicUrl = 'https://humaidh.alwaysdata.net'
& 'tests\hosting_bootstrap_test.ps1' -BaseUrl $publicUrl

$lintFailures = 0
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    C:\xampp\php\php.exe -l $_.FullName
    if ($LASTEXITCODE -ne 0) { $lintFailures++ }
}
if ($lintFailures -ne 0) { throw "$lintFailures PHP files failed syntax validation" }

git diff --check
git status --short
```

## Publish and Deploy

Commit only the verified files, then run:

```powershell
git push origin main
ssh 'humaidh@ssh-humaidh.alwaysdata.net' "cd /home/humaidh/weblog && git pull --ff-only origin main"
```

Do not use force push, `git reset --hard`, or a forced server pull.

## Verify the Release

```powershell
$localCommit = git rev-parse HEAD
$originCommit = git rev-parse origin/main
$deployedCommit = ssh 'humaidh@ssh-humaidh.alwaysdata.net' "cd /home/humaidh/weblog && git rev-parse HEAD"

if ($localCommit -ne $originCommit) { throw 'Local main does not match origin/main' }
if ($localCommit -ne $deployedCommit.Trim()) { throw 'The deployed commit does not match local main' }

& 'tests\hosting_bootstrap_test.ps1' -BaseUrl 'https://humaidh.alwaysdata.net'
```

Run the phase-specific public user-flow checks after this common smoke test.

## Database Configuration

Production credentials exist only in `/home/humaidh/weblog/backend/config/db.php`. This file is ignored by Git and must never be committed.

The production connection uses:

- Host: `mysql-humaidh.alwaysdata.net`
- Database: `humaidh_weblog`
- User: `humaidh_weblog`

The password remains only in the ignored server configuration and the alwaysdata database settings.

## Database Changes

Export the production database before destructive schema changes. Apply schema changes explicitly over SSH or through phpMyAdmin; do not run schema-altering SQL during a normal page request.

## Rollback

Create and push a Git revert commit, deploy it with the normal fast-forward workflow, and repeat the public smoke test. Do not rewrite `main` or forcibly reset the server checkout.
