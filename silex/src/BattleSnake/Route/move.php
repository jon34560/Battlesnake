<?php

namespace BattleSnake\Route;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use BattleSnake\Logic\Board\Board;
use BattleSnake\Logic\Collision\Collision;
use BattleSnake\Logic\Snake\DecisionMatrix;
use BattleSnake\Logic\Snake\Snake;
use BattleSnake\Util\Util;

$decision_matix = new DecisionMatrix();

$request_data = json_decode($request->getContent(), true);
$state = Board::loadBoardState($request_data);

Collision::wallCollisionDetection($state, $decision_matix);
Collision::selfCollisionDetection($state, $decision_matix);
Collision::snakeCollisionDetection($state, $decision_matix);

error_log(print_r('Decision', true), 0);
error_log(print_r($decision_matix->firstValidDirection(), true), 0);

$return_data = array( 'move' => $decision_matix->firstValidDirection(), 'taunt' => 'Everyone wins!' );
error_log(print_r('Done', true), 0);
return $app->json($return_data, 200);