# ICSS Market + WAPT CTF — Complete Ubuntu Setup

## Important: use this complete folder
The archive contains `Dockerfile`, `docker-compose.yml`, `.env.example`, `database/`, `public/`, and `src/`. Do not copy only the `public` or `src` folders.

## 1. Extract
```bash
cd ~/Downloads
unzip icss-market-wapt-ctf-complete.zip
cd icss-market-wapt-ctf-complete
ls -la
```
You must see `Dockerfile` and `docker-compose.yml` in this directory.

## 2. Create environment file
```bash
cp .env.example .env
nano .env
```
Replace the two database password values with different, long random passwords. Keep `BIND_ADDRESS=127.0.0.1` for initial local testing. Save using Ctrl+O, Enter, Ctrl+X.

## 3. Start
```bash
sudo docker compose up --build -d
sudo docker compose ps
sudo docker compose logs --tail=80 web
```
Wait for the database health status to become healthy.

## 4. Open pages
- Home: `http://127.0.0.1:8080`
- Signup: `http://127.0.0.1:8080/signup.php`
- Login: `http://127.0.0.1:8080/login.php`
- Shop: `http://127.0.0.1:8080/shop.php`
- Cart: `http://127.0.0.1:8080/cart.php`
- Student dashboard: `http://127.0.0.1:8080/dashboard.php`

Create a student account through signup. The new version uses individual student accounts; the earlier demo username is not automatically seeded in this rebuild.

## 5. If port 8080 is already occupied
Stop the older project first from its own directory:
```bash
sudo docker compose down
```
If needed, inspect containers:
```bash
sudo docker ps
```
Avoid `docker compose down -v` unless you intend to permanently delete data in that project's volume.

## 6. Access from a classroom LAN
Only after local testing, set `BIND_ADDRESS=0.0.0.0` in `.env`, recreate the web service, and restrict inbound TCP/8080 to the trusted classroom subnet with the Ubuntu firewall. Do not port-forward to the public internet.

## 7. Reset
Stop while keeping scores:
```bash
sudo docker compose down
```
Full reset (permanently deletes scores and database data):
```bash
sudo docker compose down -v
sudo docker compose up --build -d
```

## Scope
This is a storefront, signup/login, simulated cart/checkout, student dashboard, scoreboard, and guided OWASP Top 10:2025 training scaffold. It does not yet contain live exploit implementations for all ten vulnerabilities. The instructor registration page is role-restricted, but this starter does not automatically create an instructor account. Do not collect unnecessary student data or deploy this training application publicly.


## Required source file check
Confirm `src/database.php` exists. It creates the PDO connection used by signup, login, shop, and dashboard pages.


### Extended lab scoring
Every extended lab is worth exactly **10 points**. When a student demonstrates the lab condition, the page reveals the flag and provides a flag-submission form. The student must submit the flag; points are awarded once per user per lab. Passing threshold remains 60 points.
