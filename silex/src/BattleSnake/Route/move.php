<?php

namespace BattleSnake\Route;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use BattleSnake\Logic\Board\Board;
use BattleSnake\Logic\Collision\Collision;
use BattleSnake\Logic\FreeSpace\FreeSpace;
use BattleSnake\Logic\Snake\DecisionMatrix;
use BattleSnake\Logic\Food\Food;
use BattleSnake\Logic\Snake\Snake;
use BattleSnake\Util\Util;

$log = $app['monolog'];
$decision_matix = new DecisionMatrix($log);
$request_data = json_decode($request->getContent(), true);
$state = Board::loadBoardState($request_data);

Collision::wallCollisionDetection($state, $decision_matix, $log);
Collision::selfCollisionDetection($state, $decision_matix, $log);
Collision::snakeCollisionDetection($state, $decision_matix, $log);

Food::linearFoodSearch($state, $decision_matix);
//Food::angleFoodSearch($state, $decision_matix);
Food::pathFoodSearch($state, $decision_matix, $log);

FreeSpace::linearFreeSpaceDetection($state, $decision_matix, $log);
FreeSpace::floodFillDetection($state, $decision_matix, $log);
FreeSpace::weightedFloodFillDetection($state, $decision_matix, $log);
FreeSpace::preferWallGap($state, $decision_matix, $log);
FreeSpace::preferSelfGap($state, $decision_matix, $log);


//$taunts = array('MATT DAMON', 'x', 'The greatest that ever was.');
//$taunt = $taunts[rand(0, sizeof($taunts)-1)];

//if( $state['ticks'] % 15 ){
	$return_data = array( 'move' => $decision_matix->decideMoveDirection(), 'taunt' => 'MATT DAMON' );
//} else {
//	$return_data = array( 'move' => $decision_matix->decideMoveDirection() );
//}

return $app->json($return_data, 200);
