jQuery(document).ready(function() {
	var selLevel;
	jQuery(document).bind('idc_lightbox_general', function(e) {
		selLevel = jQuery('.idc_lightbox:visible select[name="level_select"]').val();
		setLbValues(selLevel);
	});
	jQuery(document).bind('idc_lightbox_level_select', function(e, clickLevel) {
		selLevel = jQuery('.idc_lightbox:visible select[name="level_select"] option[data-order="'+ clickLevel +'"]').val();
		setLbValues(selLevel);
	});
	jQuery('.idc_lightbox select[name="level_select"]').change(function(e) {
		if (jQuery(this).has(':visible')) {
			selLevel = jQuery(this).val();
			setLbValues(selLevel, false);
		}
	});
	function setLbValues(selLevel, withProp) {
		if (typeof(withProp) !== 'undefined' && withProp) {
			var trueLevel = jQuery('.level_select option[value="' + selLevel + '"]');
			jQuery(trueLevel).prop('selected', true);
		}
		var levelDesc = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('desc');
		var levelPrice = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('price');
		jQuery('.idc_lightbox:visible .text p').text(levelDesc);
		jQuery('.idc_lightbox:visible span.total').data('value', levelPrice).text(levelPrice);
	}
});