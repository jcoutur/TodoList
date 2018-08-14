<?php
	use PHPUnit\Framework\TestCase;
	class TodoListsAPITest extends TestCase{
		private $conn;
		private $todolist;
		private $ch;
		private $auth;
		private $base_url = 'http://localhost/~jonathan/app/api/todolists/';
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
	
		public function testGetAllTodoLists(){
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			
			//Execute the curl request
			$response=curl_exec($ch);
			//Verify the status code must be equal 200
			$this->assertEquals(200,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			//Verify that the data exists
			$data=json_decode($response);
			
			$this->assertTrue(isset($data->data));
			
			$found=false;
			//Verify that the content of todo list id = 1 match the expected value
			for($i=0;$i<sizeof($data->data);$i++){
				if($data->data[$i]->id==1){
					$this->assertEquals("TEST_UPDATE_DUPLICATE",$data->data[$i]->name);
					$this->assertEquals("Update duplicate test!",$data->data[$i]->description);
					$found=true;
				}
				
			}
			//Verify that the todo list id=1 has been found
			$this->assertTrue($found);
		}
		public function testGetOneTodoListById(){
			//Prepare the CURL request
			$ch=curl_init($this->base_url.'5');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			//Execute the curl request
			$response=curl_exec($ch);
			
			//Verify the status code must be equal 200
			$this->assertEquals(200,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify that the content match
			$data=json_decode($response);
			$this->assertEquals(5,$data->id);
            $this->assertEquals("TEST_UNDONE_LIST",$data->name);
            $this->assertEquals("Get all undone todo list test!",$data->description);
            $this->assertEquals(2,sizeof($data->items));
            $this->assertEquals('integer',gettype($data->items[0]->id));
            $this->assertEquals('string', gettype($data->items[0]->name));
            $this->assertEquals('string', gettype($data->items[0]->description));
            $this->assertEquals('string',gettype($data->items[0]->status));
		}		
		public function testGetAllTodoListsInvalidPassword(){
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail123');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			
			//Execute the curl request
			$response=curl_exec($ch);
				
			//Verify the status code must be equal 401
			$this->assertEquals(401,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			$data=json_decode($response);
			
			//Verify the error message is present
			$this->assertEquals('Invalid username or password!', $data->message);
		}
		
		
		public function testGetAllIncompledTodoLists(){
			//Prepare the CURL request
 			$ch=curl_init($this->base_url . 'onlyIncompleted');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			
			//Execute the curl request
			$response=curl_exec($ch);
			
			//Verify the status code must be equal 200
			$this->assertEquals(200,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify that the data exists
			$data=json_decode($response);
			//echo $response;	
			$this->assertTrue(isset($data->data));
		}
	

		public function testAddNonExistingTodoList(){
			$post=['name' =>'TEST_ADD',
					'description'=>'This was a test'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			//Execute the curl request
			$response=curl_exec($ch);
			
			//Verify the status code must be equal 201
			$this->assertEquals(201,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify the return message
			$result=json_decode($response);
			$this->assertEquals("Todo list successfully added",$result->message);
			
			//Verify that TEST_ADD has been added
			$stmt=$this->conn->prepare('SELECT DESCRIPTION FROM ' . $this->table_lists . ' WHERE NAME="TEST_ADD" AND USER_ID=1');
			$stmt->execute();
			
			//Only one row must be returned
			$this->assertEquals(1,$stmt->rowCount());
			
			//Verify that the description match
			$result=$stmt->fetch();
			$this->assertEquals("This was a test",$result['DESCRIPTION']);
	
		}
		public function testAddLongNameTodoList(){
			
			$post=['name' =>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
					'description'=>'This was a test'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			//Execute the curl request
			$response=curl_exec($ch);
			
			//Verify the status code must be equal 201
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			$data=json_decode($response);
			$this->assertEquals("Verify the length of the name or description",$data->message);
			
		}
		
		
		public function testTryToAddDuplicateATodoList(){
			
			$post=['name' =>'TEST_ADD_DUPLICATE',
					'description'=>'This was a test'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			
			//Verify the status code must be equal 400
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));					
			
			//Verify the return message
			$result=json_decode($response);
			$this->assertEquals("Unable to add your todolist, verify the todo list name is not already taken",$result->message);
			
		}

		public function testUpdateAllFieldTodoList(){
			$post=[ 'name' => 'UPDATE_TEST',
					'description'=> 'This is part of the testing!'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);	
			//Verify the status code must be equal 200
			$this->assertEquals(204,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals("UPDATE_TEST",$result['NAME']);
			$this->assertEquals("This is part of the testing!", $result['DESCRIPTION']);
		}
			
		public function testUpdateNameFieldTodoList(){
			$post=['name' => 'UPDATE_TEST_NAME',
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 200
			$this->assertEquals(204,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals("UPDATE_TEST_NAME",$result['NAME']);
			$this->assertEquals("This is part of the testing!", $result['DESCRIPTION']);
		}
		public function testUpdateLongNameFieldTodoList(){
			
			$post=['name' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 200
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify the message content
			$this->assertEquals("Verify the length of name or description variable",$data->message);
		}
		
		public function testUpdateDescriptionFieldTodoList(){
			$post=[
				'description' => 'Description UPDATE ONLY',
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 200
			$this->assertEquals(204,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify that TEST_UPDATE has been updated
			$stmt=$this->conn->prepare('SELECT NAME, DESCRIPTION FROM ' . $this->table_lists . ' WHERE ID=4 AND USER_ID=1');
			$stmt->execute();

			$result=$stmt->fetch();
			$this->assertEquals("UPDATE_TEST_NAME",$result['NAME']);
			$this->assertEquals("Description UPDATE ONLY", $result['DESCRIPTION']);
		}

		public function testUpdateDuplicateTodoList(){
			$post=[	'name' => 'TEST_UPDATE_DUPLICATE',
					'description'=> 'This is part of the testing'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 400
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify the successful message
			$this->assertEquals("Unable to update due to duplicate entry", $data->message);
		}
		
		public function testUpdateMissingNameDescriptionFieldsTodoList(){
			$post=[];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'4');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 400
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify the successful message
			$this->assertEquals("Missing name or description variable", $data->message);
		}
		
		public function testUpdateMissingIdParam(){
			$post=['name'=> 'bogus',
					'description' =>'part of the testing!'
			];
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");	
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 400
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			//Verify the successful message
			$this->assertEquals("Missing id parameter", $data->message);
		}

		public function testDeleteTodoList(){
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'3');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			echo $response;
			$data=json_decode($response);			
			//Verify the status code must be equal 204 
			$this->assertEquals(204,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify the todo list 3 has been deleted
			$stmt=$this->conn->prepare("SELECT * FROM " . $this->table_lists . " WHERE ID = 3 AND USER_ID = 1");
			$stmt->execute();
			
			$this->assertEquals(0,$stmt->rowCount());		
		}
		
	
		public function testDeleteNonExistingTodoList(){
			//Prepare the CURL request
 			$ch=curl_init($this->base_url.'9');
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));			
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
			
			//Verify the status code must be equal 400
			$this->assertEquals(404,curl_getinfo($ch, CURLINFO_HTTP_CODE));

			//Verify the successful message
			$this->assertEquals("Todo list not found!", $data->message);
		}
		
		public function testDeleteMissingParameters(){
			
			//Prepare the CURL request
 			$ch=curl_init($this->base_url);
			curl_setopt($ch, CURLOPT_USERPWD,'cakemail' . ":" . 'cakemail');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_HEADER, 0);	
			curl_setopt($ch, CURLOPT_POST,1);
			
			//Execute the curl request
			$response=curl_exec($ch);
			$data=json_decode($response);			
						
			//Verify the status code must be equal 400
			$this->assertEquals(400,curl_getinfo($ch, CURLINFO_HTTP_CODE));
			
			//Verify the status message
			$this->assertEquals("Missing id parameter",$data->message);
		}
			
		public function tearDown(){
			//Close the database connection
			$this->conn=null;
			//Destroy the todolist
			$this->todolist=null;
		}	
	}
