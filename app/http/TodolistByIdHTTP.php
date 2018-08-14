<?php
class TodolistByIdHTTP extends DefaultHTTP{
	private $conn;
	private $tdl;
	public function __construct($userId,$todolistId){
		$database=new Database();
		$this->conn=$database->connect();
		$this->tdl= new Todolists($this->conn);
	
		//Setup the user id inside the todo list
		$this->tdl->user_id=$userId;
		$this->tdl->id=$todolistId;
	}

	public function getHTTP(){
		$result=$this->tdl->getOneTodoListById();
		if($result!=null){
			echo json_encode($result);
		}
		else{
			header('HTTP/1.1 404 Todo list not found!');
			echo json_encode(array('message'=>'Todo list not found!'));
		}
		
						
	}

	public function postHTTP(){
		header( 'HTTP/1.1 405 Method Not Allowed' );
	}
	
	public function putHTTP(){
		$data = json_decode( file_get_contents( 'php://input' ));
		if(json_last_error==JSON_ERROR_NONE){
			if(isset($data->name) or isset($data->description)){
				//Setup the todo list to be updated 
				$this->tdl->name=$data->name;
				$this->tdl->description=$data->description;
	
				//Add the todo list
				$numRowModified=$this->tdl->updateTodoListById();
				if($numRowModified==1){
					header( 'HTTP/1.1 204 No Content');
					echo json_encode(array('message' => 'Todo list successfully updated'));
				}
				else
				{	
					switch ($numRowModified) {
						case 0:
							header( 'HTTP/1.1 404 Todo List Not Found' );
							echo json_encode(array('message' => 'Todo list not found!'));
							break;
						case -1:
							header( 'HTTP/1.1 400 Bad Request' );		
							echo json_encode(array('message' => 'Unable to update due to duplicate entry'));
							break;
						case -2:
							header( 'HTTP/1.1 400 Bad Request' );
							echo json_encode(array('message' => 'SQL ERROR CODE: '));
							break;
						case -4:
							header('HTTP/1.1 400 Bad Request');
							echo json_encode(array('message' => 'Verify the length of name or description variable'));
					}
				
				}
			}
			else{
				header( 'HTTP/1.1 400 Bad Request' );
				echo json_encode(array('message' => 'Missing name or description variable'));
			}	
		}
	}
	
	public function deleteHTTP(){
		//Delete the todo list
		$numRowModified=$this->tdl->deleteTodoListById();
		switch($numRowModified){
			case 1:
				header( 'HTTP/1.1 204 No Content');
				echo json_encode(array('message' => 'Todo list successfully deleted'));
				break;
			case 0:
				header( 'HTTP/1.1 404 Todo list not found!' );		
				echo json_encode(array('message' => 'Todo list not found!'));
				break;
		}
	}
		
}
