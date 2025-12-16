# Accounts Payable Clerk — Role Summary (WITMS)

Role: Accounts Payable Clerk

Overview
-------

The Accounts Payable Clerk manages supplier-related financial transactions, ensuring invoices are verified, matched against procurement records and warehouse receipts, and processed for payment following approval and company terms.

Core Permissions (configured in `app/Config/Roles.php`)
- invoices.create, invoices.view, invoices.update, invoices.match, invoices.flag
- payments.schedule, payments.process, payments.view, payments.update
- inventory.view_relevant (view-only for relevant inventory records)
- reconciliation.request
- reports.ap, audit.trail.view

Restrictions
- No access to warehouse operational controls
- No access to accounts receivable workflows
- No system-admin privileges

Typical Workflow
1. Receive supplier invoice (system or uploaded)
2. Match invoice to purchase order + delivery receipt + warehouse stock entry
3. Flag discrepancies and coordinate resolution with Procurement / Warehouse
4. Upon approval, schedule and process payment, and update payment status
5. Provide reports and audit trail exports when requested by auditors or top management

Integration Notes
- Ensure `users` seeded with role `accounts_payable_clerk` (see `app/Database/Seeds/UsersSeeder.php`).
- Use `Config\Roles` to check permissions in controllers or authorization middleware.
