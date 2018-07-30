<?php
	use PHPUnit\Framework\TestCase;
	include("./app/config/Database.php");	
	include("./app/models/TodoLists.php");
	class TodoListsTest extends TestCase{
		private $conn;
		private $todolist;
	
		public static function setUpBeforeClass(){
			//Database connection
			$DB=new Database();
			$connection=$DB->connect();
		
			//Todo lists id 1 to 10 for the user cakemail are reserved for testing purposes
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE id<10 AND user_id=1');
			$stmt->execute();
		   	
			//Delete todo lists name that are used during testing
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE name="TEST_UPDATE" AND user_id=1');
			$stmt->execute(); 
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE name="TEST_DELETE" AND user_id=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE name="TEST_ADD_DUPLICATE" AND user_id=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE name="TEST_ADD" AND user_id=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM todo_lists WHERE name="TEST_UPDATE_DUPLICATE" AND user_id=1');
			$stmt->execute();				
				
			//Populate the database for testing
			//Todo list to test for modification
			$stmt=$connection->prepare("INSERT INTO todo_lists (ID,USER_ID,NAME,DESCRIPTION) VALUES(4,1,'TEST_UPDATE', 'Modify test!')");
			$stmt->execute();
			
			//Todo list for the delete test
			$stmt=$connection->prepare("INSERT INTO todo_lists (ID,USER_ID,NAME,DESCRIPTION) VALUES(3,1,'TEST_DELETE', 'Delete test!')");
			$stmt->execute();
			
			//Todo list for the add duplicate test
			$stmt=$connection->prepare("INSERT INTO todo_lists (ID,USER_ID,NAME,DESCRIPTION) VALUES(2,1,'TEST_ADD_DUPLICATE', 'Add duplicate test!')");
			$stmt->execute();
			
			//Todo list for the update duplicate test
			$stmt=$connection->prepare("INSERT INTO todo_lists (ID,USER_ID,NAME,DESCRIPTION) VALUES(1,1,'TEST_UPDATE_DUPLICATE', 'Update duplicate test!')");
			$stmt->execute();
			
			//Close the databse connection
			$DB=null;
		}
		public function setUp(){
			//Database connection
			$DB=new Database();
			$this->conn=$DB->connect();
			
			//Inititiate a TodoLists
			$this->todolist=new TodoLists($this->conn);
		}
	
		public function testReadAll(){
			//Verify we can get all the todo lists for the user cakemail
			$this->todolist->user_id=1;
			$stmt=$this->todolist->getAllTodoLists();
			$this->assertGreaterThan(1,$stmt->rowCount());
		}

		public function testAddNonExistingTodoList(){
			//Setup the todo list to be added
			$this->todolist->user_id=1;
			$this->todolist->name='TEST_ADD';
			$this->todolist->description='This was a test';

			//Add a non existing todo list
			$this->assertEquals(1,$this->todolist->addTodoList());
		}
		
		
		public function testTryToAddDuplicateTodoList(){
			//Try to add a duplicate todo list
			$this->todolist->user_id=1;
			$this->todolist->name='TEST_ADD_DUPLICATE';
			$this->todolist->description='This was a test';
			$this->assertEquals(-1,$this->todolist->addTodoList());
		}

		public function testUpdateTodoList(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->name='UPDATE_TEST';
			$this->todolist->description='This is part of the testing!';
	
			//One row only must be updated
			$this->assertEquals(1,$this->todolist->updateTodoList());
				
		}
		
		public function testUpdateDuplicateTodoList(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->name='TEST_UPDATE_DUPLICATE';
			$this->todolist->description='This is part of the testing!';
	
			//One row only must be updated
			$this->assertEquals(-1,$this->todolist->updateTodoList());
				
		}

		public function testDeleteTodoList(){
			//Setup the todo list to be deleted
			$this->todolist->id=3;
			$this->todolist->user_id=1;
			
			//Verify that at least one row has been deleted
			$this->assertEquals(1,$this->todolist->deleteTodoList());
		}
		public function testDeleteANonExistingTodoList(){
			//Setup the todo list to be deleted
			$this->todolist->id=5;
			$this->todolist->user_id=1;

			//Verify that no row has been deleted
			$this->assertEquals(0,$this->todolist->deleteTodoList());
		}	

		public function tearDown(){
			//Close the database connection
			$this->conn=null;
			//Destroy the todolist
			$this->todolist=null;
		}	
	}
