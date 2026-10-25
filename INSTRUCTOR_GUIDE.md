# ICSS WAPT CTF — Instructor Quick Guide

## Before class
1. Review `README.md` and `.env.example`.
2. Change database passwords in `.env`.
3. Decide whether demo flags should be visible. In this guided build, each module flag is intentionally visible in a collapsible instructor/demo section.
4. Start the app and run `./verify.sh`.
5. For LAN use, set `BIND_ADDRESS=0.0.0.0` and restrict TCP/8080 to the classroom subnet with the host firewall.
6. Test from a student device before the session.

## Suggested 90-minute class
- 10 min: scope, authorization, and rules of engagement.
- 10 min: reconnaissance and `robots.txt`.
- 35 min: ten module walkthroughs in pairs.
- 20 min: students write findings with impact and remediation.
- 10 min: compare remediation choices.
- 5 min: collect reports and reset scores if needed.

## Assessment rubric (100 points)
- Reconnaissance and clue interpretation: 10
- Access-control reasoning: 10
- Configuration and cryptography reasoning: 15
- Injection prevention concepts: 15
- Secure design and authentication: 15
- Integrity, logging, and exception handling: 15
- Evidence quality, impact explanation, and remediation: 20

## Multi-student warning
The included account is a shared demonstration account. Students using it will share a score. Before a graded session, add instructor-managed individual accounts and hide/gate demo flags. Never distribute the database root password.

## Reset
- Clear scores only: delete rows from `submissions` using an authenticated DB client.
- Full reset: `docker compose down -v` then `docker compose up --build -d`. This permanently deletes the database volume and all scores.


### Extended lab scoring
Every extended lab is worth exactly **10 points**. When a student demonstrates the lab condition, the page reveals the flag and provides a flag-submission form. The student must submit the flag; points are awarded once per user per lab. Passing threshold remains 60 points.
