# Alwaysdata Hosting Bootstrap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a secure, publicly accessible alwaysdata deployment for WeBLOG and prove a repeatable Git-over-SSH release workflow before rebuilding Phase 1.

**Architecture:** The public alwaysdata PHP site serves a clone of the GitHub `main` branch from the repository root. A server-only ignored PDO configuration connects the application to alwaysdata MySQL/MariaDB, while root Apache rules block non-public directories. Releases are verified locally, pushed to GitHub, pulled with `git pull --ff-only` over SSH, and smoke-tested through HTTPS.

**Tech Stack:** PHP 8.2, Apache 2.4, MySQL/MariaDB, PDO, Git, SSH, PowerShell, HTML5, CSS3

## Global Constraints

- Application code uses plain HTML, CSS, vanilla JavaScript, procedural PHP, and PDO; no framework or ORM is introduced.
- Source code contains no comments, matching `guidelines.md`.
- PHP 8.2 is used locally and selected on alwaysdata.
- Only the GitHub `main` branch is deployed.
- Deployment uses `git pull --ff-only`; force pulls, force pushes, and destructive resets are prohibited.
- `backend/config/db.php` remains ignored and contains environment-specific credentials.
- No password, private key, database credential, or raw exception is committed or printed publicly.
- The alwaysdata PHP site serves the repository root, while Apache denies public access to configuration, include, database, documentation, hidden, and Git files.
- Public verification uses HTTPS.
- GitHub Actions deployment is outside this bootstrap.

## File Structure

- Create `index.php`: safe public bootstrap page used to verify the hosting pipeline before Phase 1 is rebuilt.
- Create `.htaccess`: disable directory listing and deny HTTP access to sensitive repository paths.
- Create `backend/config/db.example.php`: working local-shaped PDO configuration template with safe error handling and no production secret.
- Create `tests/hosting_bootstrap_test.php`: dependency-free CLI checks for required hosting files and security rules.
- Modify `frontend/css/style.css`: add the minimal presentation rules used by the bootstrap page.
- Create `docs/deployment.md`: durable manual deployment and verification runbook.
- Modify `README.md`: link to the deployment runbook and state the supported release workflow.
- Preserve `backend/config/db.php`: ignored local configuration; create a separate ignored copy on alwaysdata.

---

### Task 1: Provision alwaysdata and Create the Initial Server Clone

**Files:**
- Create remotely: the `$sitePath` directory derived as `/home/$alwaysdataAccount/weblog` through `git clone`
- Create through alwaysdata administration: one MySQL/MariaDB database and one database user
- No local source files change in this task

**Interfaces:**
- Consumes: GitHub repository `https://github.com/HumaidhMilhan/WeBLOG.git` and the approved `main` branch.
- Produces: `$alwaysdataAccount`, `$publicUrl`, `$alwaysdataSshHost`, `$sitePath`, a remote repository at `$sitePath`, and database connection values used by Task 3.

- [ ] **Step 1: Publish the approved design and implementation plan**

Run from the local repository:

```powershell
git status --short --branch
git push origin main
$localCommit = git rev-parse HEAD
$remoteCommit = git rev-parse origin/main
if ($localCommit -ne $remoteCommit) { throw 'origin/main does not match the local main branch' }
```

Expected: the push succeeds and both commit hashes are identical.

- [ ] **Step 2: Create the free hosting account**

Create an alwaysdata Free account at `https://www.alwaysdata.com/en/register/`. In the administration interface, keep the assigned `alwaysdata.net` address and set the account-wide PHP version to 8.2 under `Environment > PHP`.

Record the non-secret account name in the current PowerShell session:

```powershell
$alwaysdataAccount = Read-Host 'alwaysdata account name'
$alwaysdataSshHost = "ssh-$alwaysdataAccount.alwaysdata.net"
$publicUrl = "https://$alwaysdataAccount.alwaysdata.net"
$sitePath = "/home/$alwaysdataAccount/weblog"
Write-Output $alwaysdataSshHost
Write-Output $publicUrl
Write-Output $sitePath
```

