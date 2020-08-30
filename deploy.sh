#!/usr/bin/env bash

rm -rf mycerts.tar.gz
echo -ne "1. creating package tar..."
tar --exclude='vendor' -zcvf mycerts.tar.gz * 1>/dev/null
echo -e "done"
# If we want to clean up before release (it don"t refresh database)
# ssh root@104.131.120.6 'cd /var/www/html; rm -rf *'


echo -ne "2. sending package to server..."
scp -rp mycerts.tar.gz root@104.131.120.6:/var/www/html 1>/dev/null
echo -e "done"

echo -e "3. start deployment";
ssh root@104.131.120.6 '

cd /var/www/html;

echo -ne "4. untar...";
tar -xzf mycerts.tar.gz;
echo -e "done";

echo -ne "5. composer install started...";
composer install --quiet --no-plugins --no-scripts --no-suggest --no-progress --no-dev;
echo -e "done";

echo -ne "6. checking permissions...";
chmod 755 -R /var/www/html/lumen/storage;chown www-data: -R /var/www/html/lumen/storage;
echo -e "done";

echo -ne "7. migration started...";
lumen/artisan migrate --force --quiet;
echo -e "done";

echo -ne "8. restarting nginx..."
service nginx restart'
echo -e "done"
rm -rf mycerts.tar.gz
echo -e "Finished"
