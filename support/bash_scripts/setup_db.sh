#!/usr/bin/env bash
echo "This script will reinstall db from the very beginning (drop, create, migrate)"
echo "Starting now..."
php support/migration_tool/drop_db.php
php support/migration_tool/create_db.php
php support/migration_tool/migrate.php
