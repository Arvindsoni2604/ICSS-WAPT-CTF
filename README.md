# ICSS Market + WAPT CTF — Complete Ubuntu Setup

## Fix for the missing signup page
This release includes `src/database.php`, required by `src/bootstrap.php`. If that file is missing, PHP throws a fatal error before signup or dashboard pages can render.

## Install
1. Extract the whole archive to `~/Downloads/icss-market-wapt-ctf-complete`.
2. Run `ls -la` and confirm `Dockerfile`, `docker-compose.yml`, `.env.example`, `database/`, `public/`, and `src/` exist.
3. Run:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Set different strong values for `DB_PASSWORD` and `DB_ROOT_PASSWORD`. Keep `BIND_ADDRESS=127.0.0.1` for local testing.
4. Start:
   ```bash
   sudo docker compose up --build -d
   sudo docker compose ps
   sudo docker compose logs --tail=80 web
   ```
5. Open `http://127.0.0.1:8080/signup.php`.

## If port 8080 is already occupied
Inspect containers with `sudo docker ps`. Stop the old project's containers from its own project directory with `sudo docker compose down`. Do not use `-v` unless intentionally deleting that project's database volume.

## Pages
- Home: `/`
- Signup: `/signup.php`
- Login: `/login.php`
- Shop: `/shop.php`
- Cart: `/cart.php`
- Dashboard: `/dashboard.php`
- Challenges: `/challenges.php`
- Scoreboard: `/scoreboard.php`

## Included
ICSS-branded home, signup and login with password hashing, CSRF tokens, individual student accounts, fictional shop, session cart and simulated checkout, student dashboard with a 60/100 passing threshold, per-account demo wallet (₹100 initial student balance; admin unlimited), flags and scoreboard, and guided OWASP Top 10:2021 practical scenarios.

Signup records are stored in the local database. They are not automatically emailed. The instructor page requires an account with role `admin`; this starter does not automatically create one.

## Scope note
This is a guided training scaffold and does not yet implement live exploitable endpoints for all ten OWASP categories. Keep the lab private and use fictional records. Do not expose the service publicly.


## Instructor account
The app provisions classroom instructor accounts on first request if they do not exist: `ICSS94` / `indian@123` and `Arvind` / `Anime@0405`. Sign in at `/login.php`, then visit `/admin.php` to review student registrations, roles, wallet balances, points, and flag submissions. Passwords are stored as hashes and never displayed in the admin page. These are classroom-only credentials: keep the app isolated, change them before any real deployment, and do not reuse them elsewhere. If either username already exists as a non-admin account, the provisioning routine promotes it to admin and resets its password to the classroom value.


## OWASP Top 10:2021 practical range

The challenge catalog maps to the official OWASP Top 10:2021 categories. Each challenge opens `/lab.php?id=...`, a controlled exercise endpoint with fictional data. The injection exercise uses an in-memory parser and does not execute SQL; the SSRF exercise resolves only predefined simulator URLs and makes no outbound network requests. Flags are not printed on the challenge page. The lab reveals a flag only when the submitted test input or repeated test demonstrates that challenge’s defined vulnerable behavior; ordinary interaction does not automatically reveal it. The A03 parser and A10 SSRF simulator are intentionally bounded to fictional in-memory data and predefined simulator URLs, respectively.

**Isolation is mandatory:** bind to localhost for single-machine use. For a classroom LAN, bind only to a private interface and restrict the host firewall to the classroom subnet. Never expose this intentionally vulnerable app to the public internet.


## Shop price-manipulation exercise
The demo cart now includes a client-submitted unit-price field for each fictional product. Students can edit that value in the browser or intercept the checkout request in Burp Suite and change `unit_price[PRODUCT_ID]`. The intentionally vulnerable checkout trusts the submitted unit price rather than recalculating it from the product record. When a lower price is accepted and the simulated order is saved, the A04 challenge flag is shown in the confirmation message. No real payment gateway is connected. Run this only in an isolated classroom lab; do not deploy it to the public internet.

Each challenge lab also uses application-style chrome and a feature-specific workspace (orders, profile viewer, storefront search, checkout, diagnostics, component inventory, account recovery, imports, security operations, and URL preview) so learners test an application feature rather than a generic flag form.