Expected: the three derived values match the connection information displayed by alwaysdata.

- [ ] **Step 3: Prepare an SSH key without replacing an existing key**

Run:

```powershell
$keyPath = Join-Path $env:USERPROFILE '.ssh\id_ed25519'
if (-not (Test-Path -LiteralPath "$keyPath.pub")) {
    ssh-keygen -t ed25519 -f $keyPath
}
Get-Content -Raw "$keyPath.pub"
```

Expected: an existing key is reused or a new ED25519 key pair is created, and only the public key is displayed.

- [ ] **Step 4: Enable and verify alwaysdata SSH access**

In `Remote access > SSH`, enable the default SSH user and temporarily allow password login. Make the public key from Step 3 the only new line in the account user's `.ssh/authorized_keys` file, then disable password login after key authentication succeeds.

Run locally:

```powershell
ssh "$alwaysdataAccount@$alwaysdataSshHost" "php -v"
ssh "$alwaysdataAccount@$alwaysdataSshHost" "git --version"
```

Expected: PHP reports version 8.2 and Git reports an installed version without requesting the account password.

- [ ] **Step 5: Create the production database and database user**

In `Databases > MySQL`, create a database dedicated to WeBLOG. Create or select a database user with access to that database. Record the following values in a password manager or other private location:

- Host shown by alwaysdata
- Database name
- Database username
- Database password

Expected: the database and user appear in the alwaysdata administration interface. No credential is added to the repository or this plan.

- [ ] **Step 6: Clone the current `main` branch on the server**

Open an SSH session:

```powershell
ssh "$alwaysdataAccount@$alwaysdataSshHost"
```

Run inside the alwaysdata shell:

```bash
git clone https://github.com/HumaidhMilhan/WeBLOG.git ~/weblog
cd ~/weblog
git branch --show-current
git status --short --branch
git rev-parse HEAD
```

Expected: the branch is `main`, the worktree is clean, and the commit equals local `origin/main`.

- [ ] **Step 7: Configure the initial PHP site**

In `Web > Sites`, add a site with these values:

- Address: the `$publicUrl` host name printed in Step 2
- Type: `PHP`
- Path: the `$sitePath` value printed in Step 2
- PHP version: account default, which is 8.2
- SSL: enabled
- HTTP-to-HTTPS redirect: enabled

Expected: alwaysdata reports the site as active.

- [ ] **Step 8: Verify the pre-bootstrap public PHP route**

Run locally:

```powershell
$loginResponse = Invoke-WebRequest "$publicUrl/frontend/pages/login_view.php"
if ($loginResponse.StatusCode -ne 200) { throw "Unexpected status: $($loginResponse.StatusCode)" }
if (-not $loginResponse.Content.Contains('Login to WeBLOG')) { throw 'Login page marker was not found' }
```

Expected: HTTP 200 over HTTPS and the existing login page marker is present. This confirms PHP execution before the root bootstrap files are added.

---

### Task 2: Add the Hosting-Safe Public Bootstrap

**Files:**
- Create: `tests/hosting_bootstrap_test.php`
- Create: `index.php`
- Create: `.htaccess`
- Create: `backend/config/db.example.php`
- Modify: `frontend/css/style.css`

**Interfaces:**
- Consumes: repository-root document path from Task 1 and existing `frontend/css/style.css`.
- Produces: public GET `/`, protected sensitive paths, `backend/config/db.example.php` for Task 3, and `php tests/hosting_bootstrap_test.php` for all later deployment changes.
- Before the deployment steps, restore the connection variables in a new PowerShell session with:

```powershell
$alwaysdataAccount = Read-Host 'alwaysdata account name'
$alwaysdataSshHost = "ssh-$alwaysdataAccount.alwaysdata.net"
$publicUrl = "https://$alwaysdataAccount.alwaysdata.net"
```

- [ ] **Step 1: Write the failing bootstrap test**

Create the test directory if it does not exist:

```powershell
New-Item -ItemType Directory -Force -Path tests | Out-Null
```

