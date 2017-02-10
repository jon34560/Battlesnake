<?php
session_start();
include 'sim.php'; 


$gameState = getGameState();
if($gameState == ''){
	//echo "Init <br>";
        $gameState = initaliseGameState( $gameState );
}

if(@$_POST['next'] != ''){
	//echo "Next <br>";
	$gameState = advanceState( $gameState );	
	//echo " advance " . $gameState;
	setGameState( $gameState );
	//getGameState();
}
if(@$_POST['reset'] != ''){
	//echo "Reset <br>";
	$gameState = initaliseGameState( $gameState );
	$_SESSION['play'] = false;
	setGameState( $gameState );
	//$_SESSION['gameState'] = $gameState;
}
if(@$_POST['play'] != ''){
	if($_SESSION['play'] == true){
		$_SESSION['play'] = false;
	} else {
		$_SESSION['play'] = true;
	}
}

// Temp
//setGameState( $gameState );
$alive = snakesAlive( $gameState );
?>
<html>
<head>
<?php if(@$_SESSION['play'] == true && $alive > 1){
  $gameState = advanceState( $gameState );
  setGameState( $gameState );
  $_SESSION['gameState'] = $gameState;
  echo "<script>".
    "setTimeout(function(){ ".
    " window.location='index.php'; ".
    " }, 200); ". 
  "</script>";
}?>
</head>
<body>
<b>BattleSnake Sim</b>
<br>
<br>
<table><tr><td valign='top'>
<?php
echo getBoard($gameState); 
?>

<br>
<form action='/' method='post'>
<input type='submit' name='reset' value='Reset' > &nbsp;
<input type='submit' name='next' value='Next' > &nbsp;
<input type='submit' name='play' value='<?php if($_SESSION['play'] == true){echo "Stop";} else {echo "Play";}?>' > &nbsp;
</form>
<br>

<textarea cols='80' rows='12'>
<?php
echo $gameState . "<br>";
?>
</textarea>

</td> <td width='15'></td> <td valign='top'>

<b>Stats</b><br>
<?php 
$state = json_decode( $gameState, true );
echo "Alive: " . $alive . "<br>"; 
echo "Ticks: " . $state['ticks'] . "<br><br>";

for($s = 0; $s < count( $state['snakes'] ); $s++){
	echo "" . $s. " ". 
		($state['snakes'][$s]['alive'] ? "<font color='#00CC00'>Alive</font>" : "<font color='#CC3333'>Dead</font>") .
		" &nbsp; h:" . $state['snakes'][$s]['health'] .
		" &nbsp; l: " . (count( $state['snakes'][$s]['tails'] ) + 1) .
		" <br>" .
		" <font size='2'>Log: " . $state['snakes'][$s]['log']. "</font><br>".
		" <font size='2'>Res: " . $state['snakes'][$s]['reason']. "</font><br> ".
		"  ";
}

echo "<br>";
$uneaten = 0;
for($i = 0; $i < count( $state['foods'] ); $i++){
	if( $state['foods'][$i]['active'] == true ){
		$uneaten++;
	}
}
echo "Uneaten food: " . $uneaten . "<br>";

?>

</td></tr></table>

<br>
</body>
</html>
