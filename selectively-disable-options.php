<?php
/*
Plugin Name: Selectively Disable Options
Plugin URI: 
Description: Remove settings that you do not want users to change
Author: Sam Margulies
Version: 1.0
Author URI: 
*/

define( 'DISABLE_OPTIONS_URL', plugin_dir_url( __FILE__ ) );

/* Define the option names and values that we want to force */
function sdo_options() {
	$options = array(
		'comment_moderation' => '1',
		'comment_registration' => '1',
	);
	
	 return apply_filters('sdo_options', $options);
}

add_action('init', 'sdo_add_option_filters', 0);

function sdo_add_option_filters() {
	$options = sdo_options();
	foreach($options as $option_name => $option_value) {
		add_filter("option_$option_name", "sdo_option_value");
		add_filter("option_$option_name", "sdo_option_value");
	}
}


/* Force the output of these options to be true  */
function sdo_option_value( $current_value ) {
	$current_filter_option = str_replace( 'option_', '', current_filter() );
	$options = sdo_options();
	return $options[$current_filter_option];
}

/* Hook in CSS and javascript */
if ( is_admin() ) {
	add_action( 'admin_print_styles', 'sdo_print_styles', 0 );
	add_action( 'admin_enqueue_scripts', 'sdo_enqueue_scripts', 0 );
	add_action( 'admin_print_scripts', 'sdo_print_scripts', 99 );	
}

/* Load our CSS */
function sdo_print_styles() {
	$options = sdo_options();
	?>
	<style>
		<?php
		foreach($options as $option_name => $option_value) {
			echo "label[for='$option_name'], #$option_name, ";
		}
		?>
		#sdo_null {
			color: gray;
		}
	</style>
	<?php
}	

/* Enqueue jquery */
function sdo_enqueue_scripts() {
	wp_enqueue_script('jquery');
}

/* Load our javascript */
function sdo_print_scripts() {
	$options = sdo_options();
	?>
	<script>
		jQuery(document).ready(function($){
			// field names to disable
			fields = [ '<?php echo implode( "','", array_keys($options) ); ?>' ];
			
			$.map(fields, function(field){
				$("label[for='" + field + "'] input").attr('disabled','disabled');
				$("#" + field).attr('disabled','disabled');
			});
		});
	</script>
	<?php
}


?>