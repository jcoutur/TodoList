<?php
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: Application/json');
	
	include_once '../../config/Database.php';
	include_once '../../models/Authentications.php';	
	//Instantiate the database connection
	$database = new Database();
	$conn = $database->connect();


	if(isset($_GET['username']) and isset($_GET['password'])){
		//User name and password verification
		$auth = new Authentications($conn);
		$auth->username=$_GET['username'];
		$auth->password=$_GET['password'];
		$token=$auth->login();
		if($token>0){
			echo json_encode(array('token'=>$token));
		}
		else{
			if($token==-1){
				echo json_encode(array('message' => 'Invalid username or password'));
			}
			if($token==-2){
				echo json_encode(array('message' => 'Unable to create a token'));
			}
		}
	}
	else{
		echo json_encode(array('message' => 'Need username and password variables to be set in order to login'));
	}