Create `tests/hosting_bootstrap_test.php`:

```php
<?php
$root = dirname(__DIR__);
$failures = 0;

function checkHostingCondition(bool $condition, string $message): void
{
    global $failures;

    if ($condition) {
        echo "PASS: $message" . PHP_EOL;
    } else {
        echo "FAIL: $message" . PHP_EOL;
        $failures++;
    }
}

$indexPath = $root . DIRECTORY_SEPARATOR . 'index.php';
$accessPath = $root . DIRECTORY_SEPARATOR . '.htaccess';
$examplePath = $root . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db.example.php';
$ignorePath = $root . DIRECTORY_SEPARATOR . '.gitignore';

checkHostingCondition(file_exists($indexPath), 'root index exists');

$indexContent = file_exists($indexPath) ? file_get_contents($indexPath) : '';
checkHostingCondition(!str_contains($indexContent, 'phpinfo'), 'root index does not expose phpinfo');
checkHostingCondition(str_contains($indexContent, 'Hosting is connected'), 'root index contains the bootstrap marker');

checkHostingCondition(file_exists($accessPath), 'Apache access rules exist');
$accessContent = file_exists($accessPath) ? file_get_contents($accessPath) : '';

foreach (['backend/config', 'backend/includes', 'database', 'docs', '(^|/)\\.'] as $protectedValue) {
    checkHostingCondition(str_contains($accessContent, $protectedValue), "Apache rules protect $protectedValue");
}

checkHostingCondition(file_exists($examplePath), 'database configuration example exists');

$ignoreContent = file_get_contents($ignorePath);
checkHostingCondition(str_contains($ignoreContent, 'backend/config/db.php'), 'real database configuration remains ignored');

exit($failures === 0 ? 0 : 1);
```

- [ ] **Step 2: Run the test and verify the expected failure**

Run:

```powershell
C:\xampp\php\php.exe tests\hosting_bootstrap_test.php
```

Expected: exit code 1 with failures including `root index exists`, `root index contains the bootstrap marker`, `Apache access rules exist`, and `database configuration example exists`.

- [ ] **Step 3: Create the safe public entry page**

Create `index.php`:

```php
<?php
http_response_code(200);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeBLOG</title>
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>
    <main class="auth-container setup-container">
        <h2>WeBLOG</h2>
        <p class="setup-message">Hosting is connected and the application is being rebuilt phase by phase.</p>
        <a class="setup-link" href="frontend/pages/login_view.php">Open the current login page</a>
    </main>
</body>
</html>
```

- [ ] **Step 4: Create the Apache protection rules**

Create `.htaccess`:

```apache
Options -Indexes
RewriteEngine On
RewriteRule ^backend/config(?:/|$) - [F,L,NC]
RewriteRule ^backend/includes(?:/|$) - [F,L,NC]
RewriteRule ^database(?:/|$) - [F,L,NC]
RewriteRule ^docs(?:/|$) - [F,L,NC]
RewriteRule (^|/)\. - [F,L]
```

- [ ] **Step 5: Create the safe database configuration example**

Create `backend/config/db.example.php`:

```php
<?php
$host = 'localhost';
$db = 'weblog_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection is currently unavailable.');
}
```

- [ ] **Step 6: Add bootstrap page styling**

Append to `frontend/css/style.css`:

```css
.setup-container {
    text-align: center;
}

.setup-message {
    margin-bottom: 20px;
    color: #555;
}

.setup-link {
    display: inline-block;
    padding: 10px 18px;
    border-radius: 4px;
    background-color: #3498db;
    color: #ffffff;
    text-decoration: none;
    font-weight: bold;
}

.setup-link:hover {
    background-color: #2980b9;
}
```

- [ ] **Step 7: Run the bootstrap test and PHP syntax checks**

Run:

```powershell
C:\xampp\php\php.exe tests\hosting_bootstrap_test.php
$lintFailures = 0
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    C:\xampp\php\php.exe -l $_.FullName
    if ($LASTEXITCODE -ne 0) { $lintFailures++ }
}
if ($lintFailures -ne 0) { throw "$lintFailures PHP files failed syntax validation" }
```

