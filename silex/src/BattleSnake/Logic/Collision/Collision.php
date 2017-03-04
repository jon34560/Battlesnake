<?php

/*
  * Collision - Collision Logic functions
*/

use Silex\Application;
namespace BattleSnake\Logic\Collision;

class Collision
{
    public static function wallCollisionDetection($state, $decision_matix, $log) {
        $my_snake = $state['snakes'][$state['s']];
        $x = $my_snake['x'];
        $y = $my_snake['y'];

        if ($x - 1 < 0) {
            $log->warning("Collision: Removing Left -> Wall");
            $decision_matix->disallowDirection('left');
        }
        if ($x + 1 >= $state['board_width']) {
            $log->warning("Collision: Removing Right -> Wall");
            $decision_matix->disallowDirection('right');
        }
        if ($y - 1 < 0) {
            $log->warning("Collision: Removing Up -> Wall");
            $decision_matix->disallowDirection('up');
        }
        if ($y + 1 >= $state['board_height']) {
            $log->warning("Collision: Removing Down -> Wall");
            $decision_matix->disallowDirection('down');
        }
    }

    public static function selfCollisionDetection($state, $decision_matix, $log) {
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

    public static function snakeCollisionDetection($state, $decision_matix, $log) {
        $my_snake = $state['snakes'][$state['s']];
        $snakes = $state['snakes'];

        // Four possible locations our snake head could be next turn

        for ($c = 0;  $c < count($snakes); $c++) {
            if ($c != $state['s'] && $snakes[$c]['alive'] == true) {
                // Avoid collision with current location of other snake heads = body part next turn
                if ($snakes[$c]['x'] == $my_snake['x'] - 1 && $snakes[$c]['y'] == $my_snake['y']) {
                    $log->warning("Collision: Removing Left -> Collide with snake");
                    $decision_matix->disallowDirection('left');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] + 1 && $snakes[$c]['y'] == $my_snake['y']) {
                    $log->warning("Collision: Removing Right -> Collide with snake");
                    $decision_matix->disallowDirection('right');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] - 1) {
                    $log->warning("Collision: Removing Up -> Collide with snake");
                    $decision_matix->disallowDirection('up');
                }
                if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] == $my_snake['y'] + 1) {
                    $log->warning("Collision: Removing Down -> Collide with snake");
                    $decision_matix->disallowDirection('down');
                }

                if ($my_snake['length'] <= $snakes[$c]['length']) {
                    // Avoid head to head collisions with other snakes if we are shorter
                    if ($snakes[$c]['x'] + 1 == $my_snake['x'] - 1 && $snakes[$c]['y'] == $my_snake['y']) {
                        $log->warning("Collision: Avoid Left my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('left', 500);
                    }
                    if ($snakes[$c]['x'] - 1 == $my_snake['x'] + 1 && $snakes[$c]['y'] == $my_snake['y']) {
                        $log->warning("Collision: Avoid Right my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('right', 500);
                    }
                    if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] + 1 == $my_snake['y'] - 1) {
                        $log->warning("Collision: Avoid Up my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('up', 500);
                    }
                    if ($snakes[$c]['x'] == $my_snake['x'] && $snakes[$c]['y'] - 1 == $my_snake['y'] + 1) {
                        $log->warning("Collision: Avoid Down my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('down', 500);
                    }
                }

                // Avoid diagonal head collision. Collide with another head at ninty degree angle
                if ($my_snake['length'] <= $snakes[$c]['length']) {
                    if ($my_snake['x'] - 1 == $snakes[$c]['x'] && ($my_snake['y'] + 1 == $snakes[$c]['y'] || $my_snake['y'] - 1 == $snakes[$c]['y'])) {
                        $log->warning("Collision: Avoid Left my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('left', 500);
                    }
                    if ($my_snake['x'] + 1 == $snakes[$c]['x'] && ($my_snake['y'] + 1 == $snakes[$c]['y'] || $my_snake['y'] - 1 == $snakes[$c]['y'])) {
                        $log->warning("Collision: Avoid Right my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('right', 500);
                    }
                    if ($my_snake['y'] - 1 == $snakes[$c]['y'] && ($my_snake['x'] + 1 == $snakes[$c]['x'] || $my_snake['x'] - 1 == $snakes[$c]['x'])) {
                        $log->warning("Collision: Avoid Up my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('up', 500);
                    }
                    if ($my_snake['y'] + 1 == $snakes[$c]['y'] && ($my_snake['x'] + 1 == $snakes[$c]['x'] || $my_snake['x'] - 1 == $snakes[$c]['x'])) {
                        $log->warning("Collision: Avoid Down my HUGE amount -> Collide with larger snake head");
                        $decision_matix->decrementPreferedDirectionValue('down', 500);
                    }
                }

                // Avoid collision with another snake's' tail
                for ($t = 0; $t < count($snakes[$c]['tails']); $t++) {
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] - 1 && $snakes[$c]['tails'][$t]['y'] == $my_snake['y']) {
                        $log->warning("Collision: Removing Left -> Collide with snake");
                        $decision_matix->disallowDirection('left');
                    }
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] + 1 && $snakes[$c]['tails'][$t]['y'] == $my_snake['y']) {
                        $log->warning("Collision: Removing Right -> Collide with snake");
                        $decision_matix->disallowDirection('right');
                    }
                    if ($snakes[$c]['tails'][$t]['x']  == $my_snake['x'] && $snakes[$c]['tails'][$t]['y'] == $my_snake['y'] - 1) {
                        $log->warning("Collision: Removing Up -> Collide with snake");
                        $decision_matix->disallowDirection('up');
                    }
                    if ($snakes[$c]['tails'][$t]['x'] == $my_snake['x'] && $snakes[$c]['tails'][$t]['y'] == $my_snake['y'] + 1) {
                        $log->warning("Collision: Removing Down -> Collide with snake");
                        $decision_matix->disallowDirection('down');
                    }
                }
            }
        }
    }
}
