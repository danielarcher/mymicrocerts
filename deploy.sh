#!/usr/bin/env bash
rm -rf mycerts.tar.gz
tar --exclude='vendor' -zcvf mycerts.tar.gz *
scp -rp mycerts.tar.gz root@104.131.120.6:/var/www/html
ssh root@104.131.120.6 'cd /var/www/html; tar -xzf mycerts.tar.gz; composer install --no-plugins; lumen/artisan migrate --force; service nginx restart'
