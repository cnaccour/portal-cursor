#!/usr/bin/env bash
set -euo pipefail

: "${DB_HOST?Set DB_HOST}"
: "${DB_USER?Set DB_USER}"
: "${DB_PASS?Set DB_PASS}"
: "${DB_NAME?Set DB_NAME}"

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$(dirname "$0")/../db/schema.sql"
echo "Schema applied successfully to $DB_NAME on $DB_HOST"
