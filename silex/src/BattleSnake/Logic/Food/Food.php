<?php

/*
  * Food - Food Logic functions
*/

namespace BattleSnake\Logic\Food;

use BattleSnake\Logic\Board\Board;

class Food
{
    // Linear Food Target, Proof of concept look farther the hungrier the snake is
    // Only target food if there is a clear path.
    public static function linearFoodSearch($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];
        $foods = $state['foods'];
        $snakes = $state['snakes'];

        $vision = 1;
        $weight = 10;
        if ($my_snake['health'] < 80) {
            $vision = 4;
        }
        if ($my_snake['health'] < 70) {
            $vision = 6;
        }
        if ($my_snake['health'] < 60) {
            $vision = 12;
        }
        if ($my_snake['health'] < 50) {
            $vision = 20;
        }

        for ($f = 0; $f < count($foods); $f++) {
            for ($v = 1; $v <= $vision; $v++) {
                // Look Left
                if ($my_snake['x'] - $v == $foods[$f]['x'] && $my_snake['y'] == $foods[$f]['y'] && $foods[$f]['active'] == true) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['x'] - 1; $p > $foods[$f]['x']; $p--) {
                        if (!Board::isSpaceEmpty($state, $p, $my_snake['y'], $decission_matrix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('left')) {
                        $decision_matix->incrementPreferedDirectionValue('left', $weight);
                    }
                }

                // Look Right
                if ($my_snake['x'] + $v == $foods[$f]['x'] && $my_snake['y'] == $foods[$f]['y'] && $foods[$f]['active'] == true) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['x'] + 1; $p < $foods[$f]['x']; $p++) {
                        if (!Board::isSpaceEmpty($state, $p, $my_snake['y'], $decission_matrix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('right')) {
                        $decision_matix->incrementPreferedDirectionValue('right', $weight);
                    }
                }

                // Look Up
                if ($my_snake['x'] == $foods[$f]['x'] && $my_snake['y'] - $v == $foods[$f]['y'] && $foods[$f]['active'] == true) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['y'] - 1; $p > $foods[$f]['y']; $p--) {
                        if (!Board::isSpaceEmpty($state, $my_snake['x'], $p, $decission_matrix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('up')) {
                        $decision_matix->incrementPreferedDirectionValue('up', $weight);
                    }
                }

                // Look Down
                if ($my_snake['x']  == $foods[$f]['x'] && $my_snake['y'] + $v == $foods[$f]['y'] && $foods[$f]['active'] == true) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['y'] + 1; $p < $foods[$f]['y']; $p++) {
                        if (!Board::isSpaceEmpty($state, $my_snake['x'], $p, $decission_matrix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('down')) {
                        $decision_matix->incrementPreferedDirectionValue('down', $weight);
                    }
                }
            }
        }
    }
}