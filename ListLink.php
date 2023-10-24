<?php namespace ProcessWire;

class ListLink extends WireData {

	protected $assignments = [];

	public function __construct() {
		parent::__construct();
	}
	
	public function assign($leftVal, $rightVal) {
		$this->assignments[$leftVal] = $rightVal;
	}
	
	public function isAssigned($leftVal) {
		return array_key_exists($leftVal, $this->assignments);
	}
	
	public function hasAssignment($rightVal) {
		return in_array($rightVal, $this->assignments);
	}
	
	public function getAssignments() {
		return $this->assignments;
	}
	
	public function getJsonValue() {
		return json_encode((object)$this->assignments);
	}
	
	public function setFromJson($json) {
		if(empty($json))
			return;
		$vals = json_decode($json);
		foreach($vals as $k => $v) {
			$this->assign($k, $v);
		}
	}
}

