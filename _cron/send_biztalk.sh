#!/bin/sh

SET=$(seq 0 29)
for i in $SET
do
    php /var/www/_cron/send_biztalk.php
    sleep 2;
done