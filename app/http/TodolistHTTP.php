<?php
class TodolistHTTP extends DefaultHTTP{
	private $conn;
	private $tdl;
	public function __construct($userId){
		$database=new Database();
		$this->conn=$database->connect();
		$this->tdl= new Todolists($this->conn);
	
		//Setup the user id inside the todo list
		$this->tdl->user_id=$userId;
	}

	public function getHTTP(){
		$results = $this->tdl->getAllTodoLists();
		if($results->rowCount()>0){
			//Todo list array
			$tdl_arr=array();
			$tdl_arr['data']=array();
			while($row = $results->fetch(PDO::FETCH_ASSOC)){
				extract($row);
				$tdl_item=array(
					'id' => (int)$ID,
					'name' => $NAME,
					'description' => $DESCRIPTION);
					//push to the data
					array_push($tdl_arr['data'],$tdl_item);
			}		
			//Turn the array in json format
			echo json_encode($tdl_arr);	
		}
		else{
			//Set the header to 204 for no content
			header("HTTP/1.0 204 The user has no todo list".$this->user_id);
			//No todolist for that user
			echo json_encode(array('message' => 'The user has no todo list'.$this->user_id));
		}
	}

	public function postHTTP(){
		$data = json_decode( file_get_contents( 'php://input' ));
		if(json_last_error==JSON_ERROR_NONE){	
			//Verify that a todo list name and description has been passed
			if(isset($data->name) and isset($data->description)){
				if(strlen($data->name)<=45 and strlen($data->description)<=1500){
					//Setup the todo list to be added
					$this->tdl->name=$data->name;
					$this->tdl->description=$data->description;
	
					//Add the todo list
					$numRowModified=$this->tdl->addTodoList();
					if($numRowModified==1){
						header( 'HTTP/1.1 201 Todo list successfully added' );
						echo json_encode(array('message' => 'Todo list successfully added'));
					}
					else
					{
						if($numRowModified=-1){
							header( 'HTTP/1.1 400 Bad Request' );		
							echo json_encode(array('message' => 'Unable to add your todolist, verify the todo list name is not already taken'));
						}
						else{
							header( 'HTTP/1.1 400 Bad Request' );
							echo json_encode(array('message' => 'SQL ERROR'));
						}
					}
				}
				else{
					header( 'HTTP/1.1 400 Bad Request' );
					echo json_encode(array('message' => 'Verify the length of the name or description'));
				}
			}	
			else{
				header( 'HTTP/1.1 400 Bad Request' );
				echo json_encode(array('message' => 'Missing name or description variable'));
			}
		}
		
		
		else{
			header( 'HTTP/1.1 400 Bad Request' );
			echo json_encode(array('message' => 'Accept only parameters in JSON format'));
		}
	}
	
	public function putHTTP(){
		header('HTTP/1.1 400 Bad Request');
		echo json_encode(array('message' => 'Missing id parameter'));
	}
	
	public function deleteHTTP(){
		header('HTTP/1.1 400 Bad Request');
		echo json_encode(array('message' => 'Missing id parameter'));
	}
		
}
