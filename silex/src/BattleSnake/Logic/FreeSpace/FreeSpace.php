<?php

/*
* FreeSpace - Free Space on board Logic functions
*/

namespace BattleSnake\Logic\FreeSpace;

class FreeSpace
{


	/**
	* linearFreeSpaceDetection
	*
	* Description: Go in direction of open space. Avoid being trapped.
	*	This will fail if there is a way out and it is tricked into a cave.
	*/
	public static function floodFillDetection($state, $decision_matix) {
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
				$decision_matix->incrementPreferedDirectionValue('left', $spaceWeight);

				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Linear Space Left '.$spaceWeight.' <br>';
				//}
			}
			if($bestKey == 'up' && $up){
				$targetUp += $spaceWeight;
				$decision_matix->incrementPreferedDirectionValue('up', $spaceWeight);

				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Linear Space Up '.$spaceWeight.' <br>';
				//}
			}
			if($bestKey == 'right' && $right){
				$targetRight += $spaceWeight;
				$decision_matix->incrementPreferedDirectionValue('right', $spaceWeight);

				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Linear Space Right '.$spaceWeight.' <br>';
				//}
			}	
			if($bestKey == 'down' && $down){
				$targetDown += $spaceWeight;
				$decision_matix->incrementPreferedDirectionValue('down', $spaceWeight);

				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Linear Space Down '.$spaceWeight.' <br>';
				//}
			}
		}	
	}


	/**
	* floodFillDetection
	*
	* Description: 
	*/
	public static function floodFillDetection($state, $decision_matix) {
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
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Left <br>';
			//}
		}
		if( $upFull > 0 && $upFill <= count($state['snakes'][$s]['tails']) * 2 ){
			//$fillWeight = 50;
			$avoidUp = true;
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Up <br>';
			//}
		} 
		if( $rightFill > 0 && $rightFill <= count($state['snakes'][$s]['tails']) * 2 ){
			//$fillWeight = 50;
			$avoidRight = true;
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Right <br>';
			//}
		}
		if( $downFill > 0 && $downFill <= count($state['snakes'][$s]['tails']) * 2 ){
			//$fillWeight = 50;
			$avoidDown = true;
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Avoid Down <br>';
			//}
		}
		if ($left && ($leftFill > $snakeLength*2 && ( $avoidUp || $avoidRight || $avoidDown )) ){
			$targetLeft += 50;
			//echo ".";
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Target Left Panic <br>';
			//}
		}
		if ($up && ($upFill > $snakeLength*2 && ( $avoidLeft || $avoidRight || $avoidDown )) ){
			$targetUp += 50;
			//echo ".";
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Target Up Panic <br>';
			//}
		}
		if ($right && ($rightFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidDown )) ){
			$targetRight += 50;
			//echo ".";
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Target Right Panic <br>';	
			//}
		}	
		if ($down && ($downFill > $snakeLength*2 && ( $avoidLeft || $avoidUp || $avoidRight )) ){
			$targetDown += 50;
			//echo ".";
			//if($debug){
			//	$state['snakes'][$s]['reason'] .= 'Flood Fill Target Down Panic <br>';
			//}
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
				$decision_matix->incrementPreferedDirectionValue('left', $fillWeight);
				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Left '.$fillWeight.' <br>';
				//}
			}
			if($bestKey == 'up' && $up){
				$targetUp += $fillWeight;
				$decision_matix->incrementPreferedDirectionValue('up', $fillWeight);
				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Up '.$fillWeight.' <br>';
                //}
			}
			if($bestKey == 'right' && $right){
				$targetRight += $fillWeight;
				$decision_matix->incrementPreferedDirectionValue('right', $fillWeight);
				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Right '.$fillWeight.' <br>';
                //}
			}
			if($bestKey == 'down' && $down){
				$targetDown += $fillWeight;
				$decision_matix->incrementPreferedDirectionValue('down', $fillWeight);
				//if($debug){
				//	$state['snakes'][$s]['reason'] .= 'Flood Fill Prefer Down '.$fillWeight.' <br>';
				//}
			}
        }

	}


	/**
	* floodFill
	*
	* Description: Check tiles in one direction to see how many open are connected.
	*
	* Optimization: Don't search farther than we have to... 
	*/
	function floodFill( $state, $checkPosX, $checkPosY, &$spaces, $depth = 0 ){
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
			$tx = $spaces[$key]['x'] - 1;
			$ty = $spaces[$key]['y'];
			$tKey = $tx . '_' . $ty; 
			if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
				$checkPosX = $tx;
				$checkPosY = $ty;
				$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
			}
			
			// Up
			$tx = $spaces[$key]['x'];
			$ty = $spaces[$key]['y'] - 1;  
			$tKey = $tx . '_' . $ty;              
			if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
				$checkPosX = $tx;
				$checkPosY = $ty;
				$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
			}		 
				
			// Right
			$tx = $spaces[$key]['x'] + 1;
			$ty = $spaces[$key]['y'];
			$tKey = $tx . '_' . $ty;
			if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
				$checkPosX = $tx;
				$checkPosY = $ty;
				$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
			}
			
			// Down
			$tx = $spaces[$key]['x'];
			$ty = $spaces[$key]['y'] + 1;
			$tKey = $tx . '_' . $ty;
			if( isSpaceOnBoard( $state, $tx , $ty) && !array_key_exists($tKey, $spaces) ){
				$checkPosX = $tx;
				$checkPosY = $ty;
				$fillCount += floodFill( $state, $checkPosX, $checkPosY, $direction, $spaces, $depth+1 );
			}		
		}
		return $fillCount;
	}

}
