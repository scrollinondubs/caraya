 <?php

	if( !defined( 'ABSPATH') )
	exit('Restricted Access..! Please Login.');
 
	if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	wp_register_style( 'DC_MyWP_login_logo_Styles', DC_MyWP_LoginLogo_URL.'css/styles.css' );
	wp_enqueue_style( 'DC_MyWP_login_logo_Styles' );
	
	global $current_user;
    get_currentuserinfo();
	
	if($_POST['update_MyWP_login_logo'] == 'update') {

		update_option('wp_custom_login_logo_url', $_POST['wp_custom_login_logo_url']);
		update_option('wp_custom_login_logo_height', $_POST['wp_custom_login_logo_height']);
		update_option('wp_custom_login_logo_width', $_POST['wp_custom_login_logo_width']);
		update_option('wp_custom_login_title', $_POST['wp_custom_login_title']);
		update_option('wp_custom_login_logo_fadein',$_POST['wp_custom_login_logo_fadein']);
		update_option('wp_custom_login_logo_fadetime',$_POST['wp_custom_login_logo_fadetime']);
		update_option('wp_custom_login_logo_message',$_POST['wp_custom_login_logo_message']);
		
?>
		<div class="updated"><p><strong><?php _e('Login Page Logo Updated.' ); ?></strong></p></div>
<?php
	}
		$custom_logo_url = get_option('wp_custom_login_logo_url', DC_MyWP_LoginLogo_URL.'images/mylogo.png');
		$custom_logo_height = get_option('wp_custom_login_logo_height','70');
		$custom_logo_width = get_option('wp_custom_login_logo_width','320');
		$custom_login_title = get_option('wp_custom_login_title',get_bloginfo('description'));
		$custom_login_url = get_option('wp_custom_login_url',home_url());
		$custom_logo_fadein = get_option('wp_custom_login_logo_fadein','true');
		$custom_logo_fadetime = get_option('wp_custom_login_logo_fadetime','2500');
		$custom_logo_message = get_option('wp_custom_login_logo_message','');
?>
<div class="wrap columns-2 dd-wrap">
	<h2><img src="<?php echo DC_MyWP_LoginLogo_URL.'images/plugin_header_logo.png'; ?>" alt="My Wordpress Login Logo" /></h2>
	<p>by <strong>Afsal Rahim</strong> from <strong><a title="DigitCodes.com" href="http://digitcodes.com">digitcodes.com</a></strong></p>
			
	<div class="metabox-holder has-right-sidebar" id="poststuff">
			
		<div class="inner-sidebar" id="side-info-column">
		<?php include_once( DC_MyWP_LoginLogo_PATH . '/views/subscribe.php' ); ?>
		<?php include_once( DC_MyWP_LoginLogo_PATH . '/views/plugin-info.php' ); ?>	
		</div>	
						
		<div id="post-body">
		<div id="post-body-content">
		
				<div class="stuffbox">
				<h3>Your Current Login Page Logo</h3>
				<div class="inside">
				<p class="description"><img src="<?php echo $custom_logo_url; ?>" alt="" /></p> 
				</div>
				</div>
		
				<div class="stuffbox">
				<h3>Customize Login Page </h3>
				<div class="inside">
				<form name="DC_MyWP_login_logo_form" method="post" action="">
				<input type="hidden" name="update_MyWP_login_logo" value="update">
				<h2>Customize Logo</h2> 
				<p><b>Logo Image URL : </b><input class="regular-text code" type="text" name="wp_custom_login_logo_url" value="<?php echo $custom_logo_url; ?>" size="70"><br/>
				<span class="description">Example: <code>http://yoursite.com/wp-content/uploads/2013/01/logo.png</code></span></p>
				<p><b>Logo Width : </b><input type="text" name="wp_custom_login_logo_width" value="<?php echo $custom_logo_width; ?>" size="5">px</p>
				<p><b>Logo Height : </b><input type="text" name="wp_custom_login_logo_height" value="<?php echo $custom_logo_height; ?>" size="5">px</p>
				<p><b>Logo Link : </b><input class="regular-text code" type="text" name="wp_custom_login_url" value="<?php echo $custom_login_url; ?>" size="70"><br/>
				<span class="description">This is the url opened when clicked on the logo in your login page.</p>
				<p><b>Logo Title : </b><input class="regular-text code" type="text" name="wp_custom_login_title" value="<?php echo $custom_login_title; ?>" size="40"><br/>
				<span class="description">Title or description shown on hovering mouse over the logo.</p>
				<input type="submit" class="button-primary" name="Submit" value="Update" />
				<br/>
				<h2>Customize Login Form</h2>
				<p><input id="DisableFadeIn" type="checkbox" name="wp_custom_login_logo_fadein" value="false" <?php if($custom_logo_fadein) echo "checked"; ?>><span id="DisableFadeInText"> Enable FadeIn Effect</span><br/>
				<span class="description"> Provides a fading effect to the login form</span></p>
				<p id="fadetime" <?php if(!$custom_logo_fadein) { echo 'style="display:none;"'; } ?>><b>Set fade in time : </b><input type="text" name="wp_custom_login_logo_fadetime" value="<?php echo $custom_logo_fadetime; ?>" size="5">seconds</p>
						<script type="text/javascript">// <![CDATA[
						jQuery('#DisableFadeIn').change(function(){
						  if(jQuery(this).is(':checked')){
							jQuery('#fadetime').show();
						  } else {
							jQuery('#fadetime').hide();
						  }
						});
						// ]]></script>
						
						
				<p><b>Custom Message : </b><br/><textarea class="large-text code" name="wp_custom_login_logo_message"><?php echo $custom_logo_message; ?></textarea><br/>
				<span class="description">Shows the given message below the login form. Leave this empty if you don't want to show any custom message.</p>				
				
				<input type="submit" class="button-primary" name="Submit" value="Update" />
				</form>
				</div>
				</div>
				
				<?php include_once( DC_MyWP_LoginLogo_PATH . '/views/faq.php' ); ?>
			
		</div>
		</div>

</div>