<?php
	header('Access-Control-Allow-Origin: *, Content-Type: Application/json, Access-Control-Allow-Method: POST,X-Requested-With');
	
	include_once '../../config/Database.php';
	include_once '../../models/TodoLists.php';
	include_once '../../models/Authentications.php';	
	
	//Instantiate the database connection
	$database = new Database();
	$conn = $database->connect();

	//Instantiate the TodoLists
	$tdl = new TodoLists($conn);
	
	//Token validation
	$auth = new Authentications($conn);
	if(isset($_POST['token'])){
		$auth->token=$_POST['token'];
		$user_id=$auth->tokenValidation();
		if($user_id>0){
			//Verify that a todo list id has been passed
			if(isset($_POST['id'])){
				
				//Setup the todo list to be deleted
				$tdl->user_id=$user_id;
				$tdl->id=$_POST['id'];
	
				//Add the todo list
				if($tdl->deleteTodoList()){
					echo json_encode(array('message' => 'Todo list successfully deleted'));
				}
				else
				{
					echo json_encode(array('message' => 'Unable to delete your todo list, verify the id number'));
				}
			}
			else{
				echo json_encode(array('message' => 'Missing id variable'));
			}
		}
		else{
			if($user_id==-1){
				echo json_encode(array('message' => 'Invalid token'));
			}
			if($user_id==-2){
				echo json_encode(array('message' => 'Token is expired'));
			}
		}
	}
	else{
		echo json_encode(array('message' => 'Token variable is missing'));
	}
