<?php
require_once("URedditItem.class.php");

class URedditClass extends URedditItem {
	public $id;
	protected $teacher;
	protected $name;
	protected $summary;

	public function __construct($id, $name){
		$this->id = $id;
		$this->name = $name;
	}

	public function GetName(){
		return $this->name;
	}

	public function GetTeacherUser(){
		$this->PopulateFields();
		return new URedditUser($this->teacher);
	}

	public function GetTeacher(){
		$this->PopulateFields();
		return $this->teacher;
	}

	public function GetSummary(){
		$this->PopulateFields();
		return $this->summary;
	}

	protected function PopulateFields(){
		if(!isset($this->teacher) || !isset($this->summary)){
			$temp = self::FromId($this->id);
			$this->teacher = $temp->teacher;
			$this->summary = $temp->summary;
		}
	}

	public static function FromId($id){
		$page = self::Get("class/$id");
		$r = self::FromHtml($page);

		$summary = self::Extract($page, '<div class="class-desc">', '</div>');
		$teacher = self::Extract($page, 'taught by', '</a>');
			$teacher = self::Extract($teacher, '>');

		$r->teacher = $teacher;
		$r->summary = $summary;

		return $r;
	}

	public static function FromHtml($html) {
		$id = self::Extract($html, '<div id="button', '"');
		$name = self::Extract($html, '<div class="class-name">', '<');


		if($id && $name){
			return new URedditClass($id, $name);
		}else{
			return false;
		}
	}
}