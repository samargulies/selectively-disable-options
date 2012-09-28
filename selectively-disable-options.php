<?php
/*
Plugin Name: Selectively Disable Options
Plugin URI: https://github.com/samargulies/selectively-disable-options
Description: Stop users from changing options you don't want them to change
Author: Sam Margulies
Version: 2
Author URI: http://belabor.org/
*/

if ( ! class_exists( 'Selectively_Disable_Options' ) ) :

class Selectively_Disable_Options {

	/* Options */
	var $disabled_options = array();

	/* Constructor. Adds hooks. */
	function Selectively_Disable_Options() {

		// Bail without 3.0.
		if ( ! function_exists( '__return_false' ) )
			return;

		// Load options to disable
		$this->disabled_options = $this->get_disabled_options();

		// Disable options
		add_action( 'init', array( &$this, 'add_option_filters' ), 0 );
		
		// Set admin hooks
		if ( ! is_admin() ) return;

		// Hook in css and javascript
		add_action( 'admin_print_styles', array( &$this, 'print_styles' ), 0 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 0 );
		add_action( 'admin_print_scripts', array( &$this, 'print_scripts' ), 99 );	
	}
		
	/* Apply filters and return list of disabled options */
	function get_disabled_options() {

	 	return apply_filters( 'selectively_disabled_options', array() );
	}
	
	/* Use WordPress filters to force the return value of each get_option() call for our disabled options */
	function add_option_filters() {

		foreach($this->disabled_options as $option_name => $option_value) {

			add_filter( "option_$option_name", array( &$this, 'set_option_value' ) );

		}
	}
	
	/* Force the output of these options to be true  */
	function set_option_value( ) {
		//remove the hook prefix to get the real option name
		$current_filter_option = str_replace( 'option_', '', current_filter() );
		$new_option_value = $this->disabled_options[$current_filter_option];
		return $new_option_value;
	}
	
	/* Load our CSS */
	function print_styles() {

		if( empty( $this->disabled_options ) ) { return; }

		?>
		<style>
			<?php
			foreach( $this->disabled_options as $option_name => $option_value ) {
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
	function enqueue_scripts() {

		if( empty( $this->disabled_options ) ) { return; }

		wp_enqueue_script('jquery');
	}
	
	/* Load our javascript */
	function print_scripts() {

		if( empty( $this->disabled_options ) ) { return; }

		?>
		<script>
			jQuery(document).ready(function($){
				// field names to disable
				fields = [ '<?php echo implode( "','", array_keys($this->disabled_options) ); ?>' ];
				
				$.map(fields, function(field){
					$("label[for='" + field + "'] input").attr('disabled','disabled');
					$("#" + field).attr('disabled','disabled');
				});
			});
		</script>
		<?php
	}

}

/* Initialize */
$GLOBALS['selectively_disable_options'] = new Selectively_Disable_Options;

endif;
