<?php

$STATE_FILE = "/var/www/magnite.org/game_state.dat";
$snake_colours = array(1 => '#FF4444', 2 => '#AAAA00', 3 => '#00EE22', 4 => '#2222FF', 5 => '#44EEEE', 6 => '#EE55EE', 7 => '88EE88', 8=> '#4477EE', 9=> '#FF8822');
$tick_cache = array();

function initaliseGameState( &$gameState ){
	$state = json_decode( $gameState, true );
	$state['board_width'] = 20;
	$state['board_height'] = 20;
	$state['snake_count'] = 4;
	$state['inital_health'] = 100;
	$state['ticks'] = 0;

	$snakes = array();
	for($i = 0; $i < 8; $i++){
		$snakes[$i]['x'] = rand(0, $state['board_width'] - 1);
		$snakes[$i]['y'] = rand(0, $state['board_height'] - 1); 
		$snakes[$i]['health'] = 100;
		$snakes[$i]['alive'] = true;
		$snakes[$i]['tails'] = array();
	}
	$state['snakes'] = $snakes;

	$foods = array();
	for($f = 0; $f < 12; $f++){
		$foods[$f]['active'] = true;	
		$foods[$f]['x'] = rand(0, $state['board_width'] - 1);
		$foods[$f]['y'] = rand(0, $state['board_height'] - 1); 
	}
	$state['foods'] = $foods;
 
	$gameState = json_encode( (array)$state );
	return $gameState;
}

