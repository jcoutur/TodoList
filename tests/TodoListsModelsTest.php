<?php
	use PHPUnit\Framework\TestCase;
	include("./app/config/Database.php");	
	include("./app/models/TodoLists.php");
	class TodoListsModelsTest extends TestCase{
		private $conn;
		private $todolist;
		private $table_lists='todo_lists';
		public static function setUpBeforeClass(){
			
			//Table name
			$table_lists='todo_lists';
			$table_items='todo_list_items';
			//Database connection
			$DB=new Database();
			$connection=$DB->connect();
		
			//Todo lists id 1 to 10 for the user cakemail are reserved for testing purposes
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE id<10 AND USER_ID=1');
			$stmt->execute();
		   	
			//Delete todo lists name that are used during testing
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_UPDATE" AND USER_ID=1');
			$stmt->execute(); 
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_DELETE" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_ADD_DUPLICATE" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_ADD" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_UPDATE_DUPLICATE" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_UNDONE_LIST" AND USER_ID=1');				
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="TEST_DONE_LIST" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="UPDATE_TEST" AND USER_ID=1');
			$stmt->execute();
			$stmt=$connection->prepare('DELETE FROM ' . $table_lists . ' WHERE NAME="UPDATE_TEST_NAME" AND USER_ID=1');
			$stmt->execute();
			//Populate the database for testing
			//Todo list to test for modification
			$stmt=$connection->prepare("INSERT INTO " . $table_lists . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(4,1,'TEST_UPDATE', 'Modify test!')");
			$stmt->execute();
			
			//Todo list for the delete test
			$stmt=$connection->prepare("INSERT INTO " . $table_lists  . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(3,1,'TEST_DELETE', 'Delete test!')");
			$stmt->execute();
			
			//Todo list for the add duplicate test
			$stmt=$connection->prepare("INSERT INTO " . $table_lists . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(2,1,'TEST_ADD_DUPLICATE', 'Add duplicate test!')");
			$stmt->execute();
			
			//Todo list for the update duplicate test
			$stmt=$connection->prepare("INSERT INTO " . $table_lists . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(1,1,'TEST_UPDATE_DUPLICATE', 'Update duplicate test!')");
			$stmt->execute();

			//Todo list for the test get all undone lists
			$stmt=$connection->prepare("INSERT INTO " . $table_lists . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(5,1,'TEST_UNDONE_LIST', 'Get all undone todo list test!')");
			$stmt->execute();

			$stmt=$connection->prepare("INSERT INTO " . $table_lists . " (ID,USER_ID,NAME,DESCRIPTION) VALUES(6,1,'TEST_DONE_LIST', 'Get all undone todo lis    t test!')");
            		$stmt->execute();

			//Insert todo list items for the list TEST_UNDONE_LIST and TEST_DONE_LIST
			$stmt=$connection->prepare("INSERT INTO " . $table_items . " (TODO_LIST_ID, NAME, DESCRIPTION, STATUS) VALUES(5,'ITEM_1','ITEM_1 DESC','not started')");
			$stmt->execute();

			$stmt=$connection->prepare("INSERT INTO " . $table_items . " (TODO_LIST_ID, NAME, DESCRIPTION, STATUS) VALUES(5,'ITEM_2','ITEM_2 DESC','started')");
			$stmt->execute(); 			

			$stmt=$connection->prepare("INSERT INTO " . $table_items . " (TODO_LIST_ID, NAME, DESCRIPTION, STATUS) VALUES(6,'ITEM_1','ITEM_1 DESC','completed')");
 			$stmt->execute();

			$stmt=$connection->prepare("INSERT INTO " . $table_items . " (TODO_LIST_ID, NAME, DESCRIPTION, STATUS) VALUES(6,'ITEM_2','ITEM_2 DESC','completed')");			
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

			//Verify that at least one todo list is returned
			$this->assertGreaterThan(0,$stmt->rowCount());
			
			$found=false;

			//Verify that the content of todo list id = 1 match the expected value
			while($row = $stmt->fetch()){
				extract($row);
				if($ID==1){
					$this->assertEquals("TEST_UPDATE_DUPLICATE",$NAME);
					$this->assertEquals("Update duplicate test!",$DESCRIPTION);
					$found=true;
				}
				
			}
			//Verify that the todo list id=1 has been found
			$this->assertTrue($found);
		}

		public function testGetAllIncompletedTodoLists(){
			$this->todolist->user_id=1;
			$stmt=$this->todolist->getAllIncompletedTodoLists();
			$this->assertGreaterThan(0,$stmt->rowCount());
		}
		
		public function testGetOneTodoListById(){
			$this->todolist->user_id=1;
			$this->todolist->id=5;
			$result=$this->todolist->getOneTodoListById();
			$this->assertEquals(5,$result['id']);
			$this->assertEquals("TEST_UNDONE_LIST",$result['name']);
			$this->assertEquals("Get all undone todo list test!",$result['description']);
			$this->assertEquals(2,sizeof($result['items']));
			$this->assertEquals('integer',gettype($result['items'][0]['id']));
			$this->assertEquals('string', gettype($result['items'][0]['name']));	
			$this->assertEquals('string', gettype($result['items'][0]['description']));	
			$this->assertEquals('string',gettype($result['items'][0]['status']));
		}

		public function testAddNonExistingTodoList(){
			//Setup the todo list to be added
			$this->todolist->user_id=1;
			$this->todolist->name='TEST_ADD';
			$this->todolist->description='This was a test';

			//Add a non existing todo list
			//Verify that only one record has been added
			$this->assertEquals(1,$this->todolist->addTodoList());
			
			//Verify that TEST_ADD has been added
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM '. $this->table_lists . ' WHERE NAME="TEST_ADD" AND USER_ID=1');
			$stmt->execute();
			
			//Only one row must be returned
			$this->assertEquals(1,$stmt->rowCount());
			

			$results=$stmt->fetch();
			//Verify that the name match
			$this->assertEquals($this->todolist->name,$results['NAME']);	
			//Verify that the description match
			$this->assertEquals($this->todolist->description,$results['DESCRIPTION']);
			
			
		}
		
		
		public function testTryToAddDuplicateTodoList(){
			//Try to add a duplicate todo list
			$this->todolist->user_id=1;
			$this->todolist->name='TEST_ADD_DUPLICATE';
			$this->todolist->description='This was a test';
			$this->assertEquals(-1,$this->todolist->addTodoList());
		}

		public function testUpdateAllFieldsTodoList(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->name='UPDATE_TEST';
			$this->todolist->description='This is part of the testing!';
	
			//One row only must be updated
			$this->assertEquals(1,$this->todolist->updateTodoListById());
			
			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists  . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals($this->todolist->name,$result['NAME']);
			$this->assertEquals($this->todolist->description, $result['DESCRIPTION']);			
		}
		
		public function testUpdateNameFieldTodoList(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->name='UPDATE_TEST_NAME_FIELD';
			$this->todolist->description=null;
	
			//One row only must be updated
			$this->assertEquals(1,$this->todolist->updateTodoListById());
			
			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists  . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals($this->todolist->name,$result['NAME']);
			$this->assertEquals('This is part of the testing!',$result['DESCRIPTION']);			
		}
		
		public function testUpdateDescriptionFieldTodoList(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->description="Description UPDATE ONLY";
	
			//One row only must be updated
			$this->assertEquals(1,$this->todolist->updateTodoListById());
			
			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists  . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals('UPDATE_TEST_NAME_FIELD',$result['NAME']);
			$this->assertEquals($this->todolist->description,$result['DESCRIPTION']);
		}			
		
		public function testUpdateNoFiledsTodoListById(){
			
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			
			//Will generate a PDO Exception
			$this->assertEquals(-2,$this->todolist->updateTodoListById());
		}

		public function testUpdateDuplicateTodoListById(){
			//Setup the todo list to be modified
			$this->todolist->user_id=1;
			$this->todolist->id=4;
			$this->todolist->name='TEST_UPDATE_DUPLICATE';
			$this->todolist->description='This is part of the testing!';
	
			//One row only must be updated
			$this->assertEquals(-1,$this->todolist->updateTodoListById());
				
		}

		public function testDeleteTodoListById(){
			//Setup the todo list to be deleted
			$this->todolist->id=3;
			$this->todolist->user_id=1;
			
			//Verify that at least one row has been deleted
			$this->assertEquals(1,$this->todolist->deleteTodoListById());
			
			//Verify that the todo list with id=3 has been properly deleted
			$stmt=$this->conn->prepare('SELECT ID FROM ' . $this->table_lists . ' WHERE ID=3');
			$stmt->execute();
			$this->assertEquals(0,$stmt->rowCount());
		}
	
		public function testDeleteANonExistingTodoListById(){
			//Setup the todo list to be deleted
			$this->todolist->id=8;
			$this->todolist->user_id=1;

			//Verify that no row has been deleted
			$this->assertEquals(0,$this->todolist->deleteTodoListById());
		}	

		public function tearDown(){
			//Close the database connection
			$this->conn=null;
			//Destroy the todolist
			$this->todolist=null;
		}	
	}
