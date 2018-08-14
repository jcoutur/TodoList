<?php
abstract class DefaultHTTP{
	abstract public function getHTTP();
	abstract public function postHTTP();
	abstract public function putHTTP();
	abstract public function deleteHTTP();
	public function execute(){
		switch($_SERVER['REQUEST_METHOD']){
			case "GET":
				$this->getHTTP();
				break;
			case "POST":
				$this->postHTTP();
				break;
			case "PUT":
				$this->putHTTP();
				break;
			case "DELETE":
				$this->deleteHTTP();
				break;
		}
	}
}

