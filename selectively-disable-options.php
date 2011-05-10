<?php
/*
Plugin Name: Selectively Disable Options
Plugin URI: 
Description: Remove settings that you do not want users to change
Author: Sam Margulies
Version: 1.0
Author URI: 
*/

if ( ! class_exists( 'Selectively_Disable_Options' ) ) :

/**
 * Base class.
 *
 * @package Selectively_Disable_Options
 *
 */
class Selectively_Disable_Options {

	/**
	 * DB version.
	 *
	 * @var int
	 */
	var $db_version = 0;

	/**
	 * Options.
	 *
	 * @var array
	 */
	var $options = array();

	/**
	 * Option name in DB.
	 *
	 * @var string
	 */
	var $option_name = 'selectively_disable_options';

	/**
	 * Constructor. Adds hooks.
	 */
	function Selectively_Disable_Options() {
		// Bail without 3.0.
		if ( ! function_exists( '__return_false' ) )
			return;

		// Registers the uninstall hook.
		register_activation_hook( __FILE__, array( 'Selectively_Disable_Options', 'on_activation' ) );
		
		// Load options
		$this->options = get_option( $this->option_name );

		// Disable options
		add_action('init', array( &$this, 'add_option_filters' ), 0 );
		
		// Set admin hooks
		if ( ! is_admin() )
			return;

		// Textdomain and upgrade routine.
		add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
		
		// Hook in css and javascript
		add_action( 'admin_print_styles', array( &$this, 'print_styles' ), 0 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 0 );
		add_action( 'admin_print_scripts', array( &$this, 'print_scripts' ), 99 );	
	}

	/**
	 * Attached to admin_init. Loads the textdomain and the upgrade routine.
	 */
	function action_admin_init() {
		if ( false === $this->options || ! isset( $this->options['db_version'] ) || $this->options['db_version'] < $this->db_version ) {
			if ( ! is_array( $this->options ) )
				$this->options = array();
			$current_db_version = isset( $this->options['db_version'] ) ? $this->options['db_version'] : 0;
			$this->upgrade( $current_db_version );
			$this->options['db_version'] = $this->db_version;
			update_option( $this->option_name, $this->options );
		}
		load_plugin_textdomain('selectively_disable_options', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		//update disabled options with outside filters
		$this->options['disabled_options'] =  $this->set_disabled_options();		
		update_option( $this->option_name, $this->options );

	}

	/**
	 * Upgrade routine.
	 */
	function upgrade( $current_db_version ) {

	}


	/**
	 * Runs on activation. Simply registers the uninstall routine.
	 */
	function on_activation() {
		register_uninstall_hook( __FILE__, array( 'Selectively_Disable_Options', 'on_uninstall' ) );
	}

	/**
	 * Runs on uninstall. Removes all log data.
	 */
	function on_uninstall() {
		delete_option( 'selectively_disable_options' );
	}
	
	/* apply filters and return new list of disabled options */
	function set_disabled_options() {
		$options = array();
		if( isset( $this->options['disabled_options'] ) ) {
			$options = $this->options['disabled_options'];
		}
	 	return apply_filters( 'selectively_disabled_options', $options );
	}

	
	/* Define the option names and values that we want to force */
	function get_disabled_options() {
		if( ! isset( $this->options['disabled_options'] ) ) {
			return;
		}
	 	return $this->options['disabled_options'];
	}
		
	function add_option_filters() {
		$options = $this->get_disabled_options();
		foreach($options as $option_name => $option_value) {
			add_filter("option_$option_name", array( &$this, 'set_option_value' ) );
		}
	}
	
	/* Force the output of these options to be true  */
	function set_option_value( ) {
		//remove the hook prefix to get the real option name
		$current_filter_option = str_replace( 'option_', '', current_filter() );
		$options = $this->get_disabled_options();
		$new_option_value = $options[$current_filter_option];
		return $new_option_value;
	}
	
	/* Load our CSS */
	function print_styles() {
		$options = $this->get_disabled_options();
		if( empty($options) ) { return; }
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
	function enqueue_scripts() {
		wp_enqueue_script('jquery');
	}
	
	/* Load our javascript */
	function print_scripts() {
		$options = $this->get_disabled_options();
		if( empty($options) ) { return; }
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


}
/** Initialize. */
$GLOBALS['selectively_disable_options'] = new Selectively_Disable_Options;

endif;

?>