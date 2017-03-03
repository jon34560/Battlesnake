<?php

/*
  * Board - Board Logic functions
*/

namespace BattleSnake\Logic\Board;

class Board
{
    public static function loadBoardState($data) {
        $state = array();
        $state['board_width'] = $data['width'];
        $state['board_height'] = $data['height'];

        $state['ticks'] = $data['turn'];
        $state['s'] = '';

        $snakes = array();
        for ($s = 0; $s < count( $data['snakes'] ); $s++) {
            // id, name status message taunt age health, coords []  kills  food gold
            $tails = array();
            for ($p = 0; $p < count($data['snakes'][$s]['coords']); $p++) {
                $x = $data['snakes'][$s]['coords'][$p][0];
                $y = $data['snakes'][$s]['coords'][$p][1];

                if ($p == 0) {
                    $snakes[$s]['x'] = $x;
                    $snakes[$s]['y'] = $y;
                } else {
                    $tails[ ($p - 1) ]['x'] = $x;
                    $tails[ ($p - 1) ]['y'] = $y;
                }
            }
            $snakes[$s]['health'] = $data['snakes'][$s]['health_points'];
            $snakes[$s]['alive'] = ( $data['snakes'][$s]['health_points'] > 0 ? true : false );
            $snakes[$s]['tails'] = $tails;

            // Identify my snake
            if ($data['snakes'][$s]['id'] == $data['you'] ) {
                $state['s'] = $s;
            }
        }
        $state['snakes'] = $snakes;

        $foods = array();
        for ($f = 0; $f < count($data['food']); $f++) {
            $x = $data['food'][$f][0];
            $y = $data['food'][$f][1];
            $foods[$f]['x'] = $x;
            $foods[$f]['y'] = $y;
        }
        $state['foods'] = $foods;

        return (array)$state;
    }

    public static function isSpaceOnBoard($state, $x, $y, $decission_matrix) {
        $key = $x . "_" . $y . "b";
        $result = $decission_matrix->getTickCacheValue($key);

        if ($result == 't') {
            return true;
        } else if ($result == 'f') {
            return false;
        }

        if ($x < 0) {
            $decission_matrix->setTickCacheValue($key, 'f'); //false;
            return false;
        }
        if ($y < 0) {
            $decission_matrix->setTickCacheValue($key, 'f'); //false;
            return false;
        }
        if ($x > ($state['board_width'] - 1)) {
            $decission_matrix->setTickCacheValue($key, 'f'); //false;
            return false;
        }
        if ($y > ($state['board_height'] - 1)) {
            $decission_matrix->setTickCacheValue($key, 'f'); //false;
            return false;
        }
        $decission_matrix->setTickCacheValue($key, 't'); //true;
        return true;
    }

    public static function isSpaceEmpty($state, $x, $y, $decission_matrix) {
        $my_snake = $state['snakes'][$state['s']];
        $key = $x . "_" . $y . "e";
        $result = $decission_matrix->getTickCacheValue($key);

        if ($result == 't') {
            return true;
        } else if ($result == 'f') {
            return false;
        }

        if (!self::isSpaceOnBoard($state, $x, $y, $decission_matrix)) {
            $decission_matrix->setTickCacheValue($key, 'f'); //false;
            return false;
        }

        for ($s = 0; $s < count($state['snakes']); $s++ ) {
            if ($my_snake['x'] == $x && $my_snake['y'] == $y && $my_snake['alive'] == true) {
                $decission_matrix->setTickCacheValue($key, 'f'); //false;
                return false;
            }

            for ($t = 0; $t < count($my_snake['tails']); $t++) {
                if ($x == $my_snake['tails'][$t]['x'] && $y == $my_snake['tails'][$t]['y'] && $my_snake['alive'] == true) {
                    $decission_matrix->setTickCacheValue($key, 'f'); //false;
                    return false;
                }
            }
        }
        $decission_matrix->setTickCacheValue($key, 't'); //true;
        return true;
    }
}