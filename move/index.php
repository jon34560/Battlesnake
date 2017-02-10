<?php


//echo var_dump($_POST);
//echo "Req" . http_get_request_body();
echo "R" . var_dump($_REQUEST) . "___";

echo "P" . var_dump($_POST) . "_";

$data = array( 'move' => 'up', 'taunt' => 'Everyone wins!' );

$response = json_encode( $data );
echo $response;

?>
