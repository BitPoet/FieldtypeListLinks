<?php namespace ProcessWire;

class FieldtypeListLinks extends Fieldtype implements Module {
	
	public static function getModuleInfo() {
		return [
			'title'			=>	__('List Link Fieldtype', __FILE__),
			'summary'		=>	__('Fieldtype for mapping values between two lists'),
			'version'		=>	'0.0.1',
			'icon'			=>	'link',
			'installs'		=>	'InputfieldListLinks'
		];
	}
	
	/**
	 * Construct the Fieldtype, make sure all dependencies are in place
	 * 
	 */
	public function __construct() {
		
		require_once(__DIR__ . '/ListLink.php');
		
		$optPath = $this->config->paths->FieldtypeOptions;
		
		require_once($optPath . 'SelectableOption.php');
		require_once($optPath . 'SelectableOptionArray.php');
		require_once($optPath . 'SelectableOptionManager.php');
		
		$this->set('leftOptionsConfig', '');
		$this->set('rightOptionsConfig', '');
		
		parent::__construct();
	}

	public function getDatabaseSchema(Field $field) {
		
		$schema = parent::getDatabaseSchema($field);

		// 'data' is a required field for any Fieldtype, and we're using it to represent our 'date' field
		$schema['data'] = 'MEDIUMTEXT';

		return $schema;
	}


	/**
	 * Return a blank ready-to-populate value
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @return EventArray
	 *
	 */
	public function getBlankValue(Page $page, Field $field) {
		return new ListLink();
	}
	
	/**
	 * Given a value, make it clean and of the correct type for storage within a Page
	 *
	 * @param Page $page
	 * @param Field $field
	 * @param ListLink $value
	 * @return ListLink|mixed
	 *
	 */
	public function sanitizeValue(Page $page, Field $field, $value) {
		// if given an invalid value, return a valid blank value
		if(!$value instanceof ListLink) {
			$val = $this->getBlankValue($page, $field);
			if(!empty($value) && is_string($value))
				$val->setFromJson($value);
		} else {
			$val = $value;
		}
		return $val;
	}

/** 
	 * Given a raw value from DB (arrays), return the value as it would appear in a Page object (EventArray)
	 *
	 * @param Page $page
	 * @param Field $field
	 * @param array $value
	 * @return ListLink
	 *
	 */
	public function ___wakeupValue(Page $page, Field $field, $value) {
		
		// start a blank value to be populated
		$links = $this->getBlankValue($page, $field);
		
		if(empty($value) || !is_string($value))
			return $links;
		
		$state = json_decode($value, true);
		if(!is_array($state))
		{
			return $links;
		}
		
		foreach($state as $l => $r) {
			$links->assign($l, $r);
		}
		
		$links->resetTrackChanges();
		return $links;
	}
	
	
	/**
	 * Get the Inputfield that provides input for this Fieldtype
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @return Inputfield
	 * 
	 */
	public function getInputfield(Page $page, Field $field) {
		
		$inputfield = $this->wire()->modules->get('InputfieldListLinks');
		
		foreach($this->getOptions($field, 'left') as $value => $label) {
			$inputfield->addLeftOption($value, $label);
		}
		
		foreach($this->getOptions($field, 'right') as $value => $label) {
			$inputfield->addRightOption($value, $label);
		}
		
		return $inputfield;
		
	}
	
	
	public function ___getOptions($field, $which) {
		
		$optString = $field->get("{$which}OptionsConfig");

		if($optString === NULL)
			$optString = '';
		
		if(!is_string($optString)) {
			throw new WireException("value must be string");
		}
		
		$optionsArray = array();
		
		foreach(explode("\n", $optString) as $line) {

			if(empty($line)) continue;

			$pos = strpos($line, '=');
			
			if($pos === false) {
				// new option
				$title = trim($line); 
				$value = $title;

			} else {
				// an equals sign is present
				$value = trim(substr($line, 0, $pos));
				$title = trim(substr($line, $pos+1));

			}
		
			$optionsArray[$value] = $title;
			
		}
		
		return $optionsArray;
	}
		
	
	/**
	 * Given an ListLink value, convert the value back to an array for storage in DB
	 *              
	 * @param Page $page
	 * @param Field $field
	 * @param ListLink $value
	 * @return array
	 *
	 */
	public function ___sleepValue(Page $page, Field $field, $value) {

		$sleepValue = array();
		if(!$value instanceof ListLink) {
			return $sleepValue;
		}
		
		$sleepValue['data'] = $value->getJsonValue();
		
		return $sleepValue;
	}
	
	
	/**
	 * Format a value for front-end output
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @param EventArray $value
	 * @return EventArray
	 *
	 */
	public function ___formatValue(Page $page, Field $field, $value) {
		return $value;
	}
	
	
	protected function getLeftOptionsConfig($field) {
		
		$f = $this->modules->get('InputfieldTextarea');
		$f->attr('name+id', 'leftOptionsConfig');
		$f->label = $this->_('Left Options');
		$f->description = $this->_('These are the options for the list on the left side');
		$f->val($field->get('leftOptionsConfig'));
		return $f;
		
	}
	

	protected function getRightOptionsConfig($field) {
		
		$f = $this->modules->get('InputfieldTextarea');
		$f->attr('name+id', 'rightOptionsConfig');
		$f->label = $this->_('Right Options');
		$f->description = $this->_('These are the options for the list on the right side');
		$f->val($field->get('rightOptionsConfig'));
		return $f;
		
	}


	/**
	 * Get Inputfields needed to configure this Fieldtype
	 * 
	 * @param Field $field
	 * @return InputfieldWrapper
	 * 
	 */
	public function ___getConfigInputfields(Field $field) {
		
		$inputfields = parent::___getConfigInputfields($field); 
		
		$inputfields->add($this->getLeftOptionsConfig($field));
		$inputfields->add($this->getRightOptionsConfig($field));
		
		return $inputfields;
	}
	
}
