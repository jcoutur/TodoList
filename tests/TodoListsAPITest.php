<?php
	use PHPUnit\Framework\TestCase;
	class TodoListsAPITest extends TestCase{
		private $conn;
		private $todolist;
		private $http;
		private $auth;
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
			
			//Login and obtain a token
			$this->auth= new Authentications($this->conn);
			$this->auth->username='cakemail';
			$this->auth->password='cakemail';
			$this->auth->login();
			//Initiate an Guzzle HTTP client
			$this->http = new GuzzleHttp\Client(['base_uri' => 'http://localhost/~jonathan/app/api/todolists/']);
		}
	
		public function testGetAllTodoLists(){
    		//Prepare the html request
			$response = $this->http->request('POST', 'get_all_todo_lists.php', [
								'form_params'=>[
									'token'=>$this->auth->token
									]
								]);

			$body = json_decode($response->getBody());
			
			//Verify that the data field is existent which signify the user has at least one todo list
			$this->assertTrue(isset($body->data));
		}

		public function testGetAllTodoListsInvalidToken(){
			//prepare the html request 
			$response = $this->http->request('POST', 'get_all_todo_lists.php', [
								'form_params'=>[
									'token'=>-1
									]
								]);
			$body = json_decode($response->getBody());
			//Verify that the data field is existent which signify the user has at least one todo list
			$this->assertEquals("Invalid token", $body->message);
		}	

		public function testGetAllTodoListsInvalidWithoutToken(){
			//Prepare the html request
			 $response = $this->http->request('POST', 'get_all_todo_lists.php', [
								'form_params'=>[]
								]);
			$body = json_decode($response->getBody());
			//Verify the error message
			$this->assertEquals("Token variable is missing", $body->message);
		}	
		
		public function testAddNonExistingTodoList(){
			//Prepare the html request	
			 $response = $this->http->request('POST', 'add_todo_list.php', [
								'form_params'=>[
									'token'=>$this->auth->token,
									'name' =>'TEST_ADD',
									'description' => 'This was a test'
									]
								]);
			$body = json_decode($response->getBody());
			//Verify the successful message
			$this->assertEquals("Todo list successfully added", $body->message);
	
		}
		
		
		public function testTryToDuplicateATodoList(){
			
			//Prepare the html request	
			 $response = $this->http->request('POST', 'add_todo_list.php', [
								'form_params'=>[
									'token'=>$this->auth->token,
									'name' =>'TEST_ADD_DUPLICATE',
									'description' => 'This was a test'
									]
								]);
			$body = json_decode($response->getBody());
			//Verify the successful message
			$this->assertEquals("Unable to add your todolist, verify the todo list name is not already taken", $body->message);
		}

		public function testUpdateTodoList(){
			
			
			//Prepare the html request	
			 $response = $this->http->request('POST', 'update_todo_list.php', [
								'form_params'=>[
									'token'=>$this->auth->token,
									'id'=>4,
									'name' =>'UPDATE_TEST',
									'description' => 'This is part of the testing'
									]
								]);
			$body = json_decode($response->getBody());
			//Verify the successful message
			$this->assertEquals("Todo list successfully updated", $body->message);
		}
		public function testUpdateDuplicateTodoList(){
		
			//Prepare the html request	
			 $response = $this->http->request('POST', 'update_todo_list.php', [
								'form_params'=>[
									'token'=>$this->auth->token,
									'id'=>4,
									'name' =>'TEST_UPDATE_DUPLICATE',
									'description' => 'This is part of the testing'
									]
								]);
			$body = json_decode($response->getBody());
			//Verify the successful message
			$this->assertEquals("Unable to update due to duplicate entry", $body->message);
		}

		public function testDeleteTodoList(){
			//Prepare the html request
			$response = $this->http->request('POST', 'delete_todo_list.php', [
							'form_params'=>[
								'token'=> $this->auth->token,
								'id' => 3
								]
							]);
			
			$body = json_decode($response->getBody());
			
			//Verify the successful message
			$this->assertEquals("Todo list successfully deleted", $body->message);
			
			
		}
		
	
		public function testDeleteNonExistingTodoList(){
			//Prepare the html request
			$response = $this->http->request('POST', 'delete_todo_list.php', [
							'form_params'=>[
								'token'=> $this->auth->token,
								'id' => 5
								]
							]);
			
			$body = json_decode($response->getBody());
			
			//Verify the successful message
			$this->assertEquals("Unable to delete your todo list, verify the id number", $body->message);
		}
			

		public function tearDown(){
			//Close the database connection
			$this->conn=null;
			//Destroy the todolist
			$this->todolist=null;
			//Destroy the http connection
			$this->http=null;
			//Logout from the authentication
			$this->auth->logout();
		}	
	}