Expected: every bootstrap assertion prints `PASS`, the test exits 0, and every PHP file reports no syntax errors.

- [ ] **Step 8: Review and commit the local bootstrap**

Run:

```powershell
git diff --check
git status --short
git add -- index.php .htaccess backend/config/db.example.php frontend/css/style.css tests/hosting_bootstrap_test.php
git diff --cached --check
git commit -m "feat: add hosting bootstrap"
```

Expected: only the five intended files are committed.

- [ ] **Step 9: Push and deploy the bootstrap through Git**

Run locally:

```powershell
git push origin main
ssh "$alwaysdataAccount@$alwaysdataSshHost" "cd ~/weblog && git pull --ff-only origin main"
```

Expected: the push succeeds and the server pull fast-forwards to the new commit.

- [ ] **Step 10: Verify the public page and blocked paths**

Run locally with PowerShell 7:

```powershell
$homeResponse = Invoke-WebRequest "$publicUrl/"
if ($homeResponse.StatusCode -ne 200) { throw "Unexpected home status: $($homeResponse.StatusCode)" }
if (-not $homeResponse.Content.Contains('Hosting is connected')) { throw 'Bootstrap marker was not found' }

$blockedPaths = @(
    '/backend/config/db.example.php',
    '/database/schema.sql',
    '/docs/IN2120-%20TakeHomeAssignment.pdf',
    '/.git/config'
)

foreach ($blockedPath in $blockedPaths) {
    $response = Invoke-WebRequest "$publicUrl$blockedPath" -SkipHttpErrorCheck
    if ($response.StatusCode -notin @(403, 404)) {
        throw "$blockedPath returned $($response.StatusCode)"
    }
}
```

Expected: `/` returns 200 with the bootstrap marker and every protected path returns 403 or 404.

---

### Task 3: Configure and Verify the Production Database

**Files:**
- Create remotely and keep ignored: `$sitePath/backend/config/db.php`
- Read: `backend/config/db.example.php`
- Read and import: `database/schema.sql`
- No tracked local source files change in this task

**Interfaces:**
- Consumes: the database host, name, username, and password from Task 1 plus the configuration example from Task 2.
- Produces: a server-only `$pdo` connection in `backend/config/db.php` and populated `user` and `blogPost` tables.
- Restore the SSH connection variables in a new PowerShell session with:

```powershell
$alwaysdataAccount = Read-Host 'alwaysdata account name'
$alwaysdataSshHost = "ssh-$alwaysdataAccount.alwaysdata.net"
$publicUrl = "https://$alwaysdataAccount.alwaysdata.net"
```

- [ ] **Step 1: Create the ignored production configuration from the example**

Open the server shell:

```powershell
ssh "$alwaysdataAccount@$alwaysdataSshHost"
```

Run remotely:

```bash
cd ~/weblog
cp backend/config/db.example.php backend/config/db.php
nano backend/config/db.php
```

Replace only the `$host`, `$db`, `$user`, and `$pass` values with the exact values recorded from `Databases > MySQL`. Save the file and exit the editor.

Verify that Git does not expose or track it:

```bash
git status --short --ignored backend/config/db.php
git check-ignore -v backend/config/db.php
```

Expected: the file is reported as ignored and never appears as a staged or untracked file.

- [ ] **Step 2: Verify PDO connectivity from the server CLI**

Run remotely:

```bash
cd ~/weblog
php -r 'require "backend/config/db.php"; echo $pdo->query("SELECT 1")->fetchColumn() . PHP_EOL;'
```

Expected: the command prints `1` and no credential or exception.

- [ ] **Step 3: Import the schema without placing the password in shell history**

Run remotely and enter the non-secret values when prompted:

```bash
read -r -p "Database host: " db_host
read -r -p "Database name: " db_name
read -r -p "Database username: " db_user
mysql -h "$db_host" -u "$db_user" -p "$db_name" < database/schema.sql
```

