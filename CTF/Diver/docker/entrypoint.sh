#!/bin/bash
set -e

DB=/data/diver.db

# Ensure /data is writable by www-data
chown www-data:www-data /data

# Initialise database if it doesn't exist
if [ ! -f "$DB" ]; then
    echo "[diver] Initialising database..."
    su -s /bin/sh www-data -c "php /var/www/html/seed.php"
    echo "[diver] Database ready."
fi

# Write the flag outside the web root
if [ ! -f /flag.txt ]; then
    echo "DIVER{d33p_w4t3r_f1l3_upl04d_ftw}" > /flag.txt
    chmod 444 /flag.txt
fi

exec apache2-foreground
