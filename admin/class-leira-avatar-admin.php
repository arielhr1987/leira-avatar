<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://leira.dev
 * @since      1.0.0
 *
 * @package    Leira_Avatar
 * @subpackage Leira_Avatar/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Leira_Avatar
 * @subpackage Leira_Avatar/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Avatar_Admin{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		/**
		 * Load croppie css
		 */

		if ( $hook === 'profile.php' || $hook === 'user-edit.php' ) {
			wp_enqueue_style( $this->plugin_name . '_croppie', plugin_dir_url( __FILE__ ) . '../public/js/node_modules/croppie/croppie.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'css/leira-avatar-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * Load croppie and admin scripts
		 */

		if ( $hook === 'profile.php' || $hook === 'user-edit.php' ) {
			add_thickbox();
			wp_enqueue_script( $this->plugin_name . '_croppie', plugin_dir_url( __FILE__ ) . '../public/js/node_modules/croppie/croppie.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'js/leira-avatar-admin.js', array( 'jquery' ), $this->version, false );
		}

	}

	/**
	 * Remove the description under the profile picture
	 *
	 * @param string  $description
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public function remove_avatar_description( $description, $user ) {
		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$description = '';
			$description = sprintf(
			/* translators: %s: Gravatar URL. */
				__( '<a href="%s" class="thickbox">Change</a>.' ),
				__( '?modal=false&TB_inline&inlineId=profile-page' )
			);
		}

		return $description;
	}

}
