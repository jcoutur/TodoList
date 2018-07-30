<?php
	header('Access-Control-Allow-Origin: *, Access-Control-Allow-Method: POST, Content-Type: application/json');
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
			//Setup the todo list for the user id 
			$tdl->user_id=$user_id;
			$results = $tdl->getAllTodoLists();
	
			//Check if there are todolists
			if($results->rowCount()>0){
				//Todo list array
				$tdl_arr=array();
				$tdl_arr['data']=array();
		
				while($row = $results->fetch(PDO::FETCH_ASSOC)){
					extract($row);
			
					$tdl_item=array(
						'id' => $id,
						'name' => $name,
						'description' => $description);
					//push to the data
					array_push($tdl_arr['data'],$tdl_item);
				}		
				//Turn the array in json format
				echo json_encode($tdl_arr);	
			}
			else{
				//No todolist for that user
				echo json_encode(array('message' => 'The user xxx has no todo list'));
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
