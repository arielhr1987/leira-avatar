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
	 * @access   protected
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles( $page ) {

		/**
		 * Load croppie css
		 */
		if ( $page === 'profile.php' || $page === 'user-edit.php' ) {
			wp_enqueue_style( $this->plugin_name . '_croppie', plugin_dir_url( __FILE__ ) . '../public/js/node_modules/croppie/croppie.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'css/leira-avatar-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts( $page ) {

		/**
		 * Load croppie and admin scripts
		 */

		if ( $page === 'profile.php' || $page === 'user-edit.php' ) {
			add_thickbox();
			wp_enqueue_script( $this->plugin_name . '_croppie', plugin_dir_url( __FILE__ ) . '../public/js/node_modules/croppie/croppie.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'js/leira-avatar-admin.js', array( 'jquery' ), $this->version, false );
			wp_add_inline_script( $this->plugin_name . '_admin', sprintf( 'var LeiraAvatarNonce = "%s";', wp_create_nonce( 'update-user_' . get_current_user_id() ) ) );
			wp_localize_script( $this->plugin_name . '_admin_localization', 'LeiraAvatarL10N', array(
				'some_string' => __( 'Some string to translate', 'leira-avatar' ),
			) );
		}

	}

	/**
	 * Remove the description under the profile picture
	 *
	 * @param string  $description
	 * @param WP_User $user
	 *
	 * @return string
	 * @since    1.0.0
	 * @access   public
	 */
	public function remove_avatar_description( $description, $user ) {
		if ( $this->is_admin_profile_page() || $this->is_user_edit_page() ) {
			//TODO: Translate and include aria-label and title properties
			$description = '<button class="button button-delete" data-leira-avatar="delete">Remove</button>  ' .
			               '<button class="button" data-leira-avatar="select">Select</button>';
		}

		return $description;
	}

	/**
	 * Add modal content to page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function modal_content() {

		if ( $this->is_admin_profile_page() || $this->is_user_edit_page() ) {
			//TODO: Translate and include aria-label and title properties
			?>
            <div id="leira-avatar-modal-container" style="display:none;">
                <div class="leira-avatar-modal">
                    <div>
                        <div id="leira-avatar-croppie"></div>
                        <input id="leira-avatar-uploader" type="file" accept="image/*" style="display: none">
                    </div>
                    <div class="leira-avatar-modal-footer">
                        <button class="button" data-leira-avatar="close">Cancel</button>
                        <!--<button class="button" data-leira-avatar="select">Other</button>-->
                        <button class="button button-primary" data-leira-avatar="save">Apply</button>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	/**
	 * Determine if we are in the current user admin profile page
	 *
	 * @return bool
	 * @since    1.0.0
	 * @access   public
	 */
	public function is_admin_profile_page() {
		return is_admin() && defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE;
	}

	/**
	 * Determine if we are editing a user in admin area
	 *
	 * @return bool
	 * @since    1.0.0
	 * @access   public
	 */
	public function is_user_edit_page() {
		$res = false;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			$res    = isset( $screen->id ) && $screen->id == 'user-edit';
		}

		return $res;
	}

}
