<?php

/*
* FreeSpace - Free Space on board Logic functions
*/

namespace BattleSnake\Logic\FreeSpace;

use BattleSnake\Logic\Board\Board;

class FreeSpace
{
    /*
      * linearFreeSpaceDetection
      *
      * Description: Go in direction of open space. Avoid being trapped.
      *	This will fail if there is a way out and it is tricked into a cave.
    */
    public static function linearFreeSpaceDetection($state, $decision_matix) {
        $my_snake = $state['snakes'][$state['s']];

        $vision = 5;
        $spaceWeight = 10; // was 1 but 10 is better

        $leftSpace = 0;
        $rightSpace = 0;
        $upSpace = 0;
        $downSpace = 0;

        // Left
        for ($i = 1; $i < $vision + 1; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'] - $i, $my_snake['y'], $decision_matix)) {
                $leftSpace++;
            }
        }
        // Right
        for ($i = 1; $i < $vision + 1; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'] + $i, $my_snake['y'], $decision_matix)) {
                $rightSpace++;
            }
        }
        // Up
        for ($i = 1; $i < $vision + 1; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'], $my_snake['y'] - $i, $decision_matix)) {
                $upSpace++;
            }
        }
        // Down
        for ($i = 1; $i < $vision + 1; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'], $my_snake['y'] + $i, $decision_matix)) {
                $downSpace++;
            }
        }

        // Sort best direction
        $directions = ['left' => $leftSpace, 'up' => $upSpace, 'right' => $rightSpace, 'down' => $downSpace];
        arsort($directions);
        reset($directions);
        $bestKey = key($directions);
        $bestValue = $directions[$bestKey];
        asort($directions);
        reset($directions);
        $worstKey = key($directions);
        $worstValue = $directions[$worstKey];

        if ($bestValue > 0 && $bestValue > $worstValue) {
            // If in closed space increase spaceWeight.
            if ($bestKey == 'left' && $decision_matix->getAllowedDirectionValue('left')) {
                $decision_matix->incrementPreferedDirectionValue('left', $spaceWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $decision_matix->incrementPreferedDirectionValue('up', $spaceWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $decision_matix->incrementPreferedDirectionValue('right', $spaceWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $decision_matix->incrementPreferedDirectionValue('down', $spaceWeight);
            }
        }
    }

    /*
    * floodFillDetection
    *
    * Description: check number of free spaces in each direction from current snake head.
    */
    public static function floodFillDetection($state, $decision_matix) {
        $fillWeight = 12; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        $leftSpaces = [];
        $checkPosX = $my_snake['x'] - 1;
        $checkPosY = $my_snake['y'];
        $leftFill = self::floodFill($state, $checkPosX, $checkPosY, $leftSpaces, $decision_matix);
        $avoidLeft = false;

        $rightSpaces = [];
        $checkPosX = $my_snake['x'] + 1;
        $checkPosY = $my_snake['y'];
        $rightFill = self::floodFill($state, $checkPosX, $checkPosY, $rightSpaces, $decision_matix);
        $avoidRight = false;

        $upSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] - 1;
        $upFill = self::floodFill($state, $checkPosX, $checkPosY, $upSpaces, $decision_matix);
        $avoidUp = false;

        $downSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] + 1;
        $downFill = self::floodFill($state, $checkPosX, $checkPosY, $downSpaces, $decision_matix);
        $avoidDown = false;

        if ($leftFill > 0 && $leftFill <= count($my_snake['tails']) * 2) { // Is there enough space to the left to fit the snake.
            $avoidLeft = true;
        }
        if ($upFill > 0 && $upFill <= count($my_snake['tails']) * 2) {
            $avoidUp = true;
        }
        if ($rightFill > 0 && $rightFill <= count($my_snake['tails']) * 2) {
            $avoidRight = true;
        }
        if ($downFill > 0 && $downFill <= count($my_snake['tails']) * 2) {
            $avoidDown = true;
        }

        // If one direction is good and one or more other directions are bad, increase the priority.
        if ($decision_matix->getAllowedDirectionValue('left') && ($leftFill > $my_snake['length']*2 && ( $avoidUp || $avoidRight || $avoidDown ))) {
              $decision_matix->incrementPreferedDirectionValue('left', 50);
        }
        if ($decision_matix->getAllowedDirectionValue('up') && ($upFill > $my_snake['length']*2 && ( $avoidLeft || $avoidRight || $avoidDown ))) {
              $decision_matix->incrementPreferedDirectionValue('up', 50);
        }
        if ($decision_matix->getAllowedDirectionValue('right') && ($rightFill > $my_snake['length']*2 && ( $avoidLeft || $avoidUp || $avoidDown ))) {
              $decision_matix->incrementPreferedDirectionValue('right', 50);
        }
        if ($decision_matix->getAllowedDirectionValue('down') && ($downFill > $my_snake['length']*2 && ( $avoidLeft || $avoidUp || $avoidRight ))) {
              $decision_matix->incrementPreferedDirectionValue('down', 50);
        }

        // Chose direction based on direction counts found.
        $directions = ['left' => $leftFill, 'up' => $upFill, 'right' => $rightFill, 'down' => $downFill];
        arsort($directions);
        reset($directions);
        $bestKey = key($directions);
        $bestValue = $directions[$bestKey];
        asort($directions);
        reset($directions);
        $worstKey = key($directions);
        $worstValue = $directions[$worstKey];

        if ($bestValue > 0 && $bestValue > $worstValue) {
            if ($bestKey == 'left' && $decision_matix->getAllowedDirectionValue('left')) {
                $decision_matix->incrementPreferedDirectionValue('left', $fillWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $decision_matix->incrementPreferedDirectionValue('right', $fillWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $decision_matix->incrementPreferedDirectionValue('up', $fillWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $decision_matix->incrementPreferedDirectionValue('down', $fillWeight);
            }
        }
    }


    /*
    * floodFill
    *
    * Description: Check tiles in one direction to see how many open are connected.
    *
    * Optimization: Don't search farther than we have to...
    */
    public static function floodFill($state, $checkPosX, $checkPosY, &$spaces, $decision_matix, $depth = 0) {
        // Limit floodFill recursion
        if ($depth > 40) {
            return 0;
        }

        $oldCheckPosX = $checkPosX;
        $oldCheckPosY = $checkPosY;
        $fillCount = 0;
        $key = $checkPosX . '_' . $checkPosY;

        if (array_key_exists($key, $spaces)) {
            return 0;
        }

        $isEmpty = false;
        if (Board::isSpaceEmpty($state, $checkPosX, $checkPosY, $decision_matix)) {
            $fillCount++;
            $isEmpty = true;
        }

        $spaces[$key] = ['open' => $isEmpty, 'x' => $checkPosX, 'y' => $checkPosY];
        // Find next pos to scan
        $i = 0;
        if ($spaces[$key]['open'] == true) {
            // Left
            $tx = $spaces[$key]['x'] - 1;
            $ty = $spaces[$key]['y'];
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }

            // Up
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] - 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }

            // Right
            $tx = $spaces[$key]['x'] + 1;
            $ty = $spaces[$key]['y'];
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }

            // Down
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] + 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }
        }
        return $fillCount;
    }



    /*
    * weightedFloodFillDetection
    *
    * Description: check number of free spaces in each direction from current snake head.
    *  WARNING: the fillWeight must be less that that of the floodFillDetection function!!!
    */
    public static function weightedFloodFillDetection($state, $decision_matix) {
        $fillWeight = 5; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        $leftSpaces = [];
        $checkPosX = $my_snake['x'] - 1;
        $checkPosY = $my_snake['y'];
        $leftFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $leftSpaces, $decision_matix);
        $avoidLeft = false;

        $rightSpaces = [];
        $checkPosX = $my_snake['x'] + 1;
        $checkPosY = $my_snake['y'];
        $rightFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $rightSpaces, $decision_matix);
        $avoidRight = false;

        $upSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] - 1;
        $upFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $upSpaces, $decision_matix);
        $avoidUp = false;

        $downSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] + 1;
        $downFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $downSpaces, $decision_matix);
        $avoidDown = false;

    	if ($leftFill > 0 && $leftFill <= count($my_snake['tails']) * 2) { // Is there enough space to the left to fit the snake.
            $avoidLeft = true;
        }
        if ($upFill > 0 && $upFill <= count($my_snake['tails']) * 2) {
            $avoidUp = true;
        }
        if ($rightFill > 0 && $rightFill <= count($my_snake['tails']) * 2) {
            $avoidRight = true;
        }
        if ($downFill > 0 && $downFill <= count($my_snake['tails']) * 2) {
            $avoidDown = true;
        }

        // If one direction is good and one or more other directions are bad, increase the priority.
        //if ($decision_matix->getAllowedDirectionValue('left') && ($leftFill > $my_snake['length']*2 && ( $avoidUp || $avoidRight || $avoidDown ))) {
        //      $decision_matix->incrementPreferedDirectionValue('left', 50);
        //}
        //if ($decision_matix->getAllowedDirectionValue('up') && ($upFill > $my_snake['length']*2 && ( $avoidLeft || $avoidRight || $avoidDown ))) {
        //      $decision_matix->incrementPreferedDirectionValue('up', 50);
        //}
        //if ($decision_matix->getAllowedDirectionValue('right') && ($rightFill > $my_snake['length']*2 && ( $avoidLeft || $avoidUp || $avoidDown ))) {
        //      $decision_matix->incrementPreferedDirectionValue('right', 50);
        //}
        //if ($decision_matix->getAllowedDirectionValue('down') && ($downFill > $my_snake['length']*2 && ( $avoidLeft || $avoidUp || $avoidRight ))) {
        //      $decision_matix->incrementPreferedDirectionValue('down', 50);
        //}

        // Chose direction based on direction counts found.
        $directions = ['left' => $leftFill, 'up' => $upFill, 'right' => $rightFill, 'down' => $downFill];
        arsort($directions);
        reset($directions);
        $bestKey = key($directions);
        $bestValue = $directions[$bestKey];
        asort($directions);
        reset($directions);
        $worstKey = key($directions);
        $worstValue = $directions[$worstKey];

        if ($bestValue > 0 && $bestValue > $worstValue) {
            if ($bestKey == 'left' && $decision_matix->getAllowedDirectionValue('left')) {
                $decision_matix->incrementPreferedDirectionValue('left', $fillWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $decision_matix->incrementPreferedDirectionValue('right', $fillWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $decision_matix->incrementPreferedDirectionValue('up', $fillWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $decision_matix->incrementPreferedDirectionValue('down', $fillWeight);
            }
        }
    }


	/*
    * floodFill
    *
    * Description: Check tiles in one direction to see how many open are connected.
    *
    * Optimization: Don't search farther than we have to...
    */
    public static function weightedFloodFill($state, $checkPosX, $checkPosY, &$spaces, $decision_matix, $depth = 0) {
        // Limit floodFill recursion
        if ($depth > 6) {
            return 0;
        }

        $oldCheckPosX = $checkPosX;
        $oldCheckPosY = $checkPosY;
        $fillCount = 0;
        $key = $checkPosX . '_' . $checkPosY;

        if (array_key_exists($key, $spaces)) {
            return 0;
        }

        $isEmpty = false;
        if (Board::isSpaceEmpty($state, $checkPosX, $checkPosY, $decision_matix)) {
            $fillCount = $fillCount + (20 - $depth);
            $isEmpty = true;
        }

        $spaces[$key] = ['open' => $isEmpty, 'x' => $checkPosX, 'y' => $checkPosY];
        // Find next pos to scan
        $i = 0;
        if ($spaces[$key]['open'] == true) {
            // Left
            $tx = $spaces[$key]['x'] - 1;
            $ty = $spaces[$key]['y'];
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !array_key_exists($tKey, $spaces)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }

            // Up
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] - 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx , $ty, $decision_matix) && !array_key_exists($tKey, $spaces)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }
	    // Right
            $tx = $spaces[$key]['x'] + 1;
            $ty = $spaces[$key]['y'];
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx , $ty, $decision_matix) && !array_key_exists($tKey, $spaces)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }

            // Down
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] + 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx , $ty, $decision_matix) && !array_key_exists($tKey, $spaces)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1);
            }
        }
        return $fillCount;
    }
	

}
