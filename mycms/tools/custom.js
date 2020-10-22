function onCalcSettingChange(name) {
	var value = $('input[name="' + name + '"]').val();
	var _GET = getGETParams();
	var pageId = typeof(_GET['parent_record_id']) != 'undefined' ? _GET['parent_record_id'] : _GET['record_id'];

	var data = {
		setting_id: value,
		page_id: pageId
	};
	$.ajax({
		type: 'post',
		url: 'index.php?module=custom&act=GetCalcValueBySettingId',
		data: data,
		success: function(html) {
			var valueInputName = name.replace(/SettingId/, 'Value');
			var $valueInput = $('input[name="' + valueInputName + '"]');
			var $valueInputHolder = $valueInput.closest('.fld');
			var $valueInputNode = $valueInputHolder.children();

			$valueInputHolder.append(html);
			var $valueNewInputNode = $valueInputHolder.children().last();
			if ($valueNewInputNode.attr('class') == 'inp-text') {
				var $valueNewInput = $valueNewInputNode.children('input');
				$valueNewInput.attr('name', valueInputName);

			} else if ($valueNewInputNode.attr('class') == 'inp-textarea') {
				var $valueNewInput = $valueNewInputNode.children('textarea');
				$valueNewInput.attr('name', valueInputName);

			} else if ($valueNewInputNode.attr('class') == 'inp-select') {
				var $valueNewInput = $valueNewInputNode.children('input:hidden');
				$valueNewInput.attr('name', valueInputName);
				$valueNewInput.prev('input:text').attr('name', valueInputName + '_title');
				envSetSelectHandlers($valueNewInputNode.get(0));
			}

			$valueInputNode.remove();
		}
	});
}