Expected: `mysql` prompts separately for the password and completes without an SQL error.

- [ ] **Step 4: Verify both required tables**

Run remotely in the same shell session:

```bash
mysql -h "$db_host" -u "$db_user" -p "$db_name" -e "SHOW TABLES;"
```

Expected: the result contains exactly the required application tables `user` and `blogPost`.

- [ ] **Step 5: Verify database-backed handlers fail safely on non-POST requests**

Run locally:

```powershell
$loginHandler = Invoke-WebRequest "$publicUrl/backend/api/login.php" -MaximumRedirection 0 -SkipHttpErrorCheck
$registerHandler = Invoke-WebRequest "$publicUrl/backend/api/register.php" -MaximumRedirection 0 -SkipHttpErrorCheck
if ($loginHandler.StatusCode -notin @(302, 303)) { throw "Login handler returned $($loginHandler.StatusCode)" }
if ($registerHandler.StatusCode -notin @(302, 303)) { throw "Register handler returned $($registerHandler.StatusCode)" }
```

Expected: both handlers redirect rather than exposing an exception or configuration detail.

---

### Task 4: Document and Prove the Repeatable Release Workflow

**Files:**
- Modify: `tests/hosting_bootstrap_test.php`
- Create: `docs/deployment.md`
- Modify: `README.md`

**Interfaces:**
- Consumes: `php tests/hosting_bootstrap_test.php`, alwaysdata SSH access, `$publicUrl`, and the remote `~/weblog` clone.
- Produces: a durable deployment runbook, a README entry point, and a verified local-to-public Git release cycle.
- Restore the deployment variables in a new PowerShell session with:

```powershell
$alwaysdataAccount = Read-Host 'alwaysdata account name'
$alwaysdataSshHost = "ssh-$alwaysdataAccount.alwaysdata.net"
$publicUrl = "https://$alwaysdataAccount.alwaysdata.net"
```

- [ ] **Step 1: Extend the test with failing documentation requirements**

Insert before the final `exit` in `tests/hosting_bootstrap_test.php`:

```php
$deploymentPath = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'deployment.md';
$readmePath = $root . DIRECTORY_SEPARATOR . 'README.md';

checkHostingCondition(file_exists($deploymentPath), 'deployment runbook exists');

$deploymentContent = file_exists($deploymentPath) ? file_get_contents($deploymentPath) : '';
checkHostingCondition(str_contains($deploymentContent, 'git pull --ff-only origin main'), 'deployment runbook uses fast-forward pulls');
checkHostingCondition(str_contains($deploymentContent, 'hosting_bootstrap_test.php'), 'deployment runbook includes local verification');

$readmeContent = file_get_contents($readmePath);
checkHostingCondition(str_contains($readmeContent, '## Deployment'), 'README links the deployment workflow');
```

- [ ] **Step 2: Run the test and verify the documentation failure**

Run:

```powershell
C:\xampp\php\php.exe tests\hosting_bootstrap_test.php
```

Expected: exit code 1 with failures for the missing deployment runbook and README deployment section, while the earlier bootstrap checks still pass.

- [ ] **Step 3: Create the deployment runbook**

Create `docs/deployment.md` with this content:

````markdown
# WeBLOG Deployment

The public application runs on alwaysdata from the GitHub `main` branch. Deploy only commits that have passed local verification.

## Set Connection Values

Run in PowerShell:

```powershell
$alwaysdataAccount = Read-Host 'alwaysdata account name'
$alwaysdataSshHost = "ssh-$alwaysdataAccount.alwaysdata.net"
$publicUrl = "https://$alwaysdataAccount.alwaysdata.net"
```

## Verify Locally

```powershell
C:\xampp\php\php.exe tests\hosting_bootstrap_test.php
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
ssh "$alwaysdataAccount@$alwaysdataSshHost" "cd ~/weblog && git pull --ff-only origin main"
```

Do not use force push, `git reset --hard`, or a forced server pull.

## Verify the Release