function getDirection( $state, $s ){
	global $tick_cache;
	$left = true; 		// Allowed
	$up = true;
	$right = true;
	$down = true;

	$targetLeft = 0;	// Prefered
	$targetUp = 0;
	$targetRight = 0;
	$targetDown = 0;

	$snake = $state['snakes'][$s];

	// Avoid collision with edge
	$x = $state['snakes'][$s]['x'];
	$y = $state['snakes'][$s]['y'];
	if($state['snakes'][$s]['x'] - 1 < 0){
		$left = false;	
	}
	if($state['snakes'][$s]['y'] - 1 < 0){
                $up = false;
        }	
	if($state['snakes'][$s]['x'] + 1 >= $state['board_width']){
                $right = false;
        }
        if($state['snakes'][$s]['y'] + 1 >= $state['board_height']){
                $down = false;
        }

	// avoid Collision with self	
	for( $t = 0; $t < count($state['snakes'][$s]['tails']); $t++ ){
        	if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x'] - 1 &&
                	$state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y'] 
		){
			$left = false;
                }
		if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x']  &&
                        $state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y'] - 1
                ){
                        $up = false;
                }
		if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x'] + 1 &&
                        $state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y']
                ){
                        $right = false;
                }
		if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x']  &&
                        $state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y'] + 1
                ){
                        $down = false;
                }
        } 

	// Other snakes
	// NOTE: doesn't prevent two snakes from walking into the same space next round.
	for( $c = 0;  $c < count($state['snakes']); $c++ ){
		if($c != $s &&
			$state['snakes'][$s]['alive'] == true &&
			$state['snakes'][$c]['alive'] == true
		){
			// Avoid Collide with another snake head
			if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] - 1 &&
				$state['snakes'][$c]['y'] == $state['snakes'][$s]['y']  
                                        //count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) 
				){
						$left = false;
				}
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] &&
                                        $state['snakes'][$c]['y'] == $state['snakes'][$s]['y'] - 1
                                        //count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) 
                                ){
                                                $up = false;
                                }
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] + 1 &&
                                        $state['snakes'][$c]['y'] == $state['snakes'][$s]['y']
                                        //count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) 
                                ){
                                                $right = false;
                                }
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] &&
                                        $state['snakes'][$c]['y'] == $state['snakes'][$s]['y'] + 1
                                        //count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) 
                                ){
                                                $down = false;
                                }

				// TODO: Expand boundary of neigbouring snake heads by 1 block in each direction.
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] - 2 && $state['snakes'][$c]['y'] == $state['snakes'][$s]['y']){$left = false;}
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] && $state['snakes'][$c]['y'] == $state['snakes'][$s]['y'] - 2){$up = false;}
				if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] + 2 && $state['snakes'][$c]['y'] == $state['snakes'][$s]['y']){$right = false;}
                                if( $state['snakes'][$c]['x'] == $state['snakes'][$s]['x'] && $state['snakes'][$c]['y'] == $state['snakes'][$s]['y'] + 2){$down = false;}

                                // Collide with another snake tail
                                for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] - 1 &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']
                                        ){
						$left = false;		
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x']  == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y'] - 1
                                        ){
                                                $up = false;
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] + 1 &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']
                                        ){
                                                $right = false;
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y'] + 1
                                        ){
                                                $down = false;
                                        }
                                }
                        }
	}
	
	// Goal, head in direction of 1) free space and 2) food 

	// If closer to food than anyone else go for it.
	// TODO

	// Linear Food Target, Proof of concept look farther the hungrier the snake is
	// Only target food if there is a clear path.
	$vision = 1;
	if($state['snakes'][$s]['health'] < 80){
                $vision = 4;
        }
	if($state['snakes'][$s]['health'] < 70){
                $vision = 6;
        }
	if($state['snakes'][$s]['health'] < 60){
                $vision = 12;
        }	
	if($state['snakes'][$s]['health'] < 50){
		$vision = 20;
	}
	for( $f = 0; $f < count($state['foods']) - 1; $f++ ){
		for($v = 1; $v < $vision + 1; $v++){
			if( $state['snakes'][$s]['x'] - $v == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				// Is path clear 
				$clear = true;
				for($p = $x - 1; $p > $state['foods'][$f]['x']; $p--){
					if( !isSpaceEmpty( $state, $p, $y ) ){
						$clear = false;
					}
				}
				if($clear && $left){
					$targetLeft += 10;
				}
			}
			if( $state['snakes'][$s]['x'] == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] - $v == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				// Is path clear
                                $clear = true;
                                for($p = $y - 1; $p > $state['foods'][$f]['y']; $p--){
                                        if( !isSpaceEmpty( $state, $x, $p ) ){
                                                $clear = false;
                                        }
                                }
                                if($clear && $up){
					$targetUp += 10;
				}
			}	
			if( $state['snakes'][$s]['x'] + $v == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				// Is path clear
                                $clear = true;
                                for($p = $x + 1; $p < $state['foods'][$f]['x']; $p++){
                                        if( !isSpaceEmpty( $state, $p, $y ) ){
                                                $clear = false;
                                        }
                                }
                                if($clear && $right){	
					$targetRight += 10;
				}
			} 
			if( $state['snakes'][$s]['x']  == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] + $v == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				// Is path clear
                                $clear = true;
                                for($p = $y + 1; $p < $state['foods'][$f]['y']; $p++){
                                        if( !isSpaceEmpty( $state, $x, $p ) ){
                                                $clear = false;
                                        }
                                }
                                if($clear && $down){
					$targetDown += 10;
				}
			}	
		}	
	}

	//
	// Any angle Food target. Head towards closest food if there is no obsticle.
	//
	$dirWeight = 1;
	if($state['snakes'][$s]['health'] < 85){  // Prioritize food when health low
                $dirWeight = 3;
        }
        if($state['snakes'][$s]['health'] < 50){  // Prioritize food when health low
                $dirWeight = 15;
        }
	$distances = array();
	for( $f = 0; $f < count($state['foods']); $f++ ){
		if( $state['foods'][$f]['active'] == true ){
			$fx = (float)$state['foods'][$f]['x'];
			$fy = (float)$state['foods'][$f]['y'];  
			$distances[$f] = sqrt( pow( (float)$x - $fx , 2) + pow( (float)$y - $fy, 2) );
			//echo "  d " . $distances[$f]  . "  - $x $y -> ".$state['foods'][$f]['x']." ".$state['foods'][$f]['y']."   <br>";
			// Calculate obsticles in path
			
			// If other snakes (c) are within bounding box of curr snake and
                        $range = isRangeEmpty( $state, $x, $y, $fx, $fy );
			if($range > 0){
				$distances[$f] = 999999; // Forget it	
			}
			//echo " range " . $range . "<br>";                 
		}		
	}		
	asort($distances);
        reset($distances);
        $closestKey = key($distances);
        $closestValue = $distances[$closestKey];
	$xDir = $state['foods'][$closestKey]['x'] - $x;
	$yDir = $state['foods'][$closestKey]['y'] - $y;
	//echo "  snake " . $x ." " . $y ."  food " . $state['foods'][$closestKey]['x'] . " " . $state['foods'][$closestKey]['y'] . "<br>";  
	//echo "  dir ".$xDir." ".$yDir."  i: " . $closestKey . " dist: " . $closestValue . "<br>";	
	if( $closestValue < 100 ){
		if( abs($xDir) > abs($yDir) ){ // horizontal
			if( $xDir < 0 && $left){
				$targetLeft += $dirWeight;
			} else if($right) {
				$targetRight += $dirWeight;
			}
		} else {			// Vertical
			if( $yDir < 0 && $up ){
				$targetUp += $dirWeight;
			} else if($down) {
				$targetDown += $dirWeight;
			}
		}
	}


	//
	// Linear Free Space Target, Go in direction of open space. Avoid being trapped.	
	//
	// This will fail if there is a way out and it is tricked into a cave.
	$vision = 5;
	$spaceWeight = 1;
	$leftSpace = 0;	
	for($i = 1; $i < $vision + 1; $i++){
		if(isSpaceEmpty( $state, $x - $i, $y ) ){
			$leftSpace++;
		}		
	}
	$upSpace = 0;
        for($i = 1; $i < $vision + 1; $i++){
                if(isSpaceEmpty( $state, $x, $y - $i ) ){
                        $upSpace++;
                }
        }		
	$rightSpace = 0;
        for($i = 1; $i < $vision + 1; $i++){
                if(isSpaceEmpty( $state, $x + $i, $y ) ){
                        $rightSpace++;
                }
        }	
	$downSpace = 0;
        for($i = 1; $i < $vision + 1; $i++){
                if(isSpaceEmpty( $state, $x, $y + $i ) ){
                        $downSpace++;
                }
        }
	// Sort best direction	
	$directions = array( 'left' => $leftSpace, 'up' => $upSpace, 'right' => $rightSpace, 'down' => $downSpace );
	arsort($directions);
	reset($directions);
	$bestKey = key($directions);
	$bestValue = $directions[$bestKey];
	asort($directions);
        reset($directions);
        $worstKey = key($directions);
        $worstValue = $directions[$worstKey];	
	//echo " best " . $bestKey . " v " . $bestValue . "   ----- worst " . $worstKey. " v " .$worstValue. "<br>"; 
	if($bestValue > 0 && $bestValue > $worstValue){
		// If in closed space increase spaceWeight. 

		if($bestKey == 'left' && $left){
			$targetLeft += $spaceWeight;
		}
		if($bestKey == 'up' && $up){
			$targetUp += $spaceWeight;
		}
		if($bestKey == 'right' && $right){
                        $targetRight += $spaceWeight;
                }	
		if($bestKey == 'down' && $down){
                        $targetDown += $spaceWeight;
                }	
	}
	

	//
	// Flood fill free space target. If there is more free space in one direction and a path to it go.
	//
	$fillWeight = 2;
	$spaces = array();
	$checkPosX = $x - 1;
        $checkPosY = $y;	
	$leftFill = floodFill( $state, $checkPosX, $checkPosY, 0, $spaces );
	$spaces = array();
        $checkPosX = $x;
        $checkPosY = $y - 1;
        $upFill = floodFill( $state, $checkPosX, $checkPosY, 1, $spaces );
	$spaces = array();
        $checkPosX = $x + 1;
        $checkPosY = $y;
        $rightFill = floodFill( $state, $checkPosX, $checkPosY, 2, $spaces );
	$spaces = array();
        $checkPosX = $x;
        $checkPosY = $y + 1;
        $downFill = floodFill( $state, $checkPosX, $checkPosY, 3, $spaces );
	$avoidLeft = false;
	$avoidUp = false;
	$avoidRight = false;
	$avoidDown = false; 
	$snakeLength = count($state['snakes'][$s]['tails']) + 1;
	if( $leftFill > 0 && $leftFill <= count($state['snakes'][$s]['tails']) * 2 ){ // Is there enough space to the left to fit the snake.
		//$fillWeight = 50; 
		$avoidLeft = true;
	}
	if( $upFull > 0 && $upFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidUp = true;
	} 
	if( $rightFill > 0 && $rightFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidRight = true;
	}
	if( $downFill > 0 && $downFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidDown = true;
	}

	if ($left && ($leftFill > $snakeLength*2 && ( $avoidUp || $avoidRight || $avoidDown )) ){
		$targetLeft += 50;
		echo ".";
	}
	if ($up && ($upFill > $snakeLength*2 && ( $avoidLeft || $avoidRight || $avoidDown )) ){
                $targetUp += 50;
        	echo ".";
	}
	if ($right && ($rightFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidDown )) ){
                $targetRight += 50;
        	echo ".";
	}	
	if ($down && ($downFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidRight )) ){
                $targetDown += 50;
        	echo ".";
	}
	
	$directions = array( 'left' => $leftFill, 'up' => $upFill, 'right' => $rightFill, 'down' => $downFill );
        arsort($directions);
        reset($directions);
        $bestKey = key($directions);
        $bestValue = $directions[$bestKey];
        asort($directions);
        reset($directions);
        $worstKey = key($directions);
        $worstValue = $directions[$worstKey];
        //echo " best " . $bestKey . " v " . $bestValue . "   ----- worst " . $worstKey. " v " .$worstValue. "<br>"; 
        if($bestValue > 0 && $bestValue > $worstValue){
                if($bestKey == 'left' && $left){
                        $targetLeft += $fillWeight;
                }
                if($bestKey == 'up' && $up){
                        $targetUp += $fillWeight;
                }
                if($bestKey == 'right' && $right){
                        $targetRight += $fillWeight;
                }
                if($bestKey == 'down' && $down){
                        $targetDown += $fillWeight;
                }
        }
	
 
	//echo "Fill  $s    x: $checkPosX y: $checkPosY   l: " . $leftFill  . " up $upFill  right: $rightFill  down: $downFill <br>";
	


	// 
	// Update position based on data collected.
	//

	// Head in direction of best target if it is > 0 and > worst target. 
	//I.e. if they are all the same skip and choose randomly later.
	// IDEA: if we can't go in one direction we should give higher preference to allowable dirs
	$targets = array( 'left' => $targetLeft, 'up' => $targetUp, 'right' => $targetRight, 'down' => $targetDown );
	arsort($targets);
        reset($targets);
        $bestKey = key($targets);
        $bestValue = $targets[$bestKey];
        asort($targets);
        reset($targets);
        $worstKey = key($targets);
        $worstValue = $targets[$worstKey];
	//echo " Target best " . $bestKey . " v " . $bestValue . "   ----- worst " . $worstKey. " v " .$worstValue. "<br>";	
	if($bestValue > 0 && $bestValue > $worstValue){
		if($bestKey == 'left' && $left){
			return 0; // Go left
		}	
		if($bestKey == 'up' && $up){
			return 1; // Go up
		}
		if($bestKey == 'right' && $right){
			return 2; // Go Right
		}
		if($bestKey == 'down' && $down){
			return 3; // Go Down
		}
	}

	// Chose a random free space. This is a fallback
	for($i = 0; $i < 10; $i++ ){ // Bit of a hack
		$dir = rand(0, 3);
		if($dir == 0 && $left){
			return 0;
		} 
		if($dir == 1 && $up){
			return 1;
		}
		if($dir == 2 && $right){
			return 2;
		}
		if($dir == 3 && $down){
			return 3;
		}
	}
	if($left){return 0;} if($up){return 1;} if($right){return 2;} if($down){return 3;}
	if( rand(0, 1) == 1 ){return 1;} // Go up
	return 3; // No other option? Just go down town. Thats what I would do.
}


