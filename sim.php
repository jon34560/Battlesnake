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
		$snakes[$i]['log'] = ' ';
		$snakes[$i]['reason'] = ' ';
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

// 2016 API
function loadGameState ( $data ){
	$state = array();
	$state['board_width'] = $data['width'];
        $state['board_height'] = $data['height'];
	$state['ticks'] = $data['turn'];
	$state['s'] = '';

	$snakes = array();
	for($s = 0; $s < count( $data['snakes'] ); $s++){
		// id, name status message taunt age health, coords []  kills  food gold
		$tails = array();		
		for( $p = 0; $p < count($data['snakes'][$s]['coords']); $p++ ){
			$x = $data['snakes'][$s]['coords'][$p][0];
			$y = $data['snakes'][$s]['coords'][$p][1]; 
			//echo " coord " . $x . " " . $y  ;
			if($p == 0){
				$snakes[$s]['x'] = $x;
				$snakes[$s]['y'] = $y;
			} else {
				$tails[ ($p - 1) ]['x'] = $x;
				$tails[ ($p - 1) ]['y'] = $y;	
			}
		}
		$snakes[$s]['health'] = $data['snakes'][$s]['health'];	
		$snakes[$s]['alive'] = ( $data['snakes'][$s]['health'] > 0 ? true : false );
		$snakes[$s]['log'] = '';
		$snakes[$s]['reason'] = '';		
		$snakes[$s]['tails'] = $tails;

		// Identify my snake
		if($data['snakes'][$s]['name'] == 'Jon' ){
			$state['s'] = $s;
		}	
	}	
	$state['snakes'] = $snakes;

	$foods = array();
	for($f = 0; $f < count( $data['food'] ); $f++){
		$x = $data['food'][$f][0];
		$y = $data['food'][$f][1];
		//echo " " . $x .  " " . $y ;
		$foods[$f]['x'] = $x;
		$foods[$f]['y'] = $y;
		$foods[$f]['active'] = true;
	}
	$state['foods'] = $foods;	


	$gameState = json_encode( (array)$state );
	//echo " json " . $gameState;
	return $gameState;	
}

