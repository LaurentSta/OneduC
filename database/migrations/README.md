# Migrations

Historical migrations were pruned after the schema baseline was generated.

The current database baseline lives in:

- `database/schema/mysql-schema.sql`

The baseline was refreshed after the guarded removal of the legacy
`contacts` and `learning_objectives` tables. Keep the post-baseline drop
migrations until every existing environment has applied them; fresh installs
are already covered by the updated schema dump.

Create new migrations in this directory with `php artisan make:migration`.
