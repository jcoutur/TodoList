<?php
	class TodoLists {
		//DB parameters
		private $conn;
		private $table='todo_lists';
		
		//Todo lists parameters
		public $id;
		public $user_id;
		public $name;
		public $description;
		
		//Constructor with DB
		public function __construct($db){
			$this->conn=$db;
		}
		
		//Get the all the todo lists for a specfic user
		public function getAllTodoLists(){
			//Create query
			$query='SELECT 
				ID, 
				NAME, 
				DESCRIPTION 
				FROM ' . $this->table . 
				' WHERE USER_ID = :user_id  
				ORDER BY created_at DESC';
			//Prepare statement
			$stmt=$this->conn->prepare($query);
			
			//Execute statement
			$stmt->execute([
				':user_id' => $this->user_id
			]);
			return $stmt;
		}

		public function addTodoList(){
			//Remove the specials caracters from the name and descriptions
			$this->name=htmlspecialchars(strip_tags($this->name));
			$this->description=htmlspecialchars(strip_tags($this->description));	
			//Create the query
			$query='INSERT INTO '. $this->table . ' (user_id, name, description) VALUES(:userId,:name,:description)';
			
			//Prepare statement
			$stmt=$this->conn->prepare($query);

			try{
				$stmt->execute([
					':userId' => $this->user_id,
					':name' => $this->name,
					':description' => $this->description
					]);
				return $stmt->rowCount(); ;
			}	
			catch(PDOException $e){
				if($e->getCode()==23000){
					return -1;
				}
				else{
					return -$e->getCode();
				}
			}
			
		}
 
		public function updateTodoList(){
			//Remove the specials caracters from the name and descriptions
			$this->name=htmlspecialchars(strip_tags($this->name));
			$this->description=htmlspecialchars(strip_tags($this->description));
			//Create the query
			$query="UPDATE todo_lists SET NAME=:newName, DESCRIPTION=:newDescription WHERE ID=:id AND USER_ID=:userId";
					
			//prepare the statement
			$stmt=$this->conn->prepare($query);
			try{
				$stmt->execute([
					':newName' => $this->name,
					':newDescription' => $this->description,
					':id' => $this->id,
					':userId' => $this->user_id
				]);
				return $stmt->rowCount();
			}
			catch(PDOException $e){
				if($e->getCode()==23000){
					return -1;
				}
				else{
					return -$e->getCode();
				}
			}
			return -2;
		}
		
		public function deleteTodoList(){
			//Create the query
			$query='DELETE FROM todo_lists WHERE USER_ID = :userId AND ID = :id';
			
			//Prepare the statement
			$stmt=$this->conn->prepare($query);
			$stmt->execute([
				':id' => $this->id,
				':userId' => $this->user_id
			]);
			return $stmt->rowCount();
		}	
	}
