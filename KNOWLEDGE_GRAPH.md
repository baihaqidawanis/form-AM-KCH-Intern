# Form AM Knowledge Graph

## Runtime map

```text
index.php
  -> Router
  -> BaseController / SecureController
  -> BaseMachineController
  -> <Machine>Controller
  -> app/views/partials/<machine>/*
  -> PostgreSQL tb_mesin_<machine> + kendala_<machine>
```

## Core layers

| Area | Responsibility | Key files |
|---|---|---|
| Bootstrap | Config, routing, controller loading | `index.php`, `config.php`, `system/Router.php` |
| Security | Login, role access, CSRF, session | `system/SecureController.php`, `libs/ACL.php`, `libs/Csrf.php`, `app/controllers/IndexController.php` |
| AM engine | CRUD, operational date, approval, reports, dynamic parts | `system/BaseMachineController.php` |
| Machine modules | Machine key, label, legacy part fallback | `app/controllers/*Controller.php`, `app/views/partials/<machine>/` |
| Master data | Dynamic part definition, scheduling, takeout | `app/controllers/Master_partController.php`, `master_part` |
| Reports | Daily aggregation and period check sheet | `app/views/partials/machine_daily_report.php`, `app/views/partials/machine_period_report.php` |
| PDF | HTML report body rendered by Dompdf | `system/BaseView.php` |

## Data graph

```text
master_part
  machine_key + field_name + shift_schedule + taken_out_at
      -> BaseMachineController partsForAdd()
      -> tb_mesin_<machine> form columns
      -> kendala_<machine> abnormality records
      -> daily report / period PDF

users
  -> login / account_status
  -> password reset -> pending_activation -> UsersController activation

tb_mesin_<machine>
  created_at -> operational_date
  approval + user_approve
  shift (only meaningful when Master Part enables Shift 2/3)
```

## Shift rule

- Default `master_part.shift_schedule` is `1`.
- If a machine has an active part using Shift `2` or `3`, its Add form enters shift mode.
- Shift mode filters parts by selected shift and permits one submission per machine, operational date, and shift.
- Non-shift mode permits one submission per machine and operational date.
- Illapak 1-2 and Illapak 3-12 are existing shift modules; generic modules use the same base behavior once configured.

## Reporting rule

- Operational day: 06:45 until 05:45 next day.
- Period report is the official PDF/Excel check sheet path.
- Approval stamp is `APPROVED` only if every record in the selected period is approved; otherwise `MENUNGGU APPROVAL`.
- Do not claim FUPD integration unless an actual FUPD API/e-sign evidence is implemented.

## Change safety

- Prefer `master_part` takeout over deleting a part: historical reports must remain auditable.
- A takeout must be evaluated against the record creation timestamp, not only operational date.
- `vendor/` is intentionally committed for deployment; do not remove production dependencies blindly.
- `scratch/legacy-artifacts/` holds unreferenced local artifacts moved from repository root; it is recoverable storage, not application runtime.

## Verification

- PHP syntax: `php -l <file>`.
- Feature tests: `php vendor/bin/phpunit --testdox tests/Feature/...`.
- Existing duplicate submissions can cause one-per-day/shift tests to fail; inspect the local database before treating this as a code regression.
- Check layout/PDF in a running browser; syntax tests do not validate Dompdf rendering.