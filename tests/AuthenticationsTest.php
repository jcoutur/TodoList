<?php
use PHPUnit\Framework\TestCase;
include("./app/models/Authentications.php");
class AuthenticationsTest extends TestCase{

	private $conn;
	private $auth;
	
	public function setUp(){
		$DB = new Database();
		$this->conn = $DB->connect();
		$this->auth=new Authentications($this->conn);
	}

	public function testLoginSuccessful(){
		//Setup the user for the login
		$this->auth->username='cakemail';
		$this->auth->password='cakemail';
		//Verify if it receive a valid token
		$this->assertGreaterThanOrEqual(1,$this->auth->login());
		
	}
	public function testLoginFailure(){
		//Setup the user for the login
		$this->auth->username='cakemail';
		$this->auth->password='cakemail123';	
		//A return value of -1 signify an invalid username or password
		$this->assertEquals(-1,$this->auth->login());
	}
	
	public function testTokenValid(){
		//Setup the user cakemail for the login		
		$this->auth->username='cakemail';
		$this->auth->password='cakemail';
		$this->auth->login();

		//Verify the token is valid inside the auth
		$this->assertGreaterThan(0,$this->auth->tokenValidation());
	}
	
	public function testInvalidToken(){
		$this->auth->token=-1;
		$this->assertEquals(-1,$this->auth->tokenValidation());
	}
	
	public function testExpiredToken(){
		//Login with the user cakemail
		$this->auth->username='cakemail';
		$this->auth->password='cakemail';
		$this->auth->login();
		//Change the login time in order to invalidate the token	
		$stmt=$this->conn->prepare('UPDATE authentications SET CREATED_AT = DATE_ADD(CREATED_AT,INTERVAL -20 minute) WHERE TOKEN=:token');
		$stmt->execute([
			':token'=>$this->auth->token
		]);
		$this->assertEquals(-2,$this->auth->tokenValidation());
	}

	public function testLogoutWorking(){
		//Login using cakemail user
		$this->auth->username='cakemail';
		$this->auth->password='cakemail';
		$this->auth->login();
		
		$this->assertTrue($this->auth->logout());
	}
	public function testLogoutFailure(){
		$this->auth->token=-1;
		$this->assertFalse($this->auth->logout(-1));
	}

	public function tearDown(){
		$this->conn=null;
	}
}
