<?php
	header('Access-Control-Allow-Origin: *, Access-Control-Allow-Method: POST, Content-Type: application/json');
	include_once '../../config/Database.php';
	include_once '../../models/TodoLists.php';
	include_once '../../models/Authentications.php';
	include_once '../../http/DefaultHTTP.php';	
	include_once '../../http/TodolistHTTP.php';
	include_once '../../http/TodolistByIdHTTP.php';
	include_once '../../http/TodolistOnlyIncompletedHTTP.php';

	//Sethe the base URI
	$base_URI="/~jonathan/app/api/todolists/";
	
	//Instantiate the database connection
	$database = new Database();
	$conn = $database->connect();
	
	//Instantiate the TodoLists
	$tdl = new TodoLists($conn);
	//User validation
	$auth = new Authentications($conn);
	$auth->basicHTMLAuthentication();	
	//Obtain the parameters from the URI
	$request = str_replace($base_URI,"",$_SERVER['REQUEST_URI']);
	$params = explode("/",$request);
	if($auth->id!=-1){
		$tdlHTTP;
		if(!is_numeric($params[0])){
			if($params[0]=='onlyIncompleted'){
				$tdlHTTP= new TodolistOnlyIncompletedHTTP($auth->id);	
			}
			else{
				$tdlHTTP=new TodolistHTTP($auth->id);
			}
		}
		else
		{
			$tdlHTTP= new TodolistByIdHTTP($auth->id,$params[0]);
		}
		$tdlHTTP->execute();
			
	}
	else{
		//Invalid username and password scenario
		//Set the status to 401
		header('WWW-Authenticate: Basic realm="get-all-todo-list"');
	    	header('HTTP/1.0 401 Unauthorized');
		echo json_encode(array('message'=>'Invalid username or password!'));
	}