/**
* floodFill
*
* Description: Check tiles in one direction to see how many open are connected.
*/
function floodFill( $state, $checkPosX, $checkPosY, $direction, &$spaces, $depth = 0 ){
	$directional = false;
	if($depth > 14){
		return 0;
	}
	$oldCheckPosX = $checkPosX;
	$oldCheckPosY = $checkPosY;
	$fillCount = 0;
	$key = $checkPosX . '_' . $checkPosY;
		if(array_key_exists($key, $spaces)){
			return 0;
		}

		$isEmpty = false;
		if( isSpaceEmpty($state, $checkPosX, $checkPosY) ){
			$fillCount++;
			$isEmpty = true;
		}
		$spaces[$key] = array('open' => $isEmpty, 'x' => $checkPosX, 'y' => $checkPosY);
		//echo " scan ". $key. "  o: $isEmpty    d $depth    <br>";	
		// Find next pos to scan 
		$i = 0;
		//echo " . " . "  " . $i . " : " . count($spaces ) . "<br>";
		if( $spaces[$key]['open'] == true ){
			// Left
			if($direction != 2 || !$directional ){
				$tx = $spaces[$key]['x'] - 1;
				$ty = $spaces[$key]['y'];
				$tKey = $tx . '_' . $ty; 
				if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
					$checkPosX = $tx;
					$checkPosY = $ty;
					$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
				}
			}
			// Up
			if($direction != 3 || !$directional){
				$tx = $spaces[$key]['x'];
				$ty = $spaces[$key]['y'] - 1;  
				$tKey = $tx . '_' . $ty;              
				if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
					$checkPosX = $tx;
					$checkPosY = $ty;
					$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
				}		 
			}			
			// Right
			if($direction != 0 || !$directional){
				$tx = $spaces[$key]['x'] + 1;
                                $ty = $spaces[$key]['y'];
                                $tKey = $tx . '_' . $ty;
                                if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
                                        $checkPosX = $tx;
                                        $checkPosY = $ty;
                                        $fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
                                }
			}	
			
			// Down
			if($direction != 1 || !$directional){
				$tx = $spaces[$key]['x'];
				$ty = $spaces[$key]['y'] + 1;
				$tKey = $tx . '_' . $ty;
				if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
					$checkPosX = $tx;
					$checkPosY = $ty;
					$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
				}		
			}
		}
	
	return $fillCount;
}

