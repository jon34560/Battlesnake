<?php

namespace BattleSnake\Route;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$request_data = json_decode($request->getContent(), true);

error_log(print_r($request_data, true), 0);

$return_data = array(
  'color' => '#2222FF',
  'head_url' => 'https://cdn-images-1.medium.com/max/400/1*NOGCOyyj7wpAzkVGaLZqeA.gif',
  'name' => 'Sunglasses_And_Advil',
  'taunt' => 'Everyone wins!'
);

return $app->json($return_data, 200);