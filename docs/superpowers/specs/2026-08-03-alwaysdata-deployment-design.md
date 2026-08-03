# WeBLOG Alwaysdata Deployment Design

Date: 2026-08-03
Status: Approved

## Context

WeBLOG is an academic blog application built with plain HTML, CSS, JavaScript, procedural PHP, and MySQL. The assignment requires a public hosted version with the same functionality as the local version. The existing Phase 1 authentication code is incomplete, so hosting must be established before Phase 1 is rebuilt.

## Goals

- Provide a free public PHP and MySQL environment for the assignment.
- Make every completed phase deployable and testable through the public URL.
- Use Git as the source of truth for deployed application code.
- Keep deployment understandable and manageable for a beginner-level PHP project.
- Keep database credentials and other secrets out of Git.
- Preserve a straightforward path to deployment automation later.

## Non-goals

- Automatic deployment on every push during the initial setup.
- Containers, orchestration, or a separate managed database provider.
- Paid hosting or a custom domain during development.
- A staging environment separate from the public assignment site.

## Hosting Decision

The application will use the alwaysdata Free plan. It provides a public `alwaysdata.net` address, PHP hosting, managed MySQL/MariaDB, SSL, SSH access, and enough resources for this assignment.

The initial deployment method will be a manual fast-forward Git pull over SSH. This is preferred over immediate GitHub Actions automation because it keeps each deployment deliberate and makes failures easier to inspect. GitHub Actions may be added after the manual workflow has been proven reliable.

InfinityFree was considered but rejected because its free plan does not provide SSH. Deploying there would require an additional FTP or FTPS deployment mechanism.

## Deployment Architecture

The GitHub repository remains the source of truth. The alwaysdata account contains a clone of the repository and a server-only database configuration file.

```text
Local workspace
    |
    | commit and push
    v
GitHub main branch
    |
    | SSH followed by git pull --ff-only
    v
alwaysdata repository clone
    |
    +--> PHP website
    |
    +--> managed MySQL/MariaDB database
```

Only the `main` branch is deployed. Feature work may happen on other branches, but it becomes deployable only after it has been verified and merged into `main`.

## Web Root and Routing

The alwaysdata PHP site will point to the repository root because the current project structure places browser-accessible pages and API form handlers in different top-level directories.

A root `index.php` will provide a clean entry point and redirect visitors to the appropriate application page. Existing relative paths will be reviewed during Phase 1 so they work consistently both locally and on alwaysdata.

Apache access rules will deny direct HTTP access to sensitive or non-public locations, including:

- `backend/config`
- `backend/includes`
- `database`
- `docs`
- Git metadata and environment files

The `backend/api` directory must remain accessible because HTML forms submit to its PHP handlers.

## Database Configuration and Secrets

`backend/config/db.php` will remain ignored by Git and will exist separately in each environment. A tracked `backend/config/db.example.php` will document the required variables without containing credentials.

The local file will use the local XAMPP MySQL connection. The production file will use the database host, database name, username, and password issued by alwaysdata.

Application code will include the configuration file through filesystem paths and will never display database credentials or raw connection exceptions to public users.

## Initial Hosting Bootstrap

The hosting bootstrap will establish infrastructure before rebuilding Phase 1:

1. Create an alwaysdata Free account and accept the assigned public subdomain.
2. Select a PHP version compatible with the local PHP 8.2 environment.
3. Create the MySQL/MariaDB database and database user in the alwaysdata administration interface.
4. Enable SSH access and install an SSH key.
5. Clone the GitHub repository into the alwaysdata account.
6. Create the production `backend/config/db.php` outside Git tracking.
7. Import `database/schema.sql` into the production database.
8. Configure an alwaysdata PHP site whose document root is the repository root.
9. Enable HTTPS and redirect HTTP traffic to HTTPS.
10. Verify the public entry page, PHP execution, and database connectivity.

The initial public page may identify the project as being under development. It must not expose PHP configuration, credentials, stack traces, or other diagnostic details.

## Phase Deployment Workflow

Each phase will follow the same release sequence:

1. Implement and test the phase locally.
2. Run PHP syntax checks and any phase-specific functional checks.
3. Review the Git diff and commit the intended files.
4. Push the verified commit to `main`.
5. Connect to alwaysdata through SSH.
6. Run `git pull --ff-only origin main` in the deployed repository.
7. Apply an explicit database migration only when the schema changes.
8. Smoke-test the public URL and the phase's main user flow.

The deployment command must not use force operations or discard uncommitted server files. A failed fast-forward pull stops the deployment until its cause is understood.

## Database Changes and Recovery

The assignment's two-table schema is expected to support all planned phases. If later work requires a schema change, the change will be recorded explicitly and applied deliberately rather than embedded in an ordinary page request.

Before a destructive production schema change, a database export will be created. alwaysdata backups provide an additional recovery layer, but they do not replace application-level care.

Application rollback will use a new Git revert commit on `main`, followed by the normal deployment workflow. The server checkout will not be reset forcibly to an older commit.

## Error Handling and Production Safety

- Public pages show friendly errors and do not expose raw database exceptions.
- Production PHP error display is disabled while errors remain available in server logs.
- Authentication sessions use secure cookie settings under HTTPS.
- Successful login regenerates the session identifier.
- State-changing requests use POST and include CSRF protection.
- Authorization is enforced in backend handlers rather than only through hidden UI controls.
- Database access uses PDO prepared statements.

These safeguards will be implemented incrementally with the relevant application phase, beginning with authentication protections in Phase 1.

## Verification Strategy

Infrastructure verification is complete when:

- The public HTTPS URL loads successfully.
- PHP executes using the selected supported version.
- The application can connect to the alwaysdata database without exposing credentials.
- The `user` and `blogPost` tables exist.
- A new Git commit can be pushed, pulled over SSH, and observed at the public URL.

Every later phase requires both local verification and a deployed smoke test. Phase 1 is accepted only after registration, login, protected-session behavior, logout, validation errors, and invalid-credential handling work through the hosted URL.

## Future Automation

After several successful manual deployments, a GitHub Actions workflow may deploy verified changes to `main` over SSH. The workflow would store the deployment key and host details in GitHub Actions secrets and run the same fast-forward deployment logic. This is optional and is not required for the assignment.

## Success Criteria

The deployment foundation is successful when the alwaysdata site is publicly accessible over HTTPS, the database is connected securely, secrets are absent from Git, and a documented Git-based process can publish each verified phase without manual file uploads.
