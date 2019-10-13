jQuery(document).ready(function() {
	var pID = jQuery('#post_ID').val();

	jQuery.ajax({
		type: 'POST',
		url: idf_admin_ajaxurl,
		data: {action: 'idcf_project_id', postID: pID},
		success: function(html) {	
			jQuery('.id-metabox-short-codes .shortcode-content span[data-product]').html(jQuery.trim(html));
		}
	});
	 
	// Datepicker
	jQuery('.cmb_datepicker').each(function () {
		jQuery('#' + jQuery(this).attr('id')).datepicker({
			dateFormat: idfDatePickerFormat()
		});
		// jQuery('#' + jQuery(this).attr('id')).datepicker({ dateFormat: 'yy-mm-dd' });
		// For more options see http://jqueryui.com/demos/datepicker/#option-dateFormat
	});
	

	jQuery('.cmb_text_money').change(function () {
		var num = jQuery(this).val();
		var price = cmb_format_price(num);
		//console.log(price);
		jQuery(this).val(price);
	});

	jQuery('#ign_fund_goal').change(function () {
		var num = jQuery(this).val();
		num = num.toString().replace(/\$|\,/g,'');
		if(isNaN(num))
			num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num%100;
		num = Math.floor(num/100).toString();
		if(cents<10)
			cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
			num = num.substring(0,num.length-(4*i+3))+','+
		
		num.substring(num.length-(4*i+3));
		jQuery(this).val((((sign)?'':'-') + num + '.' + cents));

	});
	
	// File and image upload handling 
	//-------------------------------------------------------------------------------------------//
	var formfield;
	var uploadStatus = true;

	jQuery(document).bind('idfMediaSelected', function(event, attachment, inputID) {
		jQuery('#' + attachment.inputID).val(attachment.url);
	});
	
	/*jQuery('.upload_button').click(function() {
		formfield = jQuery(this).prev('input').attr('name');
		tb_show('', 'media-upload.php?post_id=' + pID + 'type=image&cbm_setting=cbm_value&TB_iframe=true');
		return false;
	});*/
	
	jQuery('.remove_file_button').on('click', function() {
		formfield = jQuery(this).attr('rel');
		jQuery('input.' + formfield).val('');
		jQuery(this).parent().remove();
		return false;
	});
	
	/*
	jQuery( 'div#gallery-settings' ).hide();
	jQuery( '.savesend input.button[value*="Insert into Post"], .media-item #go_button' ).attr( 'value', 'Use this File' );
	jQuery( '.savesend a.wp-post-thumbnail' ).hide();
	jQuery( '#media-items .align' ).hide();
	jQuery( '#media-items .url' ).hide();
	*/
	
	window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function(html) {
		if (formfield) {
			
	        if ( jQuery(html).html(html).find('img').length > 0 ) {
	        	itemurl = jQuery(html).html(html).find('img').attr('src'); // Use the URL to the size selected.
	        } else {
	        	// It's not an image. Get the URL to the file instead.
	        	var htmlBits = html.split("'"); // jQuery seems to strip out XHTML when assigning the string to an object. Use alternate method.
	        	itemurl = htmlBits[1]; // Use the URL to the file.
	        	var itemtitle = htmlBits[2];
	        	itemtitle = itemtitle.replace( '>', '' );
	        	itemtitle = itemtitle.replace( '</a>', '' );
	        }
         
			var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;
			var document = /(^.*\.pdf|doc|docx|ppt|pptx|odt|psd|eps|ai*)/gi;
			var audio = /(^.*\.mp3|m4a|ogg|wav*)/gi;
			var video = /(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;
        
			if (itemurl.match(image)) {
			 	uploadStatus = '<div class="img_status"><img src="'+itemurl+'" alt="" /><a href="#" class="remove_file_button" rel="' + formfield + '">Remove Image</a></div>';
			} else {
			// No output preview if it's not an image
			// Standard generic output if it's not an image.
				html = '<a href="'+itemurl+'" target="_blank" rel="external">View File</a>';
				uploadStatus = '<div class="no_image"><span class="file_link">'+html+'</span>&nbsp;&nbsp;&nbsp;<a href="#" class="remove_file_button" rel="' + formfield + '">Remove</a></div>';
			}

			jQuery('.' + formfield).val(itemurl);
			jQuery('.' + formfield).siblings('.cmb_upload_status').slideDown().html(uploadStatus);
			tb_remove();
        
		} else {
			window.original_send_to_editor(html);
		}
		// Clear the formfield value so the other media library popups can work as they are meant to. - 2010-11-11.
		formfield = '';
	}

	jQuery('span[addlevel]').click(function (e) {
		e.preventDefault();
		evalTinyMCE();
		var element_number = parseInt(jQuery('div[levels]').attr('levels')) + 1;
		var pre_element_number = element_number - 1;
		jQuery('div[levels]').attr('levels', element_number);
		jQuery('#levels').val(element_number);
		jQuery('div[levels]').append('<div level="'+element_number+'" class="projectmeta-levelbox">' +
			'<h1>' + idcf_localization_vars.level + ' ' + (element_number) +' </h1>' +
			'<div class="ign_project_meta ign_projectmeta_reward_title">' +
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level_title + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<input class="cmb_text" type="text" name="levels['+element_number+'][title]" id="ign_level'+element_number+'title" value="" />' +
				'</div>' +
			'</div>' +
			'<div class="ign_project_meta ign_projectmeta_reward_price">' + 
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level_price + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<input class="cmb_text_money" type="text" name="levels['+element_number+'][price]" id="ign_level'+element_number+'" value="" />' +
				'</div>' +
			'</div>' +
			'<div class="ign_project_meta ign_projectmeta_reward_desc">' +
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level + ' ' + idcf_localization_vars.short_description + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<textarea name="levels['+element_number+'][short_description]" id="ign_level'+element_number+'short_desc" cols="60" rows="4" style="width:97%"></textarea>' +
				'</div>' +
			'</div>' +
			'<div class="ign_project_meta ign_projectmeta_reward_desc">' +
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level + ' ' + idcf_localization_vars.long_description + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<textarea name="levels['+element_number+'][description]" class="tinymce" id="ign_level'+element_number+'desc" cols="60" rows="4" style="width:97%"></textarea>' +
				'</div>' +
			'</div>' +
			'<div class="ign_project_meta ign_projectmeta_reward_limit">' +
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level_limit + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<input class="cmb_text_small" type="text" name="levels['+element_number+'][limit]" id="ign_level'+element_number+'limit" value="" />' +
				'</div>' +
			'</div>' +
			'<div class="ign_project_meta ign_projectmeta_reward_order">' +
				'<label class="idcf_metabox_label">' + idcf_localization_vars.level_order + ' </label>' +
				'<div class="idcf_metabox_wrapper">' +
					'<input class="cmb_text_small" type="text" name="levels['+element_number+'][order]" id="ign_level'+element_number+'order" value="0" />' +
				'</div>' +
			'</div>' +
		'</div>');
		jQuery('.cmb_text_money').change(function () {
		var num = jQuery(this).val();
			var price = cmb_format_price(num);
			//console.log(price);
			jQuery(this).val(price);
		});
		
		if (jQuery("#wp-content-wrap").hasClass('tmce-active')) {
			tinyMCE.execCommand('mceAddEditor', false, 'ign_level'+element_number+'desc');
		}
		
		jQuery(document).trigger('idcfAddLevelAfter', [element_number]);
	});

	jQuery('span[deletelevel]').click(function (e) {
		e.preventDefault();
		var element_number = parseInt(jQuery('div[levels]').attr('levels'));
		var new_number = element_number - 1;
		jQuery('div[level="'+element_number+'"]').remove();

		if (element_number == 1) {
			jQuery('#ign_level_0').val('');
			jQuery('#ign_level0desc').html('');
		} else {
			jQuery('div[levels]').attr('levels', --element_number);
			jQuery('#levels').val(new_number);
		}
	});

	function cmb_format_price(num) {
		//console.log(num);
		if (num !== '') {
			num = num.toString().replace(/\$|\,/g,'');
			if(isNaN(num))
				num = "0";
			sign = (num == (num = Math.abs(num)));
			num = Math.floor(num*100+0.50000000001);
			cents = num%100;
			num = Math.floor(num/100).toString();
			if(cents<10)
				cents = "0" + cents;
			for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
				num = num.substring(0,num.length-(4*i+3))+','+
			
			num.substring(num.length-(4*i+3));
			return (((sign)?'':'-') + num + '.' + cents);
			//return (((sign)?'':'-') + '$' + num + '.' + cents);
		}
	}
});