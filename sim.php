<?php
//session_start();

$STATE_FILE = "/var/www/magnite.org/game_state.dat";

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
	for($f = 0; $f < 10; $f++){
		$foods[$f]['active'] = true;	
		$foods[$f]['x'] = rand(0, $state['board_width'] - 1);
		$foods[$f]['y'] = rand(0, $state['board_height'] - 1); 
	}
	$state['foods'] = $foods;
 
	$gameState = json_encode( (array)$state );
	return $gameState;
}

function getDirection( $state, $s ){
	$left = true;
	$up = true;
	$right = true;
	$down = true;

	$targetLeft = false;
	$targetUp = false;
	$targetRight = false;
	$targetDown = false;

	$snake = $state['snakes'][$s];

	// Avoid collision with edge
	$x = $state['snakes'][$s]['x'];
	$y = $state['snakes'][$s]['y'];
	//echo " x " . $x . " " . $y . " ";
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
                                // Collide with another snake head
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
                                        if( $state['snakes'][$c]['tails'][$t]['x'] - 1 == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']
                                        ){
						$left = false;		
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x']  == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] - 1 == $state['snakes'][$s]['y']
                                        ){
                                                $up = false;
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x'] + 1 == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$s]['y']
                                        ){
                                                $right = false;
                                        }
                                }
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                                        if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$s]['x'] &&
                                                $state['snakes'][$c]['tails'][$t]['y'] + 1 == $state['snakes'][$s]['y']
                                        ){
                                                $down = false;
                                        }
                                }
                        }
	}
	
	// Goal, head in direction of 1) free space and 2) food 

	// If closer to food than anyone else go for it.

	// Proof of concept
	$vision = 1;
	if($state['snakes'][$s]['health'] < 80){
                $vision = 4;
        }
	if($state['snakes'][$s]['health'] < 70){
                $vision = 6;
        }
	if($state['snakes'][$s]['health'] < 60){
                $vision = 10;
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
				$targetLeft = true;
			}
			if( $state['snakes'][$s]['x'] == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] - $v == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				$targetUp = true;
			}	
			if( $state['snakes'][$s]['x'] + $v == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				$targetRight = true;
			} 
			if( $state['snakes'][$s]['x']  == $state['foods'][$f]['x'] && 
				$state['snakes'][$s]['y'] + $v == $state['foods'][$f]['y'] && 
				$state['foods'][$f]['active'] == true
			){
				$targetDown = true;
			}	
		}	
	}


	if($targetLeft && $left){
		return 0;
	}	
	if($targetUp && $up){
		return 1;
	}
	if($targetRight && $right){
		return 2;
	}
	if($targetDown && $down){
		return 3;
	}
	for($i = 0; $i < 10; $i++ ){
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
	return 3; // No other option? Just go down town.
}

function advanceState( $gameState ){
	$state = json_decode( $gameState, true );
	$state['ticks'] = (int)($state['ticks']) + 1;		

	// advance snakes, 
	// todo call function for directions
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
			$state['snakes'][$s]['y'] > ($state['board_height'] - 1) ){
			$state['snakes'][$s]['alive'] = false; 	
		}

		// Collision with self
		for( $t = 0; $t < count($state['snakes'][$s]['tails']); $t++ ){
			if( $state['snakes'][$s]['tails'][$t]['x'] == $state['snakes'][$s]['x'] &&
				$state['snakes'][$s]['tails'][$t]['y'] == $state['snakes'][$s]['y']  ){
				$state['snakes'][$s]['alive'] = false;
			}
		}


		// Collision with other except for head and having a longer length.
		// TODO
		for( $c = 0;  $c < count($state['snakes']); $c++ ){
			if($c != $s && 
				$state['snakes'][$s]['alive'] == true && 
				$state['snakes'][$c]['alive'] == true 
			){
				// Collide with another snake Hhead
				if( $state['snakes'][$s]['x'] == $state['snakes'][$c]['x'] &&
					$state['snakes'][$s]['y'] == $state['snakes'][$c]['y']  &&
					count($state['snakes'][$c]['tails']) >= count( $state['snakes'][$s]['tails']) ){
					$state['snakes'][$s]['alive'] = false; // S looses head on collision
					//echo "*** Head On ***";
				}

				// Collide with another snake tail
				for( $t = 0; $t < count($state['snakes'][$c]['tails']); $t++ ){
                        		if( $state['snakes'][$c]['tails'][$t]['x'] == $state['snakes'][$c]['x'] &&
                                		$state['snakes'][$c]['tails'][$t]['y'] == $state['snakes'][$c]['y']  
					){
                                		$state['snakes'][$s]['alive'] = false;
						$t = count($state['snakes'][$c]['tails']); // end
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
	if( $state['ticks'] % 5 == 0 ){
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

function isSpaceEmpty( $gameState, $x, $y ){

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
	$state = json_decode( $gameState, true );	
	$game = "<table cellspacing='1' cellpadding='1' bgcolor='#CCCCCC' border=0>";
	for( $h = 0; $h < $state['board_height']; $h++ ){
		$game .= "<tr>";
		for( $w = 0; $w < $state['board_width']; $w++ ){
			$game .= "<td width='26' height='26' bgcolor='#FFFFFF'>";

			for( $f = 0; $f < count($state['foods']) - 1; $f++ ){
				if( $w == $state['foods'][$f]['x'] && $h == $state['foods'][$f]['y'] ){
                                        if($state['foods'][$f]['active'] == true){
						$game .= " *";
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
						$game .= " <b><font color='#22BB22'>".$s."</font></b> <font size='1'>". $state['snakes'][$s]['health'] ."</font> ";
					} else {
						$game .= " <b><font color='#FF0000'>S</font></b> ";
					}
				}
				
				for( $t = 0; $t < count( $state['snakes'][$s]['tails']); $t++ ){
					if( $w == $state['snakes'][$s]['tails'][$t]['x'] && 
						$h == $state['snakes'][$s]['tails'][$t]['y'] && 
						$state['snakes'][$s]['alive'] == true
					){
                                        	$game .= "s";
                                	}	
				}
			}	

			$game .= "</td>";
		}
		$game .= "</tr>";
	}
	$game .= "</table>";
	return $game;
}


?>
