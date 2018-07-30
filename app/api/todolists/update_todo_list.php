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
			//Verify that a todo list id, name and description has been passed
			if(isset($_POST['id']) and isset($_POST['name']) and isset($_POST['description'])){
				
				//Setup the todo list to be added
				$tdl->user_id=$user_id;
				$tdl->id=$_POST['id'];
				$tdl->name=$_POST['name'];
				$tdl->description=$_POST['description'];
	
				//Add the todo list
				$numRowModified=$tdl->updateTodoList();
				if($numRowModified==1){
					echo json_encode(array('message' => 'Todo list successfully updated'));
				}
				else
				{	
					if($numRowModified==0){
						echo json_encode(array('message' => 'Unable to update your todolist, verify the todo list id'));
					}
					if($numRowModified==-1){
						echo json_encode(array('message' => 'Unable to update due to duplicate entry'));
					}
					else
					{
						 echo json_encode(array('message' => 'SQL ERROR CODE'. -$numRowModified));
					}
				}
			}
			else{
				echo json_encode(array('message' => 'Missing id, name or description variable'));
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
