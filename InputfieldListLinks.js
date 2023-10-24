$(document).ready(function() {

	const replacePatterns = (str, obj) => {
		for(const [k, v] of Object.entries(obj)) {
			str = str.replace('{' + k + '}', v);
		}
		return str;
	}

	const updateInput = (fname) => {
		var obj = {};
		$('#' + fname + '__mappings').find('a.listlink-trash').each((idx, el) => {
			obj[$(el).data('left')] = $(el).data('right');
		});
		var newVal = JSON.stringify(obj);
		$('#' + fname).val(newVal);
	}

	$('.listlink-assign').click(function(evt) {
		
		const fname = $(this).data('name');
		var cfg = config.InputfieldListLinks[fname];
		const ileft = $('#' + fname + '__left').first();
		const iright = $('#' + fname + '__right').first();
		
		if(! $(ileft).val()) {
			var msgLeft = cfg.msgLeftMissing.replace('{left}', cfg.leftLabel);
			alert(msgLeft);
			return;
		}
		if(! $(iright).val()) {
			var msgRight = cfg.msgRightMissing.replace('{right}', cfg.rightLabel);
			alert(msgRight);
			return;
		}
		
		var tbl = $('#' + fname + '__mappings').first();
		var txtLeft = $(ileft).find('option:selected').text();
		var txtRight = $(iright).find('option:selected').text();
		var tpl = cfg.rowTemplate;
		var html = replacePatterns(tpl, {
			leftText:		txtLeft,
			rightText:		txtRight,
			name:			fname,
			leftVal:		$(ileft).val(),
			rightVal:		$(iright).val()
		});
		var $newrow = $(html);
		$(tbl).find('tbody').append($newrow);
		$(ileft).find('option:selected').removeAttr('selected').attr('disabled', 'disabled');
		$(iright).find('option:selected').removeAttr('selected').attr('disabled', 'disabled');
		
		updateInput(fname);
	});
	
	$('.listlink-table').on('click', '.listlink-trash', function(evt) {
		
		const fname = $(this).data('name');
		const ileft = $('#' + fname + '__left').first();
		const iright = $('#' + fname + '__right').first();

		const nleft = $(this).data('left');
		const nright = $(this).data('right');
		
		const row = $(this).closest('tr');
		$(row).remove();
		
		$(ileft).find('option[value="' + nleft + '"]').removeAttr('disabled');
		$(iright).find('option[value="' + nright + '"]').removeAttr('disabled');
		
		updateInput(fname);
	});
	
});
