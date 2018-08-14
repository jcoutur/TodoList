<?php
class Authentications{
	private $table = 'users';
	private $conn;
	public $username;
	public $password;
	public $id;	
	//Class constructor
	public function __construct($DB){
		$this->conn = $DB;
	}

	/*Return a user ID when given a good username and password
	* Return -1 for an invalid username or password
	*/
	public function login(){
		//Perform the user verification
		$stmt = $this->conn->prepare('SELECT ID FROM users WHERE USER_NAME = :userName AND PASSWORD = :password');
		$stmt->execute([
			':userName' => $this->username,
			':password' => $this->password
		]);
		
		//If the user authenticated
		if($stmt->rowCount()==1){
			
			//Get the user id
			$result=$stmt->fetch();
			
			//Create a simple token. Must be change for a more secure token generator
			$this->id=$result['ID'];
			return $result['ID'];
		}
		else{
			//Wrong username or password case
			$this->id=-1;
			return $this->id;
		}
	}
	/*Will get the username and password from the super global variable and will return the user id
	* When there is a login failure it return -1
	*/
	public function basicHTMLAuthentication(){
		if(isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])){
			$this->username=$_SERVER['PHP_AUTH_USER'];
			$this->password=$_SERVER['PHP_AUTH_PW'];
			$this->id=$this->login();
		}
		else{
			$this->id = -1;
		}
		return $this->id;
	}
	
}
