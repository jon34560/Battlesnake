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
        $weight = 6;
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
            $vision = 30;
        }

        for ($f = 0; $f < count($foods); $f++) {
            for ($v = 1; $v <= $vision; $v++) {
                // Look Left
                if ($my_snake['x'] - $v == $foods[$f]['x'] && $my_snake['y'] == $foods[$f]['y']) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['x'] - 1; $p > $foods[$f]['x']; $p--) {
                        if (!Board::isSpaceEmpty($state, $p, $my_snake['y'], $decision_matix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('left')) {
                        $decision_matix->incrementPreferedDirectionValue('left', $weight);
                    }
                }

                // Look Right
                if ($my_snake['x'] + $v == $foods[$f]['x'] && $my_snake['y'] == $foods[$f]['y']) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['x'] + 1; $p < $foods[$f]['x']; $p++) {
                        if (!Board::isSpaceEmpty($state, $p, $my_snake['y'], $decision_matix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('right')) {
                        $decision_matix->incrementPreferedDirectionValue('right', $weight);
                    }
                }

                // Look Up
                if ($my_snake['x'] == $foods[$f]['x'] && $my_snake['y'] - $v == $foods[$f]['y']) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['y'] - 1; $p > $foods[$f]['y']; $p--) {
                        if (!Board::isSpaceEmpty($state, $my_snake['x'], $p, $decision_matix)) {
                            $clear = false;
                        }
                    }
                    if ($clear && $decision_matix->getAllowedDirectionValue('up')) {
                        $decision_matix->incrementPreferedDirectionValue('up', $weight);
                    }
                }

                // Look Down
                if ($my_snake['x']  == $foods[$f]['x'] && $my_snake['y'] + $v == $foods[$f]['y']) {
                    // Is path clear
                    $clear = true;
                    for ($p = $my_snake['y'] + 1; $p < $foods[$f]['y']; $p++) {
                        if (!Board::isSpaceEmpty($state, $my_snake['x'], $p, $decision_matix)) {
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

    // Any angle Food target. Head towards closest food if there is no obsticle.
    public static function angleFoodSearch($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];
        $foods = $state['foods'];
        $snakes = $state['snakes'];
        $dirWeight = 1;

        if ($my_snake['health'] < 85) {  // Prioritize food when health low
            $dirWeight = 2;
        }
        if ($my_snake['health'] < 50) {  // Prioritize food when health low
            $dirWeight = 3;
        }
	if ($my_snake['health'] < 40){
		$dirWeight = 4;
	}
	if ($my_snake['health'] < 30){
                $dirWeight = 10;
        }
	if ($my_snake['health'] < 20){
                $dirWeight = 20;
        }

        $distances = [];

        for ($f = 0; $f < count($foods); $f++ ) {
            $fx = (float)$foods[$f]['x'];
            $fy = (float)$foods[$f]['y'];
            $distances[$f] = sqrt(pow((float)$my_snake['x'] - $fx, 2) + pow((float)$my_snake['y'] - $fy, 2));
            // Calculate obsticles in path
            // If other snakes (c) are within bounding box of current snake
            $range = Board::isRangeEmpty($state, $my_snake['x'], $my_snake['y'], $fx, $fy, $decision_matix);
            if ($range > 0) {
                $distances[$f] = 999999; // Forget it
            }
        }

        asort($distances);
        reset($distances);
        $closestKey = key($distances);
        $closestValue = $distances[$closestKey];
        $xDir = $foods[$closestKey]['x'] - $my_snake['x'];
        $yDir = $foods[$closestKey]['y'] - $my_snake['y'];

        if ($closestValue < 100) {
            if (abs($xDir) > abs($yDir)) { // horizontal
                if ($xDir < 0 && $decision_matix->getAllowedDirectionValue('left')) {
                    $decision_matix->incrementPreferedDirectionValue('left', $dirWeight);
                } else if ($decision_matix->getAllowedDirectionValue('right')) {
                    $decision_matix->incrementPreferedDirectionValue('right', $dirWeight);
                }
            } else { // Vertical
                if ($yDir < 0 && $decision_matix->getAllowedDirectionValue('up')) {
                    $decision_matix->incrementPreferedDirectionValue('up', $dirWeight);
                } else if ($decision_matix->getAllowedDirectionValue('down')) {
                    $decision_matix->incrementPreferedDirectionValue('down', $dirWeight);
                }
            }
        }
    }


    /**
    * pathFoodSearch
    * 
    * Description: 
    */
    public static function pathFoodSearch($state, $decision_matix, $log) {
        $my_snake = $state['snakes'][$state['s']];
        $foods = $state['foods'];
        $snakes = $state['snakes'];
        $dirWeight = 5;

        if ($my_snake['health'] < 80) {  // Prioritize food when health low
            $dirWeight = 7;
        }
        if ($my_snake['health'] < 60){
                $dirWeight = 10;
        }
        if ($my_snake['health'] < 40){
                $dirWeight = 15;
        }
        if ($my_snake['health'] < 20){
                $dirWeight = 20;
        }

        $distances = [];

	// TODO: Only search paths if hungry < 50%
        for ($f = 0; $f < count($foods); $f++ ) {
            $fx = (float)$foods[$f]['x'];
            $fy = (float)$foods[$f]['y'];
            $distances[$f] = sqrt(pow((float)$my_snake['x'] - $fx, 2) + pow((float)$my_snake['y'] - $fy, 2));
	 	// $range = Board::isRangeEmpty($state, $my_snake['x'], $my_snake['y'], $fx, $fy, $decision_matix);
	}
	asort($distances);
        reset($distances);
        $closestKey = key($distances);
        $closestValue = $distances[$closestKey];
        $xDir = $foods[$closestKey]['x'] - $my_snake['x'];
        $yDir = $foods[$closestKey]['y'] - $my_snake['y'];

	$pathFree = true;

	$distance = $distances[$closestKey];

	$a = "";

	$x = $my_snake['x'];
	$y = $my_snake['y']; 	
	$running = true;
	for( $i = 1; $running && $i < $distance * 4; $i++ ){
		$_xDir = $foods[$closestKey]['x'] - $x;
	        $_yDir = $foods[$closestKey]['y'] - $y;

		if($x != $foods[$closestKey]['x']){
			$x = $x + ($_xDir > 0 ? 1 : -1);
		}  	
		if($y != $foods[$closestKey]['y']){	
			$y = $y + ($_yDir > 0 ? 1 : -1);
		}	
		if($x == $foods[$closestKey]['x'] && $y == $foods[$closestKey]['y']) {
			$running = false;
			break;
		}
		if(!Board::isSpaceEmpty($state, $x, $y, $decision_matix)){
			$pathFree = false;
		}
		$a .= " [" . $x . "," .$y . "]" ;
	}
	//$log->warning(" FOOD PATH PATH " . ($pathFree ? '1': '0') . "   x" . $xDir . " y" . $yDir . " me ".$my_snake['x'] ." ". $my_snake['y']."   f:     " . $a );	
	if( $pathFree ){
	    if (abs($xDir) > abs($yDir)) { // horizontal
                if ($xDir < 0 && $decision_matix->getAllowedDirectionValue('left')) {
                    $decision_matix->incrementPreferedDirectionValue('left', $dirWeight);
			//$log->warning(" FOOD PATH PATH   LEFT ");
		} else if ($decision_matix->getAllowedDirectionValue('right')) {
                    $decision_matix->incrementPreferedDirectionValue('right', $dirWeight);
			//$log->warning(" FOOD PATH PATH   RIGHT ");
		}
            } else { // Vertical
                if ($yDir < 0 && $decision_matix->getAllowedDirectionValue('up')) {
                    $decision_matix->incrementPreferedDirectionValue('up', $dirWeight);
			//$log->warning(" FOOD PATH PATH   UP ");
		} else if ($decision_matix->getAllowedDirectionValue('down')) {
                    $decision_matix->incrementPreferedDirectionValue('down', $dirWeight);
			//$log->warning(" FOOD PATH PATH   DOWN ");
		}
            }	
	}
	

    }

}
