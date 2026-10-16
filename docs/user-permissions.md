# User permissions

Open User Management → Permission for a user, select the direct permissions, and
click Update. Clearing every checkbox removes the user's direct permissions.
Role permissions remain active and are labeled on the form. Super administrators
retain full access through the existing super-admin bypass.

Viewing the user list requires `user-view`. Opening or saving the Permission form
requires `user-update`, matching the existing user-edit permission model. Ordinary
users cannot promote an account to super administrator or edit a super administrator.

## Enforcement

`CheckAdminPermission` applies the route/action mappings in `AdminPermissions` to
authenticated admin routes. Shared save endpoints require Create when no record ID
is present and Edit when updating a record. Status/password/permission changes use
Edit. Restore uses Edit and permanent deletion uses Delete, except Staff and Staff
Expense, which have dedicated Restore and Destroy permissions.

Existing report view alternatives remain supported. Where the catalog defines an
Excel permission, exports additionally require it; order exports retain Order View access. Shared lookups accept permissions from the forms
or filters that consume them; for example Product Create can load categories
without granting access to Category Management. Sidebar, menu search, and shared
listing controls use the same permissions. Business calculations are unchanged.

The permission catalog is seeded by name and guard without truncating tables.
Existing permission IDs, user assignments, role assignments, and historical entries
are preserved when it is rerun. Missing active CRUD actions are configured in
`config/admin_permissions.php`.

## Deployment

Run the catalog migration and clear stale routes when deploying these changes:

```sh
php artisan migrate --path=database/migrations/2026_10_15_000000_complete_user_permission_catalog.php
php artisan route:clear
php artisan view:clear
```

Rebuild route/config caches afterward if the deployment normally uses them. The
migration intentionally retains assigned permissions on rollback.

## Verification

```sh
vendor/bin/phpunit --filter UserPermissionTest
```

These tests use isolated SQLite databases and cover user creation, assignment,
reload, replacement, revocation, inherited roles, validation failures, transaction
rollback, unauthorized requests, create/edit separation, navigation controls,
shared lookups, and repeatable catalog updates.

Browser verification also covered English/Khmer initial selections, individual and
module checkboxes, Select All, partial-selection indicators, form submission data,
search, expand/collapse, and two independently open permission forms.
