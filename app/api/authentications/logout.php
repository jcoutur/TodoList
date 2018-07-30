<?php
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: Application/json');
	
	include_once '../../config/Database.php';
	include_once '../../models/Authentications.php';	
	//Instantiate the database connection
	$database = new Database();
	$conn = $database->connect();


	if(isset($_GET['token'])){
		//Setup the token
		$auth = new Authentications($conn);
		$auth->token=$_GET['token'];
		if($auth->logout()){
			echo json_encode(array('message'=>'Logout successful'));
		}
		else{
			echo json_encode(array('message'=>'Invalid token'));
		}
	}
	else{
		echo json_encode(array('message' => 'Need a token in order to logout'));
	}
