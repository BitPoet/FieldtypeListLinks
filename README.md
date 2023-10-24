# List Links

Fieldtype/Inputfield for mapping items from two lists 1:1 for the ProcessWire CMS/CMF

# Stability

Alpha - do not use in production environments

# Description

This Inputfield / Fieldtype provides two lists of items and allows you to associate items from the first list (let's call that one "left") to items from the second list ("right") in a 1-to-1 relationship.

Each item has a textual value and, optionally, a textual label.

## Fieldtype Configuration

If you have worked with the options Fieldtype, the configuration probably looks familiar. You will find textarea inputs on the field's Details tab, "Left options" and "Right options". Just enter your items here, one on each line. If you want to apply a label to an item, follow with an equal sign and the label text.

```
item1=Label 1
item2=Another label
```

### Note

While this looks like SelectableOptions, there are no numeric ids involved. You can, of course, use digits as values.

## Inputfield Configuration

### Number of Rows for Selection

The "left" and "right" selects display that many rows.

### Label for left select and Label for right select

These are shown as labels for the respective selects instead of just "left" or "right".

## Usage

Click an item from the left select and one from the right select, then click "Link Selected Items".

You will see the new association in the "Mapped values" table, and the associated items will be flagged with a link icon and become unselectable in the "left" and "right" select lists.

Click on the trash icon next to a mapping to remove the association.

![Animated demo](https://github.com/BitPoet/bitpoet.github.io/blob/master/img/FieldtypeListLinks_demo1.gif)

# API

## ListLink

The value of this field is a ListLink object.

### assign($left, $right)

Assigns the item $left to $right.

### isAssigned($left)

Returns true if there is an assignment with the key $left.

### hasAssignment($right)

Returns true if there is an assignment that points to $right.

### getAssignments()

Returns a an associated array of assignments.

```php
$return = [
	'left1'		=>	'right1',
	'left2'		=>	'right2',
	...
];
```

### getJsonValue()

Returns a JSON encoded object with the assignments.

### setFromJson($json)

Sets the assignment array from a JSON object.

```php
$fld->setFromJson('{"left1":"right1","left2"}');
```

## InputfieldListLinks

### addLeftOption($value, $label = null)

Add an option to the "left" select list

### addRightOption($value, $label = null)

Add an option to the "right" select list

### getLeftLabel($value)

Returns the label for the option value $value from the "left" options.

### getRightLabel($value)

Returns the label for the option value $value from the "right" options.

## FieldtypeListLinks

### Hook: FieldtypeListLinks::getOptions($field, $which)

You can modify the options in "left" and "right" with a hook after FieldtypeListLinks::getOptions.

$which is a string that contains either "left" or "right".

Example for site/ready.php:

```php
wire()->addHookAfter('FieldtypeListLinks::getOptions', function(HookEvent $event) {

	$field = $event->arguments('field');
	$which = $event->arguments('which');
	
	if($field->name !== 'testlinks')
		return;
	
	$data = $event->return;
	if($which === 'left')
		$data['pack'] = 'Pack of';
	else
		$data['wolf] = 'Wolves';
	$event->return = $data;
});
```

# License

The code in this repository is licensed under Mozilla Public License 2.0. See the file LICENSE for details.