function advanceState( $gameState ){
	global $tick_cache;
	unset($tick_cache);
	$tick_cache = array();
	$state = json_decode( $gameState, true );
	$state['ticks'] = (int)($state['ticks']) + 1;		

	// advance snakes position, 
	for( $s = 0;  $s < count($state['snakes']); $s++ ){
		if( $state['snakes'][$s]['alive'] == false){
			continue;
		}
		$state['snakes'][$s]['health'] = $state['snakes'][$s]['health'] - 1; 
		if( $state['snakes'][$s]['health'] < 0 ){
			$state['snakes'][$s]['health'] = 0;
			$state['snakes'][$s]['alive'] = false;	
		}

		$pastPos = array('x' => $state['snakes'][$s]['x'], 'y' => $state['snakes'][$s]['y'] );


		$dir = getDirection( $state, $s );
		if($dir == 0){ // Left
			$state['snakes'][$s]['x'] =  $state['snakes'][$s]['x'] -1;		
		} else if($dir == 1){ // Up
			$state['snakes'][$s]['y'] =  $state['snakes'][$s]['y'] -1;
		} else if($dir == 2){ // Right
                        $state['snakes'][$s]['x'] =  $state['snakes'][$s]['x'] +1;
                } else if($dir == 3){ // Down 
			$state['snakes'][$s]['y'] =  $state['snakes'][$s]['y'] +1;	
		}

		// Boundary
		if( $state['snakes'][$s]['x'] < 0 ||
			$state['snakes'][$s]['x'] > ($state['board_width'] - 1) || 
			$state['snakes'][$s]['y'] < 0 ||
			$state['snakes'][$s]['y'] > ($state['board_height'] - 1) 
		){
			$state['snakes'][$s]['alive'] = false; 	
		}

		// Collision with self
		for( $t = 0; $t < count($state['snakes'][$s]['tails']); $t++ ){
			if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x'] &&
				$state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y']
			){
				$state['snakes'][$s]['alive'] = false;
			}
		}


		// Collision with other except for head and having a longer length.
		for( $c = 0;  $c < count($state['snakes']); $c++ ){
			if($c != $s && 
				$state['snakes'][$s]['alive'] == true && 
				$state['snakes'][$c]['alive'] == true 
			){
				// Collide with another snake Hhead
				if( $state['snakes'][$s]['x'] == $state['snakes'][$c]['x'] &&
					$state['snakes'][$s]['y'] == $state['snakes'][$c]['y']  &&
					count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) 
				){
					$state['snakes'][$s]['alive'] = false; // S looses head on collision
					//echo "*** Head On ***";
				}

				// Collide with another snake tail
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                        		if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] && 
                                		$state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']  
					){
                                		$state['snakes'][$s]['alive'] = false;
						//$t = count($state['snakes'][$c]['tails']); // end
						//echo "*** Collide Tail " . $s. " t " . $t. " ***";
                        		}
                		}					
			}
		}
			

		// Move tail pieces up
		$prevTailPiece = null;
		for( $t = count($state['snakes'][$s]['tails']) - 1; $t >= 0; $t-- ){
			if($t == 0){
				$state['snakes'][$s]['tails'][$t]['x'] = $pastPos['x'];
                                $state['snakes'][$s]['tails'][$t]['y'] = $pastPos['y'];	
			} else {
				$state['snakes'][$s]['tails'][$t]['x'] = $state['snakes'][$s]['tails'][$t-1]['x'];
				$state['snakes'][$s]['tails'][$t]['y'] = $state['snakes'][$s]['tails'][$t-1]['y'];		
			}
		}

		// Eat food
		for( $f = 0; $f < count($state['foods']) - 1; $f++ ){
			if( $state['snakes'][$s]['x'] == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] == $state['foods'][$f]['y'] && 
 				$state['foods'][$f]['active'] == true	
			){
				$state['snakes'][$s]['health'] = $state['snakes'][$s]['health'] + 30; // magic number
				if($state['snakes'][$s]['health'] > 100){
					$state['snakes'][$s]['health'] = 100;
				}
				// Add length
				$state['snakes'][$s]['tails'][count($state['snakes'][$s]['tails']) ] = array('x' => $pastPos['x'], 'y' => $pastPos['y']);
				// Eat food
				$state['foods'][$f]['active'] = false;			
			}
		}
	
	}	

	// Add food
	// TODO: don't add on top of an ocupied space.
	if( $state['ticks'] % 4 == 0 ){
		//echo " ADD FOOD ";
		$foods = $state['foods'];
		$foods[count($state['foods'])]['active'] = true;
		$foods[count($state['foods'])]['x'] = rand(0, $state['board_width'] - 1);
                $foods[count($state['foods'])]['y'] = rand(0, $state['board_height'] - 1);
		$state['foods'] = $foods;		
	}

	$gameState = json_encode( (array)$state );
	return $gameState; 
}

