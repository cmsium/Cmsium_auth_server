#!/usr/bin/env bash
echo "This script will drop current db and create it again"
echo "Starting now..."
php ../migration_tool/drop_db.php
php ../migration_tool/create_db.php