jQuery(document).ready(function(){function e(e){if(""!==e){e=e.toString().replace(/\$|\,/g,""),isNaN(e)&&(e="0"),sign=e==(e=Math.abs(e)),e=Math.floor(100*e+.50000000001),cents=e%100,e=Math.floor(e/100).toString(),cents<10&&(cents="0"+cents);for(var t=0;t<Math.floor((e.length-(1+t))/3);t++)e=e.substring(0,e.length-(4*t+3))+","+e.substring(e.length-(4*t+3));return(sign?"":"-")+e+"."+cents}}var t=jQuery("#post_ID").val();jQuery.ajax({type:"POST",url:idf_admin_ajaxurl,data:{action:"idcf_project_id",postID:t},success:function(e){jQuery(".id-metabox-short-codes .shortcode-content span[data-product]").html(jQuery.trim(e))}}),jQuery(".cmb_datepicker").each(function(){jQuery("#"+jQuery(this).attr("id")).datepicker({dateFormat:idfDatePickerFormat()})}),jQuery(".cmb_text_money").change(function(){var t=jQuery(this).val(),l=e(t);jQuery(this).val(l)}),jQuery("#ign_fund_goal").change(function(){var e=jQuery(this).val();e=e.toString().replace(/\$|\,/g,""),isNaN(e)&&(e="0"),sign=e==(e=Math.abs(e)),e=Math.floor(100*e+.50000000001),cents=e%100,e=Math.floor(e/100).toString(),cents<10&&(cents="0"+cents);for(var t=0;t<Math.floor((e.length-(1+t))/3);t++)e=e.substring(0,e.length-(4*t+3))+","+e.substring(e.length-(4*t+3));jQuery(this).val((sign?"":"-")+e+"."+cents)});var l,a=!0;jQuery(document).bind("idfMediaSelected",function(e,t,l){jQuery("#"+t.inputID).val(t.url)}),jQuery(".remove_file_button").on("click",function(){return l=jQuery(this).attr("rel"),jQuery("input."+l).val(""),jQuery(this).parent().remove(),!1}),window.original_send_to_editor=window.send_to_editor,window.send_to_editor=function(e){if(l){if(jQuery(e).html(e).find("img").length>0)itemurl=jQuery(e).html(e).find("img").attr("src");else{var t=e.split("'");itemurl=t[1];var i=t[2];i=i.replace(">",""),i=i.replace("</a>","")}var r=/(^.*\.jpg|jpeg|png|gif|ico*)/gi,s=/(^.*\.pdf|doc|docx|ppt|pptx|odt|psd|eps|ai*)/gi,n=/(^.*\.mp3|m4a|ogg|wav*)/gi,c=/(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;itemurl.match(r)?a='<div class="img_status"><img src="'+itemurl+'" alt="" /><a href="#" class="remove_file_button" rel="'+l+'">Remove Image</a></div>':(e='<a href="'+itemurl+'" target="_blank" rel="external">View File</a>',a='<div class="no_image"><span class="file_link">'+e+'</span>&nbsp;&nbsp;&nbsp;<a href="#" class="remove_file_button" rel="'+l+'">Remove</a></div>'),jQuery("."+l).val(itemurl),jQuery("."+l).siblings(".cmb_upload_status").slideDown().html(a),tb_remove()}else window.original_send_to_editor(e);l=""},jQuery("span[addlevel]").click(function(t){t.preventDefault(),evalTinyMCE();var l=parseInt(jQuery("div[levels]").attr("levels"))+1,a=l-1;jQuery("div[levels]").attr("levels",l),jQuery("#levels").val(l),jQuery("div[levels]").append('<div level="'+l+'" class="projectmeta-levelbox"><h1>'+idcf_localization_vars.level+" "+l+' </h1><div class="ign_project_meta ign_projectmeta_reward_title"><label class="idcf_metabox_label">'+idcf_localization_vars.level_title+' </label><div class="idcf_metabox_wrapper"><input class="cmb_text" type="text" name="levels['+l+'][title]" id="ign_level'+l+'title" value="" /></div></div><div class="ign_project_meta ign_projectmeta_reward_price"><label class="idcf_metabox_label">'+idcf_localization_vars.level_price+' </label><div class="idcf_metabox_wrapper"><input class="cmb_text_money" type="text" name="levels['+l+'][price]" id="ign_level'+l+'" value="" /></div></div><div class="ign_project_meta ign_projectmeta_reward_desc"><label class="idcf_metabox_label">'+idcf_localization_vars.level+" "+idcf_localization_vars.short_description+' </label><div class="idcf_metabox_wrapper"><textarea name="levels['+l+'][short_description]" id="ign_level'+l+'short_desc" cols="60" rows="4" style="width:97%"></textarea></div></div><div class="ign_project_meta ign_projectmeta_reward_desc"><label class="idcf_metabox_label">'+idcf_localization_vars.level+" "+idcf_localization_vars.long_description+' </label><div class="idcf_metabox_wrapper"><textarea name="levels['+l+'][description]" class="tinymce" id="ign_level'+l+'desc" cols="60" rows="4" style="width:97%"></textarea></div></div><div class="ign_project_meta ign_projectmeta_reward_limit"><label class="idcf_metabox_label">'+idcf_localization_vars.level_limit+' </label><div class="idcf_metabox_wrapper"><input class="cmb_text_small" type="text" name="levels['+l+'][limit]" id="ign_level'+l+'limit" value="" /></div></div><div class="ign_project_meta ign_projectmeta_reward_order"><label class="idcf_metabox_label">'+idcf_localization_vars.level_order+' </label><div class="idcf_metabox_wrapper"><input class="cmb_text_small" type="text" name="levels['+l+'][order]" id="ign_level'+l+'order" value="0" /></div></div></div>'),jQuery(".cmb_text_money").change(function(){var t=jQuery(this).val(),l=e(t);jQuery(this).val(l)}),jQuery("#wp-content-wrap").hasClass("tmce-active")&&tinyMCE.execCommand("mceAddEditor",!1,"ign_level"+l+"desc"),jQuery(document).trigger("idcfAddLevelAfter",[l])}),jQuery("span[deletelevel]").click(function(e){e.preventDefault();var t=parseInt(jQuery("div[levels]").attr("levels")),l=t-1;jQuery('div[level="'+t+'"]').remove(),1==t?(jQuery("#ign_level_0").val(""),jQuery("#ign_level0desc").html("")):(jQuery("div[levels]").attr("levels",--t),jQuery("#levels").val(l))})});