function isRangeEmpty( $state, $x1, $y1, $x2, $y2 ){
	$count = 0;
	$minX = min($x1, $x2);
	$minY = min($y1, $y2);
	$maxX = max($x1, $x2);
	$maxY = max($y1, $y2);
	for( $x = $minX + 1; $x < $maxX; $x++ ){
		for( $y = $minY + 1; $y < $maxY; $y++ ){		
			if( !isSpaceEmpty($state, $x, $y) ){
				$count++;
			}			
		}
	}	
	return $count;
}

function isSpaceOnBoard( $state, $x , $y){

	

	if($x < 0){
                return false;
        }
        if($y < 0){
                return false;
        }
        if($x > ($state['board_width'] - 1)){
                return false;
        }
        if($y > ($state['board_height'] - 1)){
                return false;
        }
	return true;	
}

function isSpaceEmpty( $state, $x, $y ){
	global $tick_cache;

	$key = $x . "_" . $y;
	if( array_key_exists($key, $tick_cache) ){
		//echo "-";
		return $tick_cache[$key];
	} else {
		//echo "+";
	}

	if($x < 0){
		return false;
	}
	if($y < 0){
		return false;
	}
	if($x > ($state['board_width'] - 1)){
		return false;
	}
	if($y > ($state['board_height'] - 1)){
		return false;
	}
	for( $s = 0; $s < count($state['snakes']); $s++ ){
                if( $state['snakes'][$s]['x'] == $x && 
			$state['snakes'][$s]['y'] == $y && 
			$state['snakes'][$s]['alive'] == true
		){
			$tick_cache[$key] = false;
			return false;	
		}
		for( $t = 0; $t < count( $state['snakes'][$s]['tails']); $t++ ){
			if( $x == $state['snakes'][$s]['tails'][$t]['x'] &&
				$y == $state['snakes'][$s]['tails'][$t]['y'] &&
				$state['snakes'][$s]['alive'] == true
                	){
				$tick_cache[$key] = false;
				return false;
			}			
		}	
	}
	$tick_cache[$key] = true;	
	return true;
}

