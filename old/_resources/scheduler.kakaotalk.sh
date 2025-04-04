#!/bin/sh

SET=$(seq 0 29)
for i in $SET
do
    php /var/www/_resources/scheduler.kakaotalk.php
    sleep 2;
done
