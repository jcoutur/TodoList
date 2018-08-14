<?php
	class TodoLists {
		//DB parameters
		private $conn;
		private $table_lists='todo_lists';
		private $table_items='todo_list_items';
		
		//Todo lists parameters
		public $id;
		public $user_id;
		public $name;
		public $description;
		public $status;
		
		//Constructor with DB
		public function __construct($db){
			$this->conn=$db;
		}
		
		//Get the all the todo lists for a specfic user
		//Return a PDO statement
		public function getAllTodoLists(){
			//Create query
			$query='SELECT 
				ID, 
				NAME, 
				DESCRIPTION 
				FROM ' . $this->table_lists . 
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
		
		
			
		
		//Get all the todo lists with incompleted items
		//Return a PDO statement
		public function getAllIncompletedTodoLists(){
			//Create query
			$query="SELECT " . $this->table_lists . ".ID, " . $this->table_lists . ".NAME, ". $this->table_lists .".DESCRIPTION 
					FROM " . $this->table_lists . " 
					INNER JOIN " . $this->table_items . 
					" ON " . $this->table_lists . ".ID=" . $this->table_items . ".TODO_LIST_ID
					WHERE " . $this->table_items . ".STATUS <> 'completed' AND " . $this->table_lists . ".USER_ID=:user_id
					GROUP BY ". $this->table_lists . ".ID";
			//Prepare the statement
			$stmt=$this->conn->prepare($query);
			
			//Execute the statement
			$stmt->execute([
					':user_id' => $this->user_id
					]);
			return $stmt;
		}
		/*Add a new todo lists
		//Return 1 when one todo list is added
		//Return -1 when the name is duplicate
		//Return -2 when a PDOException occurs
		*/
		public function addTodoList(){
			//Remove the specials caracters from the name and descriptions
			$this->name=htmlspecialchars(strip_tags($this->name));
			$this->description=htmlspecialchars(strip_tags($this->description));	
			//Create the query
			$query='INSERT INTO '. $this->table_lists . ' (user_id, name, description) VALUES(:userId,:name,:description)';
			
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
					return -2;
				}
			}
			
		}
		
		//Return an array of the todo list when it is found
		//When the todo list has not been found it returns null
		public function getOneTodoListById(){
			//Create query to get the description of the todo list
			$query_list='SELECT NAME, DESCRIPTION 
						 FROM ' . $this->table_lists . ' 
						 WHERE USER_ID=:userId AND ID=:id';
			$stmt = $this->conn->prepare($query_list);
			$stmt->execute([
				':userId' => $this->user_id,
				':id' => $this->id 
			]);

			//Verify that the todo list has been found
			if($stmt->rowCount()==1){
				$row=$stmt->fetch();
				//Create the return array
				$result_arr=['id' => (int)$this->id,
									'name'=> $row['NAME'],
									'description'=> $row['DESCRIPTION'],
									'items' => array()];
				//Create query to get all the items of a todo list
				$query_item='SELECT ID, NAME, DESCRIPTION, STATUS  
							 FROM ' . $this->table_items . '
							 WHERE TODO_LIST_ID = :id';
				$stmt=$this->conn->prepare($query_item);
				$stmt->execute([
					'id'=>$this->id
				]);
				while($row=$stmt->fetch()){
					extract($row);
					array_push($result_arr['items'], [
								'id' => (int)$ID,
								'name' => $NAME,
								'description' => $DESCRIPTION,
								'status'=> $STATUS
					]);
				}
				return $result_arr;
			}
			return null;
	}
		/*Update a todo lists
		//Return 1 when one todo list is updated
		//Return 0 when the todo list is non existing
		//Return -1 when the name is duplicate
		//Return -2 when a PDOException occurs
		//Return -4 when the length of the name or description are too long
		*/
		public function updateTodoListById(){
			//Remove the specials caracters from the name and descriptions
			$this->name=htmlspecialchars(strip_tags($this->name));
			$this->description=htmlspecialchars(strip_tags($this->description));
			
			//Make sure that the name or description are not null to perform the update
			//Query and parameters setup
			$param=[
					':id' => $this->id,
					':userId' => $this->user_id
					];
			$query_arguments=array();
			if($this->name!=null){
				if(strlen($this->name)>45){
					return -4;
				}
				array_push($query_arguments,'NAME=:newName');
				$param[':newName'] = $this->name;
			}
			if($this->description!=null){
				if(strlen($this->description)>1500){
					return -4;
				}
				array_push($query_arguments,'DESCRIPTION=:newDescription');
				$param[':newDescription'] = $this->description;
			}
			$arguments='';
			//Prepare the arguments for the query
			for($i=0;$i<sizeof($query_arguments);$i++){
				//Adds a comma between arguments
				if($i!=0){
					$arguments=$arguments.', ';
				}
				$arguments=$arguments . $query_arguments[$i];
			}
			//Create the query
			$query="UPDATE " . $this->table_lists . " SET ". $arguments . " WHERE ID=:id AND USER_ID=:userId";
			//prepare the statement
			$stmt=$this->conn->prepare($query);
			try{
				$stmt->execute($param);
				return $stmt->rowCount();
			}
			catch(PDOException $e){
				//Duplicate exception
				if($e->getCode()==23000){
					return -1;
				}
				//Other SQL or PDO error has occured
				else{
					return -2;
				}
			}



		}
		/*Delete a todo list
		*Return 1 when a todo list is deleted
		*Return 0 when trying to delete a non existant todo list
		*/
		public function deleteTodoListById(){
			//Create the query
			$query='DELETE FROM ' . $this->table_lists .' WHERE USER_ID = :userId AND ID = :id';
			
			//Prepare the statement
			$stmt=$this->conn->prepare($query);
			$stmt->execute([
				':id' => $this->id,
				':userId' => $this->user_id
			]);
			return $stmt->rowCount();
		}	
	}