function snakesAlive( $gameState ){
	$state = json_decode( $gameState, true );
	$count = 0;	
	for( $s = 0; $s < count($state['snakes']); $s++ ){
                if( $state['snakes'][$s]['alive'] == true){	
			$count++;
		}
	}
	return $count;
}

function setGameState( $gameState ){
	global $STATE_FILE;
	//echo "Saving game state: " . $gameState . "<br>";
	//$r = file_put_contents($STATE_FILE, $gameState);
	//if($r === false){
		//echo "ERROR writing file: ".$STATE_FILE." <br>";
	//}
		
	//echo "r " . $r . " " . $gameState . "<br>" ;
	$_SESSION['game_state'] = $gameState;
	//echo "SET " . $gameState . "<br>";
}

function getGameState(){
	global $STATE_FILE;	
	//$state = file_get_contents($STATE_FILE);
	//if($state == ''){
		$state = $_SESSION['game_state'];
	//}
	//echo " GET " . $state . "<br>";
	return $state;
}

function getBoard( $gameState ){
	global $snake_colours;
	$state = json_decode( $gameState, true );	
	$game = "<table cellspacing='1' cellpadding='1' bgcolor='#CCCCCC' border=0 style='table-layout:fixed; overflow:hidden; white-space: nowrap; '>";
	for( $h = 0; $h < $state['board_height']; $h++ ){
		$game .= "<tr>";
		for( $w = 0; $w < $state['board_width']; $w++ ){
			$cell = '';
			$cellColor = '#FFFFFF';

			for( $f = 0; $f < count($state['foods']) - 1; $f++ ){
				if( $w == $state['foods'][$f]['x'] && $h == $state['foods'][$f]['y'] ){
                                        if($state['foods'][$f]['active'] == true){
						//$game .= " *";
						$cell .= "*";
					}
                                }	
			}
			for( $s = 0; $s < count($state['snakes']); $s++ ){
				if( $w == $state['snakes'][$s]['x'] && 
					$h == $state['snakes'][$s]['y'] 
					&& 
					$state['snakes'][$s]['alive'] == true 
				){
					if( $state['snakes'][$s]['alive'] == true  ){
						$cellColor = $snake_colours[$s];
						$cell .= " <b><font color='#22BB22'>".$s."</font></b> <font size='1'>". $state['snakes'][$s]['health'] ."</font> ";
					} else {
						$cell .= " <b><font color='#FF0000'>S</font></b> ";
					}
				}
				
				for( $t = 0; $t < count( $state['snakes'][$s]['tails']); $t++ ){
					if( $w == $state['snakes'][$s]['tails'][$t]['x'] && 
						$h == $state['snakes'][$s]['tails'][$t]['y'] && 
						$state['snakes'][$s]['alive'] == true
					){
						$cellColor = $snake_colours[$s];
                                        	//$cell .= " <font color='#888888'>s</font>";
                                	}	
				}
			}	
	
			$game .= "<td width='26' height='26' bgcolor='".$cellColor."' style='  ' >";	
			$game .= $cell;
			$game .= "</td>";
		}
		$game .= "</tr>";
	}
	$game .= "</table>";
	return $game;
}


?>