```powershell
$localCommit = git rev-parse HEAD
$deployedCommit = ssh "$alwaysdataAccount@$alwaysdataSshHost" "cd ~/weblog && git rev-parse HEAD"
if ($localCommit -ne $deployedCommit.Trim()) { throw 'The deployed commit does not match local main' }

$response = Invoke-WebRequest "$publicUrl/"
if ($response.StatusCode -ne 200) { throw "Unexpected status: $($response.StatusCode)" }
```

Run the phase-specific public user-flow checks after this common smoke test.

## Database Changes

Export the production database before destructive schema changes. Apply schema changes explicitly over SSH or through phpMyAdmin; do not run schema-altering SQL during a normal page request.

## Rollback

Create and push a Git revert commit, deploy it with the normal fast-forward workflow, and repeat the public smoke test. Do not rewrite `main` or forcibly reset the server checkout.
````

- [ ] **Step 4: Add the deployment entry point to the README**

Append to `README.md`:

```markdown

## Deployment

The public application is deployed to alwaysdata from the verified `main` branch using Git over SSH. See [docs/deployment.md](docs/deployment.md) for the release, verification, database-change, and rollback procedures.
```

- [ ] **Step 5: Run all local hosting verification**

Run:

```powershell
C:\xampp\php\php.exe tests\hosting_bootstrap_test.php
$lintFailures = 0
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    C:\xampp\php\php.exe -l $_.FullName
    if ($LASTEXITCODE -ne 0) { $lintFailures++ }
}
if ($lintFailures -ne 0) { throw "$lintFailures PHP files failed syntax validation" }
git diff --check
```

Expected: every hosting assertion passes, every PHP file passes syntax validation, and `git diff --check` reports no whitespace errors.

- [ ] **Step 6: Commit and publish the runbook**

Run:

```powershell
git add -- tests/hosting_bootstrap_test.php docs/deployment.md README.md
git diff --cached --check
git commit -m "docs: add alwaysdata deployment runbook"
git push origin main
```

Expected: the commit includes exactly the test, runbook, and README changes, and the push succeeds.

- [ ] **Step 7: Deploy and prove commit parity**

Run:

```powershell
ssh "$alwaysdataAccount@$alwaysdataSshHost" "cd ~/weblog && git pull --ff-only origin main"
$localCommit = git rev-parse HEAD
$deployedCommit = ssh "$alwaysdataAccount@$alwaysdataSshHost" "cd ~/weblog && git rev-parse HEAD"
if ($localCommit -ne $deployedCommit.Trim()) { throw 'The deployed commit does not match local main' }
```

Expected: the server fast-forwards and both hashes match exactly.

- [ ] **Step 8: Run the final public smoke and protection checks**

Run:

```powershell
$homeResponse = Invoke-WebRequest "$publicUrl/"
if ($homeResponse.StatusCode -ne 200) { throw "Unexpected home status: $($homeResponse.StatusCode)" }
if (-not $homeResponse.Content.Contains('Hosting is connected')) { throw 'Bootstrap marker was not found' }

$blockedPaths = @(
    '/backend/config/db.example.php',
    '/database/schema.sql',
    '/docs/deployment.md',
    '/.git/config'
)

foreach ($blockedPath in $blockedPaths) {
    $response = Invoke-WebRequest "$publicUrl$blockedPath" -SkipHttpErrorCheck
    if ($response.StatusCode -notin @(403, 404)) {
        throw "$blockedPath returned $($response.StatusCode)"
    }
}
```

Expected: the root page returns 200, displays the bootstrap marker, and all sensitive paths return 403 or 404.

## Completion Gate

The hosting bootstrap is complete only when all of the following are evidenced in the same execution session:

- Local hosting tests exit 0.
- Every PHP file passes `php -l`.
- The local `main`, `origin/main`, and deployed server commit hashes match.
- The public HTTPS root returns 200 with the bootstrap marker.
- Protected paths return 403 or 404.
- Server-side PDO connectivity returns `1`.
- Production contains the `user` and `blogPost` tables.
- `backend/config/db.php` remains ignored and no secret appears in Git.
