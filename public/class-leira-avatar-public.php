<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://leira.dev
 * @since      1.0.0
 *
 * @package    Leira_Avatar
 * @subpackage Leira_Avatar/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Leira_Avatar
 * @subpackage Leira_Avatar/public
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Avatar_Public{

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
	 * @param string $plugin_name The name of the plugin.
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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Leira_Avatar_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Leira_Avatar_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-avatar-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Leira_Avatar_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Leira_Avatar_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-avatar-public.js', array( 'jquery' ), $this->version, false );

		//wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Render modal
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_modal() {
		?>
        <div id="leira-avatar-modal" class="modal fade show" tabindex="-1" role="dialog"
             aria-labelledby="leira-avatar-modal-label" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!--<span class="modal-title" id="leira-avatar-modal-label">Modal title</span>-->
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img id="crop" style="width: 100%; height: auto;"/>
                        <div id="leira-avatar-croppie"></div>
                        <input id="leira-avatar-uploader" type="file" accept="image/*" style="display: none">
                    </div>
                    <div class="modal-footer">
                        <!--<button type="button" class="btn btn-secondary button" data-dismiss="modal">Close</button>-->
                        <button type="button" class="btn btn-secondary button leira-avatar-open-editor">
							<?php _e( 'Select other image', 'leira-avatar' ) ?>
                        </button>
                        <button type="button" class="btn btn-primary button button-primary leira-avatar-save">
							<?php _e( 'Save', 'leira-avatar' ) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Add leira avatar classes to image tag.
	 *
	 * @param string  $avatar      The image tag
	 * @param mixed   $id_or_email The user
	 * @param integer $size        The size of the image
	 * @param boolean $default     The type of image to generate
	 * @param string  $alt         The text to show in the alt property
	 * @param array   $args        Array of arguments for image creation
	 *
	 * @return string|string[]|null
	 */
	public function add_avatar_class( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		/**
		 * System forces to generate avatar with specific format.
		 * This fix discussion settings repeated images.
		 */
		$force_default = isset( $args['force_default'] ) ? $args['force_default'] : false;
		if ( $force_default ) {
			/**
			 * WP request an specific avatar type.
			 * We are in Discussion setting page.
			 */
			return $avatar;
		}

		$pattern     = "#class='([^']*)'#";// [^\"] => match any character except ' inside class attr
		$replacement = "class='$1 %s'";
		$classes     = array();

		$user = $this->get_user_from_id_or_email( $id_or_email );

		if ( $user instanceof WP_User ) {
			$classes[] = 'leira-avatar';
			$classes[] = 'leira-avatar-user-' . $user->ID;
			$classes[] = 'leira-avatar-' . leira_avatar()->core->get_avatar_type( (int) $size );
		}

		if ( ! empty( $classes ) ) {
			$classes     = array_filter( $classes, 'trim' );
			$classes     = array_unique( $classes );
			$classes     = implode( ' ', $classes );
			$replacement = sprintf( $replacement, $classes );
			$avatar      = preg_replace( $pattern, $replacement, $avatar );
		}

		return $avatar;
	}

	/**
	 * Handle ajax upload avatar.
	 *
	 * @return string|null A JSON object containing success data if the upload succeeded
	 *                     error message otherwise.
	 * @since    1.0.0
	 * @access   public
	 */
	public function avatar_ajax_upload() {
		if ( ! (bool) ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) ) {
			wp_send_json( array(
				'success' => false,
				'message' => __( 'Invalid request, please refresh the page and try again', 'leira-avatar' )
			) );
		}

		//Check the nonce.
		if ( ! check_ajax_referer( 'update-user_' . get_current_user_id(), false, false ) ) {
			wp_send_json( array(
				'success' => false,
				'message' => __( 'Invalid request, please refresh the page and try again', 'leira-avatar' )
			) );
		}

		$user_id = isset( $_REQUEST['user'] ) ? $_REQUEST['user'] : false;
		$user_id = (int) sanitize_text_field( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$core = leira_avatar()->core;

		if ( ! $core->current_user_can_edit_others_avatar() ) {
			//current user can't update others avatar
			$user_id = get_current_user_id();
//			wp_send_json( array(
//				'success' => false,
//				'message' => __( 'You can\'t edit other user avatar', 'leira-avatar' )
//			) );
		}

		/**
		 * Upload the image to uploads/avatars/$user_id
		 */
		$upload = $core->upload( $_FILES, $user_id );

		// In case of an error, stop the process and display a feedback to the user.
		if ( ! empty( $upload['error'] ) ) {
			/* translators: %s: the upload error message */
			wp_send_json( array(
				'success' => false,
				'message' => sprintf( __( 'Upload Failed! Error was: %s', 'leira-avatar' ), $upload['error'] )
			) );
		}

		// Generate the avatar from the provided file.
		if ( ! $core->generate( $user_id ) ) { //$upload['file']
			wp_send_json( array(
				'success' => false,
				'message' => __( 'Something went wrong while generating the avatar.', 'leira-avatar' )
			) );
		}

		/**
		 * Avatar was generated correctly
		 */
		wp_send_json( array(
			'success' => true,
			'user'    => $user_id, //The user id we set
			'full'    => $core->avatar( $user_id ),
			'thumb'   => $core->avatar( $user_id, 'thumb' ),
		) );
	}


	/**
	 * Filter {@link get_avatar_url()} to use the user avatar URL.
	 *
	 * @param string $url          The URL of the avatar.
	 * @param mixed  $id_or_email  The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
	 *                             user email, WP_User object, WP_Post object, or WP_Comment object.
	 * @param array  $args         Arguments passed to get_avatar_data(), after processing.
	 *
	 * @return string
	 * @since 2.9.0
	 *
	 */
	public function get_avatar_data_url_filter( $url, $id_or_email, $args ) {

		/**
		 * System forces to generate avatar with specific format.
		 * This fix discussion settings repeated images.
		 */
		$force_default = isset( $args['force_default'] ) ? $args['force_default'] : false;
		if ( $force_default ) {
			/**
			 * WP request an specific avatar type.
			 * We are in Discussion setting page.
			 */
			return $url;
		}

		$user = $this->get_user_from_id_or_email( $id_or_email );

		// No user, so bail.
		if ( false === $user instanceof WP_User ) {
			return $url;
		}

		// Use the 'full' type if size is larger than thumb's width.
		$size = (int) $args['size'];

		// Get user custom avatar URL.
		$avatar = leira_avatar()->core->avatar( $user->ID, $size );
		if ( ! empty( $avatar ) ) {
			return $avatar;
		}

		return $url;
	}

	/**
	 * Get a WP_User object given $id_or_email parameter
	 *
	 * @param $id_or_email
	 *
	 * @return false|WP_User|null
	 */
	protected function get_user_from_id_or_email( $id_or_email ) {
		$user = null;

		// Ugh, hate duplicating code; process the user identifier.
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( $id_or_email instanceof WP_User ) {
			// User Object
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Post ) {
			// Post Object
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
		} elseif ( $id_or_email instanceof WP_Comment ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$user = get_user_by( 'id', (int) $id_or_email->user_id );
			}
		} elseif ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
		}

		return $user;
	}
}