## Burp Suite classroom testing

Students can route their browser through Burp Suite and use Proxy → Intercept / HTTP history to inspect requests and responses. The lab forms submit ordinary HTTP requests, so students can replay and modify requests in Repeater. Keep Burp scoped to the local ICSS lab host and do not test unrelated systems.

The customer-facing lab pages intentionally avoid step-by-step vulnerability hints. The challenge condition is evaluated server-side; a flag is returned only when the corresponding test condition is met. In the shop, the displayed price is normal storefront text while the checkout submits the price as a hidden form value, allowing students to inspect and modify that request with Burp Suite.


### Wallet and pass criteria
- New student accounts start with ₹100.00 in their demo wallet.
- Successful simulated checkout deducts the accepted order total from the student's wallet. Insufficient funds block checkout.
- Instructor/admin accounts display an unlimited wallet and are not charged.
- The passing threshold is 60 points out of 100. The dashboard displays PASS at 60 or more points.
- These are fictional classroom credits only; no real payment processing is connected.
- If you are upgrading an existing Docker database volume, the initial SQL file will not add the new wallet column automatically. For a disposable lab, run `sudo docker compose down -v` and then `sudo docker compose up --build -d` to recreate the database. This deletes existing lab accounts, orders and scores.


## Existing database wallet migration

If you upgraded from an earlier classroom build, the existing MySQL volume may not rerun `database/init.sql`. The application now checks for the `wallet_balance` column at startup and adds it with a default balance of ₹100 when missing. This preserves existing student accounts and orders. Back up the database before upgrades. Detailed database errors are written to the container log; they are not shown to students.


## Extended vulnerability library

Open **Challenges → Extended vulnerability library** to browse the requested categories and subtopics (A01–A10 labels supplied by the course owner, plus Client-Side, File and XML Security). These are isolated, fictional training simulations intended for browser inspection and Burp Suite request/response analysis. Dangerous behavior is simulated: the lab does not run operating-system commands, contact external SSRF targets, include real remote files, expand hostile XML entities, or exhaust server resources. Flags are inserted into the flag registry only after the corresponding lab condition is detected.

**Taxonomy note:** the labels in this extended library follow the user's supplied course outline. They should not be presented as an official OWASP Top 10:2025 taxonomy without checking the official publication; the core challenge set remains labelled OWASP Top 10:2021.


## Extended vulnerability library and points

The extended library contains additional classroom exercises across access control, configuration, supply chain, cryptography, injection, design/business logic, authentication, integrity, logging, exceptional conditions, client-side security, file handling, and XML. These are supplemental training groups, not an official “OWASP Top 21” list. The canonical core remains the OWASP Top 10:2021.

Every core and extended challenge is worth exactly 10 points. These are classroom scoring values, not CVSS scores. Students pass at 60 cumulative points; because the expanded library has more than ten exercises, total points can exceed 100. A flag is only registered when the lab's configured condition is met. Some exercises are intentionally inert simulations and should not be represented as real exploit primitives.


### Extended lab scoring
Every extended lab is worth exactly **10 points**. When a student demonstrates the lab condition, the page reveals the flag and provides a flag-submission form. The student must submit the flag; points are awarded once per user per lab. Passing threshold remains 60 points.


### Instructor account
The lab provisions an `Arvind` administrator account (email `arvind.admin@icss.local`) with the requested classroom password `Anime@0405`, plus the legacy `ICSS94` administrator account. Passwords are stored as password hashes. This account has access to the instructor dashboard, registrations, scores, wallet status, and submission history. Change these classroom credentials before any network exposure and keep the lab private.

### Scoring and dashboard submissions
Every core and extended lab is worth 10 points. Students can submit any discovered core or extended lab flag from their dashboard; a submission is accepted only if the flag matches the selected lab. The pass threshold remains 60 points.


### Price manipulation flag submission
The shop checkout shows a discovered flag and a dedicated flag submission form only after a lower client-submitted unit price is accepted and the simulated order is saved. Students must submit that flag to receive the 10 points. The exercise is fictional and must remain isolated from the public internet.
