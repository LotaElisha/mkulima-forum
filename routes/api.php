<?php

/*
| The mobile app and the old admin both call /api/*. Those routes live in
| legacy.php and keep their existing JSON. New clients use /api/v1, defined
| in api_v1.php. Laravel prefixes this file with /api.
*/
require __DIR__.'/legacy.php';
require __DIR__.'/api_v1.php';
