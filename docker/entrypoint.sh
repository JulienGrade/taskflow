#!/bin/sh
set -eu
php bin/seed.php
exec php -S 0.0.0.0:8000 -t public public/router.php
