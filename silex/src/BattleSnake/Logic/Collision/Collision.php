<?php

/*
  * Collision - Collision Logic functions
*/

namespace BattleSnake\Logic\Collision;

class Collision
{
    public static function wallCollisionDetection($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];
        $x = $my_snake['x'];
        $y = $my_snake['y'];

        if ($x - 1 < 0) {
            $decision_matix->disallowDirection('left');
        }
        if ($y - 1 < 0) {
            $decision_matix->disallowDirection('up');
        }
        if ($x + 1 >= $state['board_width']) {
            $decision_matix->disallowDirection('right');
        }
        if ($y + 1 >= $state['board_height']) {
            $decision_matix->disallowDirection('down');
        }
    }

    public static function selfCollisionDetection($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];
        $my_snake_tails = $my_snake['tails'];
        $x = $my_snake['x'];
        $y = $my_snake['y'];

        for ( $t = 0; $t < count($my_snake_tails); $t++ ) {
            if ($my_snake_tails[$t]['x'] == $x - 1 && $my_snake_tails[$t]['y'] == $y) {
                $decision_matix->disallowDirection('left');
            }

            if ($my_snake_tails[$t]['x'] == $x + 1 && $my_snake_tails[$t]['y'] == $y) {
                $decision_matix->disallowDirection('right');
            }

            if ($my_snake_tails[$t]['x'] == $x  && $my_snake_tails[$t]['y'] == $y - 1) {
                $decision_matix->disallowDirection('up');
            }

            if ($my_snake_tails[$t]['x'] == $x && $my_snake_tails[$t]['y'] == $y + 1) {
                $decision_matix->disallowDirection('down');
            }
        }
    }

    public static function snakeCollisionDetection($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];
        $snakes = $state['snakes'];

        for ($c = 0;  $c < count($snakes); $c++) {
            if ($c != $state['s'] && $snakes[$c]['alive'] == true) {
                error_log('HEllo!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', 0);
                // Avoid collision with another snake's' head
                if ($snakes[$c]['x'] == $my_snake['x'] - 1 && $snakes[$c]['y'] == $my_snake['y']) {
                    $decision_matix->disallowDirection('left');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] + 1 && $snakes[$c]['y'] == $my_snake['y']) {
                    $decision_matix->disallowDirection('right');
                }

                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] - 1) {
                    $decision_matix->disallowDirection('up');
                }

                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] + 1) {
                    $decision_matix->disallowDirection('down');
                }

                // TODO: Expand boundary of neigbouring snake heads by 1 block in each direction.
                if ($snakes[$c]['x'] == $my_snake['x'] - 2 && $snakes[$c]['y'] == $my_snake['y']) {
                    $decision_matix->disallowDirection('left');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] + 2 && $snakes[$c]['y'] == $my_snake['y']) {
                    $decision_matix->disallowDirection('right');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] - 2) {
                    $decision_matix->disallowDirection('up');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] + 2) {
                    $decision_matix->disallowDirection('down');
                }

                // Avoid collision with another snake's' tail
                for ($t = 0; $t < count($snakes[$c]['tails']); $t++) {
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] - 1 && $snakes[$c]['tails'][$t]['y'] == $my_snake['y']) {
                        $decision_matix->disallowDirection('left');
                    }
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] + 1 && $snakes[$c]['tails'][$t]['y'] == $my_snake['y']) {
                        $decision_matix->disallowDirection('right');
                    }
                    if ($snakes[$c]['tails'][$t]['x']  == $my_snake['x'] && $snakes[$c]['tails'][$t]['y'] == $my_snake['y'] - 1) {
                        $decision_matix->disallowDirection('up');
                    }
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] && $snakes[$c]['tails'][$t]['y'] == $my_snake['y'] + 1) {
                        $decision_matix->disallowDirection('down');
                    }
                }
            }
        }
    }
}