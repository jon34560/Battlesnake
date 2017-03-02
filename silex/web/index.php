<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';
//require_once __DIR__.'/../src/BattleSnake/Controllers/StartControllerProvider.php';

$app = require __DIR__.'/../src/BattleSnake/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/BattleSnake/controllers.php';

$app->run();
