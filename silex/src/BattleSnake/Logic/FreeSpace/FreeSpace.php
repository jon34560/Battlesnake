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
    public static function linearFreeSpaceDetection($state, $decision_matix, $log) {
        $my_snake = $state['snakes'][$state['s']];
        $spaceWeight = 8; // was 1 but 10 is better

	$vision = 5;

        $leftSpace = 0;
        $rightSpace = 0;
        $upSpace = 0;
        $downSpace = 0;

        // Left
        for ($i = 1; $i <= $vision; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'] - $i, $my_snake['y'], $decision_matix)) {
                $leftSpace++;
            } else {
                break;
            }
        }
        // Right
        for ($i = 1; $i <= $vision; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'] + $i, $my_snake['y'], $decision_matix)) {
                $rightSpace++;
            } else {
                break;
            }
        }
        // Up
        for ($i = 1; $i <= $vision; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'], $my_snake['y'] - $i, $decision_matix)) {
                $upSpace++;
            } else {
                break;
            }
        }
        // Down
        for ($i = 1; $i <= $vision; $i++) {
            if (Board::isSpaceEmpty($state, $my_snake['x'], $my_snake['y'] + $i, $decision_matix)) {
                $downSpace++;
            } else {
                break;
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
                $log->warning("Linear: Prefer Left");
                $decision_matix->incrementPreferedDirectionValue('left', $spaceWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $log->warning("Linear: Prefer Up");
                $decision_matix->incrementPreferedDirectionValue('up', $spaceWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $log->warning("Linear: Prefer Right");
                $decision_matix->incrementPreferedDirectionValue('right', $spaceWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $log->warning("Linear: Prefer Down");
                $decision_matix->incrementPreferedDirectionValue('down', $spaceWeight);
            }
        }
    }

    /*
    * floodFillDetection
    *
    * Description: check number of free spaces in each direction from current snake head.
    */
    public static function floodFillDetection($state, $decision_matix, $log) {
        $fillWeight = 10; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        $leftSpaces = [];
        $checkPosX = $my_snake['x'] - 1;
        $checkPosY = $my_snake['y'];
        $leftFill = self::floodFill($state, $checkPosX, $checkPosY, $leftSpaces, $decision_matix, 0, $log);
        $avoidLeft = false;

        $rightSpaces = [];
        $checkPosX = $my_snake['x'] + 1;
        $checkPosY = $my_snake['y'];
        $rightFill = self::floodFill($state, $checkPosX, $checkPosY, $rightSpaces, $decision_matix, 0, $log);
        $avoidRight = false;

        $upSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] - 1;
        $upFill = self::floodFill($state, $checkPosX, $checkPosY, $upSpaces, $decision_matix, 0, $log);
        $avoidUp = false;

        $downSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] + 1;
        $downFill = self::floodFill($state, $checkPosX, $checkPosY, $downSpaces, $decision_matix, 0, $log);
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
        /*if ($decision_matix->getAllowedDirectionValue('left') && ($leftFill > $my_snake['length']*2 && ( $avoidUp || $avoidRight || $avoidDown ))) {
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
        }*/

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
                $log->warning("FloodFill: Prefer Left");
                $decision_matix->incrementPreferedDirectionValue('left', $fillWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $log->warning("FloodFill: Prefer Right");
                $decision_matix->incrementPreferedDirectionValue('right', $fillWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $log->warning("FloodFill: Prefer Up");
                $decision_matix->incrementPreferedDirectionValue('up', $fillWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $log->warning("FloodFill: Prefer Down");
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
    public static function floodFill($state, $checkPosX, $checkPosY, &$spaces, $decision_matix, $depth = 0, $log) {
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
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1, $log);
            }

            // Up
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] - 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1, $log);
            }

            // Right
            $tx = $spaces[$key]['x'] + 1;
            $ty = $spaces[$key]['y'];
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1, $log);
            }

            // Down
            $tx = $spaces[$key]['x'];
            $ty = $spaces[$key]['y'] + 1;
            $tKey = $tx . '_' . $ty;
            if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                $checkPosX = $tx;
                $checkPosY = $ty;
                $fillCount += self::floodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $depth+1, $log);
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
    public static function weightedFloodFillDetection($state, $decision_matix, $log) {
        $fillWeight = 8; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        $leftSpaces = [];
        $checkPosX = $my_snake['x'] - 1;
        $checkPosY = $my_snake['y'];
        $leftFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $leftSpaces, $decision_matix, 'left', 0, $log);
        $avoidLeft = false;

        $rightSpaces = [];
        $checkPosX = $my_snake['x'] + 1;
        $checkPosY = $my_snake['y'];
        $rightFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $rightSpaces, $decision_matix, 'right', 0, $log);
        $avoidRight = false;

        $upSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] - 1;
        $upFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $upSpaces, $decision_matix, 'up', 0, $log);
        $avoidUp = false;

        $downSpaces = [];
        $checkPosX = $my_snake['x'];
        $checkPosY = $my_snake['y'] + 1;
        $downFill = self::weightedFloodFill($state, $checkPosX, $checkPosY, $downSpaces, $decision_matix, 'down', 0, $log);
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
                $log->warning("weightedFloodFill: Prefer Left");
                $decision_matix->incrementPreferedDirectionValue('left', $fillWeight);
            }
            if ($bestKey == 'right' && $decision_matix->getAllowedDirectionValue('right')) {
                $log->warning("weightedFloodFill: Prefer Right");
                $decision_matix->incrementPreferedDirectionValue('right', $fillWeight);
            }
            if ($bestKey == 'up' && $decision_matix->getAllowedDirectionValue('up')) {
                $log->warning("weightedFloodFill: Prefer Up");
                $decision_matix->incrementPreferedDirectionValue('up', $fillWeight);
            }
            if ($bestKey == 'down' && $decision_matix->getAllowedDirectionValue('down')) {
                $log->warning("weightedFloodFill: Prefer Down");
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
    public static function weightedFloodFill($state, $checkPosX, $checkPosY, &$spaces, $decision_matix, $look_direction, $depth = 0, $log) {
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
            if ($look_direction != 'right') {
                $tx = $spaces[$key]['x'] - 1;
                $ty = $spaces[$key]['y'];
                $tKey = $tx . '_' . $ty;
                if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                    $checkPosX = $tx;
                    $checkPosY = $ty;
                    $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $look_direction, $depth+1, $log);
                }
            }

            // Up
            if ($look_direction != 'down') {
                $tx = $spaces[$key]['x'];
                $ty = $spaces[$key]['y'] - 1;
                $tKey = $tx . '_' . $ty;
                if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                    $checkPosX = $tx;
                    $checkPosY = $ty;
                    $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $look_direction, $depth+1, $log);
                }
            }

            // Right
            if ($look_direction != 'left') {
                $tx = $spaces[$key]['x'] + 1;
                $ty = $spaces[$key]['y'];
                $tKey = $tx . '_' . $ty;
                if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                    $checkPosX = $tx;
                    $checkPosY = $ty;
                    $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $look_direction, $depth+1, $log);
                }
            }

            // Down
            if ($look_direction != 'up') {
                $tx = $spaces[$key]['x'];
                $ty = $spaces[$key]['y'] + 1;
                $tKey = $tx . '_' . $ty;
                if (Board::isSpaceOnBoard($state, $tx, $ty, $decision_matix) && !($spaces[$tKey] ?? false)) {
                    $checkPosX = $tx;
                    $checkPosY = $ty;
                    $fillCount += self::weightedFloodFill($state, $checkPosX, $checkPosY, $spaces, $decision_matix, $look_direction, $depth+1, $log);
                }
            }
        }
        return $fillCount;
    }

    /*
    * preferWallGap
    *
    * Description: Check potential moves. If move up against wall prefer other options
    *
    */
    public static function preferWallGap($state, $decision_matix, $log) {
        $decrementWeight = 5; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        // Only bother checking if we haven't already eliminated the option
        if ($decision_matix->getAllowedDirectionValue('left')) {
            if ($my_snake['x'] - 2 < 0) {
                $log->warning("preferWallGap: Avoid Left");
                $decision_matix->decrementPreferedDirectionValue('left', $decrementWeight);
            }
        }
        if ($decision_matix->getAllowedDirectionValue('right')) {
            if ($my_snake['x'] + 2 >= $state['board_width']) {
                $log->warning("preferWallGap: Avoid Right");
                $decision_matix->decrementPreferedDirectionValue('right', $decrementWeight);
            }
        }
        if ($decision_matix->getAllowedDirectionValue('up')) {
            if ($my_snake['y'] - 2 < 0) {
                $log->warning("preferWallGap: Avoid Up");
                $decision_matix->decrementPreferedDirectionValue('up', $decrementWeight);
            }
        }
        if ($decision_matix->getAllowedDirectionValue('down')) {
            if ($my_snake['y'] + 2 >= $state['board_height']) {
                $log->warning("preferWallGap: Avoid Down");
                $decision_matix->decrementPreferedDirectionValue('down', $decrementWeight);
            }
        }
    }

    /*
    * preferSelfGap
    *
    * Description: Check potential moves. If move up against self prefer other options
    *
    */
    public static function preferSelfGap($state, $decision_matix, $log) {
        $decrementWeight = 4; // 2; // 12 is better than 2
        $my_snake = $state['snakes'][$state['s']];

        // Only bother checking if we haven't already eliminated the option
        if ($decision_matix->getAllowedDirectionValue('left')) {
            for ($c = 0; $c < count($my_snake['tails']); $c++) {
                if ($my_snake['x'] - 1 == $my_snake['tails'][$c]['x'] && ($my_snake['y'] == $my_snake['tails'][$c]['y'] - 1 || $my_snake['y'] == $my_snake['tails'][$c]['y'] + 1)) {
                    $log->warning("preferSelfGap: Avoid Left");
                    $decision_matix->decrementPreferedDirectionValue('left', $decrementWeight);
                    break;
                }
            }
        }
        if ($decision_matix->getAllowedDirectionValue('right')) {
            for ($c = 0; $c < count($my_snake['tails']); $c++) {
                if ($my_snake['x'] + 1 == $my_snake['tails'][$c]['x'] && ($my_snake['y'] == $my_snake['tails'][$c]['y'] - 1 || $my_snake['y'] == $my_snake['tails'][$c]['y'] + 1)) {
                    $log->warning("preferSelfGap: Avoid Right");
                    $decision_matix->decrementPreferedDirectionValue('right', $decrementWeight);
                    break;
                }
            }
        }
        if ($decision_matix->getAllowedDirectionValue('up')) {
            for ($c = 0; $c < count($my_snake['tails']); $c++) {
                if ($my_snake['y'] - 1 == $my_snake['tails'][$c]['y'] && ($my_snake['x'] == $my_snake['tails'][$c]['x'] - 1 || $my_snake['x'] == $my_snake['tails'][$c]['x'] + 1)) {
                    $log->warning("preferSelfGap: Avoid Up");
                    $decision_matix->decrementPreferedDirectionValue('up', $decrementWeight);
                    break;
                }
            }
        }
        if ($decision_matix->getAllowedDirectionValue('down')) {
            for ($c = 0; $c < count($my_snake['tails']); $c++) {
                if ($my_snake['y'] + 1 == $my_snake['tails'][$c]['y'] && ($my_snake['x'] == $my_snake['tails'][$c]['x'] - 1 || $my_snake['x'] == $my_snake['tails'][$c]['x'] + 1)) {
                    $log->warning("preferSelfGap: Avoid Down");
                    $decision_matix->decrementPreferedDirectionValue('down', $decrementWeight);
                    break;
                }
            }
        }
    }
}