function getDirection( & $state, $s, $debug = true ){
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

	if($debug){	
		$state['snakes'][$s]['reason'] .= 'Allowed   '.
			' l: '. ($left?'<b><font color=green><</font></b>':'<font color=red><</font>').
			' u: '.($up?'<b><font color=green>/\</font></b>':'<font color=red>/\</font>').
			' r: '.($right?'<b><font color=green>></font></b>':'<font color=red>></font>').
			' d: '.($down?'<b><font color=green>\/</font></b>':'<font color=red>\/</font>').' <br>';
	}

	
	// Goal, head in direction of 1) free space and 2) food 

	// If closer to food than anyone else go for it.
	// TODO

	// Linear Food Target, Proof of concept look farther the hungrier the snake is
	// Only target food if there is a clear path.
	$vision = 1;
	$weight = 10;
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
					$targetLeft += $weight;
					if($debug){
						$state['snakes'][$s]['reason'] .= 'Linear Food Left '.$weight.' <br>';
					}
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
					$targetUp += $weight;
					if($debug){
						$state['snakes'][$s]['reason'] .= 'Linear Food Up  '.$weight.' <br>';
					}
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
					$targetRight += $weight;
					if($debug){
						$state['snakes'][$s]['reason'] .= 'Linear Food Right  '.$weight.' <br>';
					}
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
					$targetDown += $weight;
					if($debug){
						$state['snakes'][$s]['reason'] .= 'Linear Food Down  '.$weight.' <br>';
					}
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
				if($debug){
					$state['snakes'][$s]['reason'] .= 'Angle Food Left '.$dirWeight.' <br>';
				}
			} else if($right) {
				$targetRight += $dirWeight;
				if($debug){
					$state['snakes'][$s]['reason'] .= 'Angle Food Right '.$dirWeight.' <br>';
				}
			}
		} else {			// Vertical
			if( $yDir < 0 && $up ){
				$targetUp += $dirWeight;
				if($debug){
					$state['snakes'][$s]['reason'] .= 'Angle Food Up  '.$dirWeight.' <br>';
				}
			} else if($down) {
				$targetDown += $dirWeight;
				if($debug){
					$state['snakes'][$s]['reason'] .= 'Angle Food Down  '.$dirWeight.' <br>';
				}
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
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Linear Space Left '.$spaceWeight.' <br>';
			}
		}
		if($bestKey == 'up' && $up){
			$targetUp += $spaceWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Linear Space Up '.$spaceWeight.' <br>';
			}
		}
		if($bestKey == 'right' && $right){
                        $targetRight += $spaceWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Linear Space Right '.$spaceWeight.' <br>';
                	}
		}	
		if($bestKey == 'down' && $down){
                        $targetDown += $spaceWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Linear Space Down '.$spaceWeight.' <br>';
                	}
		}	
	}
	

	//
	// Flood fill free space target. If there is more free space in one direction and a path to it go.
	//
	$fillWeight = 12; // 2; // 12 is better than 2
	$leftSpaces = array();
	$checkPosX = $x - 1;
        $checkPosY = $y;	
	$leftFill = floodFill( $state, $checkPosX, $checkPosY, 0, $leftSpaces );
	$upSpaces = array();
        $checkPosX = $x;
        $checkPosY = $y - 1;
        $upFill = floodFill( $state, $checkPosX, $checkPosY, 1, $upSpaces );
	$rightSpaces = array();
        $checkPosX = $x + 1;
        $checkPosY = $y;
        $rightFill = floodFill( $state, $checkPosX, $checkPosY, 2, $rightSpaces );
	$downSpaces = array();
        $checkPosX = $x;
        $checkPosY = $y + 1;
        $downFill = floodFill( $state, $checkPosX, $checkPosY, 3, $downSpaces );
	$avoidLeft = false;
	$avoidUp = false;
	$avoidRight = false;
	$avoidDown = false; 
	$snakeLength = count($state['snakes'][$s]['tails']) + 1;
	if($debug){
		$state['snakes'][$s]['reason'] .= 'Flood Fill l:' . $leftFill . ' u:' . $upFill . ' r:'. $rightFill . ' d:' . $downFill . '<br>';
	}
	if( $leftFill > 0 && $leftFill <= count($state['snakes'][$s]['tails']) * 2 ){ // Is there enough space to the left to fit the snake.
		//$fillWeight = 50; 
		$avoidLeft = true;
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Left <br>';
		}
	}
	if( $upFull > 0 && $upFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidUp = true;
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Up <br>';
		}
	} 
	if( $rightFill > 0 && $rightFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidRight = true;
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Right <br>';
		}
	}
	if( $downFill > 0 && $downFill <= count($state['snakes'][$s]['tails']) * 2 ){
		//$fillWeight = 50;
		$avoidDown = true;
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Down <br>';
		}
	}

	if ($left && ($leftFill > $snakeLength*2 && ( $avoidUp || $avoidRight || $avoidDown )) ){
		$targetLeft += 50;
		//echo ".";
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Target Left Panic <br>';
		}
	}
	if ($up && ($upFill > $snakeLength*2 && ( $avoidLeft || $avoidRight || $avoidDown )) ){
                $targetUp += 50;
        	//echo ".";
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Target Up Panic <br>';
		}
	}
	if ($right && ($rightFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidDown )) ){
                $targetRight += 50;
        	//echo ".";
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Target Right Panic <br>';	
		}
	}	
	if ($down && ($downFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidRight )) ){
                $targetDown += 50;
        	//echo ".";
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Flood Fill Target Down Panic <br>';
		}
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
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Left '.$fillWeight.' <br>';
                	}
		}
                if($bestKey == 'up' && $up){
                        $targetUp += $fillWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Up '.$fillWeight.' <br>';
                	}
		}
                if($bestKey == 'right' && $right){
                        $targetRight += $fillWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Right '.$fillWeight.' <br>';
                	}
		}
                if($bestKey == 'down' && $down){
                        $targetDown += $fillWeight;
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Down '.$fillWeight.' <br>';
                	}
		}
        }


	// Anticipate intent of other bots.
	// 1) If an opponent head is one position from blocking a path, avoid direction. 
	// $leftSpaces   analyse path to determine how narrow is is and then if heads are close on edge.
	$keys = array_keys($leftSpaces);
	for($i = 0; $i < count($keys); $i++){
		$space = $leftSpaces[ $keys[$i] ];
		//if($space['open'] == true){
			//$distance = $space['x']
			//echo "." . $space['x'];
		//}
		if($space['open'] == false){
			if( isSpaceSnakeHead( $state, $space['x'], $space['y']) && $x - 1 == $space['x'] ){ // One spot over, head is close.
				echo " " . $s . " ( ".$space['x'] . ", ". $space['y'] . " ) ";
			}			
		}	
	}


	// Murder Bot, Cut off other bots 
	// 1) If an opponent is confined in a space and you can cut him off and escape do so.
	


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
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Left  <br>';
			}
			return 0; // Go left
		}	
		if($bestKey == 'up' && $up){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Up  <br>';
			}
			return 1; // Go up
		}
		if($bestKey == 'right' && $right){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Right  <br>';
			}
			return 2; // Go Right
		}
		if($bestKey == 'down' && $down){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Down  <br>';
			}
			return 3; // Go Down
		}
	}

	// Chose a random free space. This is a fallback
	for($i = 0; $i < 10; $i++ ){ // Bit of a hack
		$dir = rand(0, 3);
		if($dir == 0 && $left){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Random Left  <br>';
			}
			return 0;
		} 
		if($dir == 1 && $up){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Random Up  <br>';
			}
			return 1;
		}
		if($dir == 2 && $right){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Random Right  <br>';
			}
			return 2;
		}
		if($dir == 3 && $down){
			if($debug){
				$state['snakes'][$s]['reason'] .= 'Choose Random Down  <br>';
			}
			return 3;
		}
	}
	if($left){
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Go Left  <br>';	
		}
		return 0;
	} 
	if($up){
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Go Up  <br>';
		}
		return 1;
	} 
	if($right){
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Go Right  <br>';
		}
		return 2;
	} 
	if($down){
		if($debug){
			$state['snakes'][$s]['reason'] .= 'Go Down  <br>';
		}
		return 3;
	}
	if( rand(0, 1) == 1 ){return 1;} // Go up
	return 3; // No other option? Just go down town. Thats what I would do.
}


