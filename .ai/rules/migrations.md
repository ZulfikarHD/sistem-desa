---
paths:
  - 'database/migrations/**'
---

# Migrations

## Drop unique after replacement when MySQL FK reuses it
MySQL/InnoDB can serve a foreign key from the leftmost column of a composite unique and will not create a separate FK index. Never dropUnique() that covering index in the same step as adding its replacement. Create the new unique (or a dedicated index on the FK column) in one Schema::table() call, then drop the old unique in a second call.

## Fold additive schema into create tables before production
The app is not in production yet. Put new columns, indexes, and FKs into the original create_* migration instead of an add_* alter. Rename the create file timestamp if a new FK parent table must exist first. Explicitly name MySQL indexes that would exceed 64 characters.
