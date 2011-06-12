<?php
require_once("URedditItem.class.php");
require_once("URedditClass.class.php");

class URedditUser extends URedditItem {
	protected $username;

	public function __construct($username){
		$this->username = $username;
	}

	public function GetUsername(){
		return $this->username;
	}

	public function GetEnrolledClasses(){
		$page = self::Get("user/{$this->username}");

		$classes = array();

		foreach(self::ExtractEnumerable($page, '<div class="class">') as $class){
			$cur = URedditClass::FromHTML($class);
			if($cur !== false){
				$classes[] = $cur;
			}
		}

		return $classes;
	}

	public function GetTaughtClasses(){
		$classes = $this->GetEnrolledClasses();

		$filteredClasses = array();
		foreach($classes as $class){
			if($class->GetTeacher() == $this->username){
				$filteredClasses[] = $class;
			}
		}

		return $filteredClasses;
	}
}