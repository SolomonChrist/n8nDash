# n8nDash v2 — Touch‑first dashboards for n8n automations

**n8nDash** is a PHP/MySQL + Bootstrap app that lets you build beautiful, client‑facing dashboards to **trigger** n8n workflows and **display** real‑time results with live progress.

- 🔘 One‑Tap Automations (App widgets)
- 📊 Live Data Panels (Data widgets)
- 🔄 Main button to refresh selected panels
- 🛰️ Live progress via signed callbacks (SSE + polling fallback)
- 🧩 JSON import/export (coming) and prebuilt examples
- 🛡️ HMAC signatures to secure status updates

## Quick Start (shared Apache hosting)
1. **Clone / Upload** this repo to your host.
2. Create a **MySQL** database + user.
3. Copy `config/config.example.php` → `config/config.php` and set DB creds.
4. Set a long random `status_webhook_secret` in `config.php`.
5. Visit `/install/install.php` once to create tables + default admin (`admin`/`password`). Change password immediately.
6. Log in at `/public/`.
7. (Optional) Run the **example seeds**:
   ```sql
   SOURCE examples/seeds.sql;
   ```
8. Import the **demo n8n workflows** from `demos/n8n/*.json` into your n8n instance.
9. Edit each widget’s `config_json.webhook_url` to match your n8n Webhook URL.
10. Open a dashboard → press **Main** for data → **Run** on app widgets.

## n8n Callback Contract
When n8nDash calls your n8n Webhook, it includes:
```json
{ "job_id": 123, "callback": { "status": "https://YOUR_DASH/api/jobs/update", "complete": "https://YOUR_DASH/api/jobs/complete" } }
```
Post progress to `callback.status` and final result to `callback.complete`. Include the **same** `X-N8N-Signature` header (HMAC‑SHA256 over body) you received, so n8nDash trusts the update.

## Example Dashboards Included
- **Social Content Studio** — Blog Generator, IG Caption Maker, YouTube Subs KPI, Headlines list
- **Executive Metrics Wall** — MRR KPI, Pipeline chart, Web Traffic, CSAT, Top Support Issues
- **AI Intake & Ticketing Desk** — New Request app + My Tickets table

## Concurrency
By default, each user can run up to **10** widgets concurrently. Modify `n8n.max_concurrent_views` in `config.php`.

## Charts
Chart.js via CDN with local fallback (`public/assets/vendor/chart.fallback.js`).

## Security
- Password hashing with `password_hash()`
- CSRF for browser POSTs
- HMAC verification on job updates

## Roadmap
- Import/export dashboards JSON
- Editor side panel for full widget config
- Schedules & refresh intervals

## License
MIT — see `LICENSE`.
