<?php


$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$year = '2016';
if($year == '2016'){

	$game = $data['game'];	

	echo "game " . $game . ", ";
	echo " data " . $data ;
}



$data = array( 'move' => 'up', 'taunt' => 'Everyone wins!' );

$response = json_encode( $data );
echo $response;

?>
