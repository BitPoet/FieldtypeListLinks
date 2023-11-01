<?php namespace ProcessWire;

class InputfieldListLinks extends Inputfield implements Module {

	protected $leftOptions = [];
	protected $rightOptions = [];

	public static function getModuleInfo() {
		return [
			'title'			=>	__('List Link Inputfield', __FILE__),
			'summary'		=>	__('Inputfield for mapping values between two lists'),
			'version'		=>	'0.0.2',
			'icon'			=>	'link',
			'requires'		=>	'FieldtypeListLinks'
		];
	}

	public function __construct() {
		parent::__construct();
		
		$this->set('linkSelectRows', 8);
		$this->set('leftLabel', $this->_('Left'));
		$this->set('rightLabel', $this->_('Right'));
		$this->set('buttonText', $this->_('Link Items'));
	}

	public function addLeftOption($value, $label = null) {
		$this->leftOptions[$value] = $label == null ? $value : $label;
	}

	public function addRightOption($value, $label = null) {
		$this->rightOptions[$value] = $label ?: $value;
	}
	
	
	public function renderReady(Inputfield $parent = null, $renderValueMode = false) {

		$modules = $this->wire()->modules;
		$config = $this->wire()->config;
		
		$modules->get('JqueryCore');
		
		// set js config
		$class = $this->className();
		$name  = $this->attr('name');
		
		$origSettings = $config->jsConfig($class);
		if(! $origSettings)
			$origSettings = [];
		
		$settings = [
			$this->attr('name') =>	[
				"leftLabel"			=>	$this->leftLabel,
				"rightLabel"		=>	$this->rightLabel,
				"msgLeftMissing"	=>	$this->_('No {left} option selected'),
				"msgRightMissing"	=>	$this->_('No {right} option selected'),
				"rowTemplate"		=>	'<tr><td>{leftText}</td><td>&rArr;</td><td>{rightText}</td><td><a class="fa fa-trash listlink-trash" data-name="{name}" data-left="{leftVal}" data-right="{rightVal}"> </a>'
			]
		];
		
		$config->js($class, array_merge($origSettings, $settings));

		return parent::renderReady($parent, $renderValueMode);		
	}
	

	public function ___render() {
		
		$name = $this->attr('name');
		$value = $this->attr('value');
		
		$inputClass = $this->adminTheme->getClass('input');
		
		$wrap = new InputfieldWrapper();
		$wrap->label = $this->label;
		$wrap->addClass('listlinkwrap');
		
		$mrk = $this->modules->get('InputfieldMarkup');
		$mrk->label = $this->leftLabel;
		$mrk->columnWidth = 50;
		$left = "";
		//$this->log(json_encode($this->leftOptions));
		foreach($this->leftOptions as $opt => $label) {
			//$this->log(sprintf('Adding option to left select: value="%s", label="%s"', $opt, $label));
			$state = $value->isAssigned($opt) ? 'disabled' : '';
			$left .= "<option value='$opt' $state>$label</option>";
		}
		$mrk->attr('value', "<select id='{$name}__left' class='listlink-sel' name='{$name}__left' size={$this->linkSelectRows} style='min-height: {$this->linkSelectRows}em;'>" . $left . "</select>");
		$wrap->add($mrk);
		
		$mrk = $this->modules->get('InputfieldMarkup');
		$mrk->label = $this->rightLabel;
		$mrk->columnWidth = 50;
		$right = "";
		foreach($this->rightOptions as $opt => $label) {
			$state = $value->hasAssignment($opt) ? 'disabled' : '';
			$right .= "<option value='$opt' $state>$label</option>";
		}
		$mrk->attr('value', "<select id='{$name}__right' class='listlink-sel' name='{$name}__right' size={$this->linkSelectRows} style='min-height: {$this->linkSelectRows}em;'>" . $right . "</select>");
		$wrap->add($mrk);
		
		$mrk = $this->modules->get('InputfieldMarkup');
		$mrk->skipLabel = Inputfield::skipLabelMarkup;
		$mrk->addClass('listlink-btn-wrap');
		$btn = $this->modules->get('InputfieldButton');
		$btn->attr('id+name', "{$name}__assign");
		$btn->addClass('listlink-assign ui-priority-secondary');
		$btn->attr("data-name", $name);
		$btn->set('html', '&#x2193; ' . $this->buttonText . ' &#x2193;');
		$mrk->attr('value', $btn->render());
		$wrap->add($mrk);
		
		$tbl = new MarkupAdminDataTable();
		$tbl->setId("{$name}__mappings");
		$tbl->addClass('listlink-table');
		$tbl->setEncodeEntities(false);
		$tbl->headerRow([
			'Left',
			' ',
			'Right',
			'Unlink'
		]);
		$tbl->row([' ', ' ', ' ', ' ']);
		foreach($value->getAssignments() as $l => $r) {
			$tbl->row([
				$this->getLeftLabel($l),
				'&rArr;',
				$this->getRightLabel($r),
				'<a class="fa fa-trash listlink-trash" data-name="' . $name . '" data-left="' . $l . '" data-right="' . $r . '"> </a>'
			]);
		}
		$mrk = $this->modules->get('InputfieldMarkup');
		$mrk->addClass('listlink-tbl-wrap');
		$mrk->label = $this->_("Mapped Values");
		$mrk->attr('value', $tbl->render());
		$wrap->append($mrk);

		$mrk = $this->modules->get('InputfieldMarkup');
		$mrk->skipLabel = Inputfield::skipLabelMarkup;
		$jsonVal = $value->getJsonValue();
		$mrk->attr('value', "<input type='text' id='$name' class='listlink-value' name='$name' value='$jsonVal'>");
		$wrap->append($mrk);
		
		$out = $wrap->render();
		
		return $out;
	}


	public function getLeftLabel($left) {
		return $this->leftOptions[$left];
	}

	public function getRightLabel($right) {
		return $this->rightOptions[$right];
	}


	public function ___processInput($input) {
		
		parent::___processInput($input); 
		
		$name = $this->attr('name');
		
		$val = $input[$name];

		$value = new ListLink();
		if($val) {
			$value->setFromJson($val);
		}
		
		$this->attr('value', $value);
		
	}
	
	
	public function ___getConfigInputfields() {
		
		$inputfields = parent::___getConfigInputfields();
		
		$f = $this->modules->get('InputfieldInteger');
		$f->attr('name', 'linkSelectRows');
		$f->label = $this->_("Number of Rows for Selection");
		$f->description = $this->_("Show this many rows without scrolling in the selection lists for unlinked entries");
		$f->attr('value', $this->linkSelectRows);
		$inputfields->append($f);
		
		$f = $this->modules->get('InputfieldText');
		$f->attr('name', 'leftLabel');
		$f->label = $this->_("Label for left select");
		$f->attr('value', $this->leftLabel);
		$f->columnWidth = 50;
		$inputfields->append($f);
		
		$f = $this->modules->get('InputfieldText');
		$f->attr('name', 'rightLabel');
		$f->label = $this->_("Label for right select");
		$f->attr('value', $this->rightLabel);
		$f->columnWidth = 50;
		$inputfields->append($f);
		
		$f = $this->modules->get('InputfieldText');
		$f->attr('name', 'buttonText');
		$f->label = $this->_("Text for Button to Link Items");
		$f->attr('value', $this->buttonText);
		$f->columnWidth = 50;
		$inputfields->append($f);

		return $inputfields;
		
	}

}

