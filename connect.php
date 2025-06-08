<?php
$host		= 'localhost';
// database name
$db			= 'slsu_community';
$user		= 'root';
$password	= '';

function connect($host,$db,$user,$password){
	try {
		$conn = "mysql:host=$host;dbname=$db;charset=UTF8";
		$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
		// echo "Connected";
		return new PDO($conn, $user, $password, $options);
		
	} catch (PDOException $e) {
		die($e->getMessage());
	}
}

function getPDO() {
    $host = 'localhost';
    $db = 'slsu_community';
    $user = 'root';
    $password = '';
    return new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}


return connect($host,$db,$user,$password);
?>
