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
	

	public function tearDown(){
		$this->conn=null;
		$this->auth=null;
	}
}
