#!/bin/sh
set -e

cat > /etc/msmtprc <<EOF
defaults
auth on
tls on
tls_starttls on

account gmail
host smtp.gmail.com
port 587

from $(cat /run/secrets/smtp_mail)
user $(cat /run/secrets/smtp_mail)
password $(cat /run/secrets/smtp_password)

account default : gmail
EOF

chown www-data:www-data /etc/msmtprc
chmod 600 /etc/msmtprc

mkdir -p /var/www/html/public/uploads/avatars /var/www/html/public/uploads/photos
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 775 /var/www/html/public/uploads

exec "$@"