/**
* floodFill
*
* Description: Check tiles in one direction to see how many open are connected.
*
* Optimization: Don't search farther than we have to... 
*/
function floodFill( $state, $checkPosX, $checkPosY, $direction, &$spaces, $depth = 0 ){
	$directional = false;
	if($depth > 18){
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
			$state['snakes'][$s]['log'] = 'Starvation.';	
		}

		$pastPos = array('x' => $state['snakes'][$s]['x'], 'y' => $state['snakes'][$s]['y'] );

		$state['snakes'][$s]['reason'] = ''; // Reset
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
			$state['snakes'][$s]['log'] = 'Collide Wall.';	
		}

		// Collision with self
		for( $t = 0; $t < count($state['snakes'][$s]['tails']); $t++ ){
			if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x'] &&
				$state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y']
			){
				$state['snakes'][$s]['alive'] = false;
				$state['snakes'][$s]['log'] = 'Collide Self.';
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
					$state['snakes'][$s]['log'] = 'Collide head on.';
				}

				// Collide with another snake tail
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                        		if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] && 
                                		$state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']  
					){
                                		$state['snakes'][$s]['alive'] = false;
						//$t = count($state['snakes'][$c]['tails']); // end
						//echo "*** Collide Tail " . $s. " t " . $t. " ***";
						$state['snakes'][$s]['log'] = 'Collide tail.';
                        		}
                		}					
			}
		}

		//$state['snakes'][$s]['log'] .= '.';			

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
	global $tick_cache;

	$key = "" . $x . "_" . $y . "b";
        /*
	if( array_key_exists($key, $tick_cache) ){ // Bad performance
                //echo "-";
                return $tick_cache[$key];
        } else {
                //echo "+";
        }
	*/
	$result = $tick_cache[$key];
	if($result == 't'){
		//echo ".";
		return true;
	} else if($result == 'f'){
		//echo ".";
		return false;
	} else {
		//echo "+";
	}

	if($x < 0){
		$tick_cache[$key] = 'f'; //false;
                return false;
        }
        if($y < 0){
		$tick_cache[$key] = 'f'; //false;
                return false;
        }
        if($x > ($state['board_width'] - 1)){
		$tick_cache[$key] = 'f'; //false;
                return false;
        }
        if($y > ($state['board_height'] - 1)){
		$tick_cache[$key] = 'f'; //false;
                return false;
        }
	$tick_cache[$key] = 't'; //true;
	return true;	
}

function isSpaceEmpty( $state, $x, $y ){
	global $tick_cache;

	$key = $x . "_" . $y . "e";
	//if( array_key_exists($key, $tick_cache) ){
		//echo "-";
	//	return $tick_cache[$key];
	//} else {
		//echo "+";
	//}

	$result = $tick_cache[$key];
	if($result == 't'){
		//echo ".";
		return true;
	} else if($result == 'f'){
		//echo ".";
		return false;
	} else {
		//echo "+";
	}
 

	if(!isSpaceOnBoard( $state, $x , $y)){
		$tick_cache[$key] = 'f'; // false;
		return false;	
	}

	for( $s = 0; $s < count($state['snakes']); $s++ ){
                if( $state['snakes'][$s]['x'] == $x && 
			$state['snakes'][$s]['y'] == $y && 
			$state['snakes'][$s]['alive'] == true
		){
			$tick_cache[$key] = 'f'; //false;
			return false;	
		}
		for( $t = 0; $t < count( $state['snakes'][$s]['tails']); $t++ ){
			if( $x == $state['snakes'][$s]['tails'][$t]['x'] &&
				$y == $state['snakes'][$s]['tails'][$t]['y'] &&
				$state['snakes'][$s]['alive'] == true
                	){
				$tick_cache[$key] = 'f'; // false;
				return false;
			}			
		}	
	}
	$tick_cache[$key] = 't'; //true;	
	return true;
}

function isSpaceSnakeHead( $state, $x, $y ){
	
	for( $s = 0; $s < count($state['snakes']); $s++ ){
                if($state['snakes'][$s]['alive'] == true){
                        if( $state['snakes'][$s]['x'] == $x &&
                                $state['snakes'][$s]['y'] == $y
			){

				return true;
			}
		}
	}
	return false;
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
