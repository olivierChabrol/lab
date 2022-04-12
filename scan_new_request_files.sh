#!/bin/bash
sudo -u www-data php /var/www/nextcloud/occ files:scan -q --path="/chabrol/files/requests/2022" chabrol > /dev/null 2>&1