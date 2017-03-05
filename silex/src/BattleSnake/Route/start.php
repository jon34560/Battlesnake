<?php

namespace BattleSnake\Route;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$request_data = json_decode($request->getContent(), true);

error_log(print_r($request_data, true), 0);

$return_data = array(
  'color' => '#664400',
  'head_url' => 'http://35.160.151.56/images/Matt.png',
  'name' => 'Matt Damon',
  'taunt' => 'Matt DAMON'
);

return $app->json($return_data, 200);
