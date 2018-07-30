<?php
class Authentications{
	private $table = 'authentications';
	private $conn;
	public $username;
	public $password;
	public $token;
	
	//Class constructor
	public function __construct($DB){
		$this->conn = $DB;
	}

	/*Return a a token a positive integer when a valid username and password is given
	* Return -1 for an invalid username or password
	* Return -2 when it is impossible to create a token
	*/
	public function login(){
		//Delete previous token for the user
		$stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE user_id=(SELECT id FROM users WHERE user_name=:userName)");
		$stmt->execute([
			':userName' => $this->username
			]);
	
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

			//Try a 100 times to create a unique token
			$i=0;
			while($i<100){
				//To be modified with a cryptographic token generator
				$token=rand(1,10000);
				
				//Store the token in the database
				try{
					$stmt=$this->conn->prepare("INSERT INTO " . $this->table . "(TOKEN, USER_ID) VALUES(:token,:id)");
					$stmt->execute([
						':token' => $token,
						':id' => $result['ID']
					]);
					$this->token=$token;
					return $token; 
				}
				catch(PDOException $e){}
				$i++;
			}
			//Impossible to create a unique token
			$this->token=-2;
			return $this->token;
		}
		else{
			//Wrong username or password case
			$this->token=-1;
			return $this->token;
		}
	}
	
	/*
	*This function return a user id when a valid token is received
	*Return -1 when the token is invalid
	*Return -2 when the token is expired
	*/
	public function tokenValidation(){
		//A token is only valid 10 minutes. Now - 10minutes
		$tminus10=time()-600;
	
		//Find the user id and the time of the token creation associated with the token
		$stmt=$this->conn->prepare('SELECT (TIME_TO_SEC(NOW())-TIME_TO_SEC(CREATED_AT))/60 AS LOGIN_DURATION, USER_ID FROM '. $this->table . ' WHERE TOKEN=:token');
		$stmt->execute([
			':token' => $this->token
		]);
		if($stmt->rowCount()==1){
			$result=$stmt->fetch();
			if($result['LOGIN_DURATION']<10){
				return $result['USER_ID'];
			}
			else{
				return -2;
			}
			
		}
		else{
			return -1;
		}
		
	}

	public function logout(){
		$stmt=$this->conn->prepare('DELETE FROM '. $this->table . ' WHERE TOKEN=:token');
		$stmt->execute([
			':token' => $this->token
		]);
		if($stmt->rowCount()==1){
			return true;
		}
		return false;
	}
}
