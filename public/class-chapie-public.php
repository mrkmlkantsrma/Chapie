<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://Chapie.com
 * @since      1.0.0
 *
 * @package    Chapie
 * @subpackage Chapie/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Chapie
 * @subpackage Chapie/public
 * @author     Chapie <Chapie@gmail.com>
 */
class Chapie_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chapie_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chapie_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'chapie-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'chapie-bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'chapie-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chapie-public.css', array(), $this->version, 'all' );
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chapie_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chapie_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'chapie-jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js', array(), $this->version, true );
		wp_enqueue_script( 'chapie-handlebars', 'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/3.0.0/handlebars.min.js', array(), $this->version, true );
		wp_enqueue_script( 'chapie-list', 'https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js', array(), $this->version, true );
		wp_enqueue_script( 'chapie-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js', array(), $this->version, true );
		wp_enqueue_script( 'chapie-script', plugin_dir_url( __FILE__ ) . 'js/chapie-public.js', array( 'jquery' ), time(), true );
		wp_localize_script( 'chapie-script', 'chapie_ajax_object', array('ajax_url' => admin_url( 'admin-ajax.php' ),'nonce'    => wp_create_nonce( 'chapie_nonce' ),) );

	}

}
