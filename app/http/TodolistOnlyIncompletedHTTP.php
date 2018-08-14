<?php
class TodolistOnlyIncompletedHTTP extends DefaultHTTP{
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
		$results = $this->tdl->getAllIncompletedTodoLists();
	
		//Check if there are todolists
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
		header( 'HTTP/1.1 405 Method Not Allowed' );
	}
	
	public function putHTTP(){
		header( 'HTTP/1.1 405 Method Not Allowed' );
	}
	
	public function deleteHTTP(){
		header( 'HTTP/1.1 405 Method Not Allowed' );
	}
		
}
