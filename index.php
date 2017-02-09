<?php
session_start();
include 'sim.php'; 


$gameState = getGameState();
if($gameState == ''){
	echo "Init <br>";
        $gameState = initaliseGameState( $gameState );
}

if(@$_POST['next'] != ''){
	echo "Next <br>";
	$gameState = advanceState( $gameState );	
	//echo " advance " . $gameState;
	setGameState( $gameState );
	//getGameState();
}
if(@$_POST['reset'] != ''){
	echo "Reset <br>";
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
BattleSnake Sim
<br>
Alive: <?php echo $alive; ?> &nbsp; Ticks: <?php echo ""; ?> 
<br>
<br>
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

<?php

echo $gameState . "<br>";
?>

</body>
</html>
