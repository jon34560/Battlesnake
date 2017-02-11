<?php
session_start();
include '../sim.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$direction = 'down';

$year = '2016';
if($year == '2016'){

	$game = $data['game'];	
	$width = $data['width'];
	$height = $data['height']; 
	//echo "game " . $game . ", ";
	//echo " data " . $data ;

	$state = loadGameState ( $data );
	
	$s = $state['s'];
	//echo " s: " . $s;
	
	$dir = getDirection( $state, $s, false );	
	//echo "dir " . $dir ;
	if($dir == 0){
		$direction = 'left';
	}
	if($dir == 1){
                $direction = 'up';
        }
	if($dir == 2){
                $direction = 'right';
        }
	if($dir == 3){
                $direction = 'down';
        }	
}



$data = array( 'move' => $direction, 'taunt' => 'Everyone wins!' );

$response = json_encode( $data );
echo $response;

?>
