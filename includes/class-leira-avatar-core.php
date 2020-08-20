<?php
/**
 * Core Avatars attachment class.
 *
 * @link       https://leira.dev
 * @since      1.0.0
 *
 * @package    Leira_Avatar
 * @subpackage Leira_Avatar/public
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Leira Avatar Attachment class.
 *
 * Extend it to manage your component's uploads.
 *
 * @since 1.0.0
 */
class Leira_Avatar_Core{

	/**
	 * Action to handle upload
	 *
	 * @var string
	 */
	protected $action = 'bp_avatar_upload';

	/**
	 * Input file containing the image
	 *
	 * @var string
	 */
	protected $file_input = 'file';

	/**
	 * Input Image max file size
	 *
	 * @var int
	 */
	protected $max_filesize = 0;

	/**
	 * Upload Error Strings. Initialized in constructor
	 *
	 * @var array
	 */
	protected $upload_error_strings = array();

	/**
	 * @var string[]
	 */
	protected $required_wp_files = array( 'file' );

	/**
	 * @var string[]
	 */
	protected $allowed_types = array( 'jpeg', 'gif', 'png' );

	/**
	 * wp_upload_dir object
	 *
	 * @var array|null
	 */
	protected $wp_upload_dir = null;

	/**
	 * The file being uploaded.
	 *
	 * @var array
	 */
	public $attachment = array();

	/**
	 * Construct Upload parameters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->max_filesize = (int) wp_max_upload_size();
		$allowed_types      = $this->get_allowed_types();
		$allowed_types      = array_map( 'strtoupper', $allowed_types );
		$comma              = _x( ',', 'avatar types separator', 'buddypress' );

		$this->upload_error_strings = array(
			0  => __( 'The file was uploaded successfully', 'buddypress' ),
			1  => __( 'The uploaded file exceeds the maximum allowed file size for this site', 'buddypress' ),
			/* translators: %s: Max file size for the file */
			2  => sprintf( __( 'The uploaded file exceeds the maximum allowed file size of: %s', 'buddypress' ), size_format( $this->max_filesize ) ),
			3  => __( 'The uploaded file was only partially uploaded.', 'buddypress' ),
			4  => __( 'No file was uploaded.', 'buddypress' ),
			5  => '',
			6  => __( 'Missing a temporary folder.', 'buddypress' ),
			7  => __( 'Failed to write file to disk.', 'buddypress' ),
			8  => __( 'File upload stopped by extension.', 'buddypress' ),
			/* translators: %s: Max file size for the profile photo */
			9  => sprintf( _x( 'That photo is too big. Please upload one smaller than %s', 'profile photo upload error', 'buddypress' ), size_format( $this->max_filesize ) ),
			/* translators: %s: comma separated list of file types allowed for the profile photo */
			10 => sprintf( _nx( 'Please upload only this file type: %s.', 'Please upload only these file types: %s.', count( $allowed_types ), 'profile photo upload error', 'buddypress' ), join( $comma . ' ', $allowed_types ) ),
		);

		$this->wp_upload_dir = wp_upload_dir();
	}

	/**
	 * Upload the avatar attachment.
	 *
	 * @param array       $file The appropriate entry the from $_FILES superglobal.
	 * @param string|null $time Optional. Time formatted in 'yyyy/mm'. Default null.
	 *
	 * @return array On success, returns an associative array of file attributes.
	 *               On failure, returns an array containing the error message
	 *               (eg: array( 'error' => $message ) )
	 * @since 1.0.0
	 */
	public function upload( $file, $time = null ) {

		/**
		 * Add custom rules before enabling the file upload
		 */
		add_filter( "{$this->action}_prefilter", array( $this, 'validate_upload' ), 10, 1 );

		// Set Default overrides.
		$overrides = array(
			'action'               => $this->action,
			'upload_error_strings' => $this->upload_error_strings,
		);

		/**
		 * If you need to add some overrides we haven't thought of.
		 *
		 * @param array $overrides The wp_handle_upload overrides
		 */
		$overrides = apply_filters( 'leira_avatar_attachment_upload_overrides', $overrides );

		$this->includes();

		/**
		 * If the $base_dir was set when constructing the class,
		 * and no specific filter has been requested, use a default
		 * filter to create the specific $base dir
		 */
		$upload_dir_filter = array( $this, 'upload_dir_filter' );

		// Make sure the file will be uploaded in the avatars directory.
		add_filter( 'upload_dir', $upload_dir_filter, 10 );

		// Helper for utf-8 filenames.
		add_filter( 'sanitize_file_name', array( $this, 'sanitize_utf8_filename' ) );

		// Upload the attachment.
		$this->attachment = wp_handle_upload( $file[ $this->file_input ], $overrides, $time );

		remove_filter( 'sanitize_file_name', array( $this, 'sanitize_utf8_filename' ) );

		// Restore WordPress Uploads data.
		remove_filter( 'upload_dir', $upload_dir_filter, 10 );

		// Finally return the uploaded file or the error.
		return $this->attachment;
	}

	/**
	 * Avatar specific rules.
	 *
	 * Adds an error if the avatar size or type don't match our needs.
	 * The error code is the index of $upload_error_strings.
	 *
	 * @param array $file the temporary file attributes (before it has been moved).
	 *
	 * @return array the file with extra errors if needed.
	 * @since 1.0.0
	 */
	public function validate_upload( $file = array() ) {
		// Bail if already an error.
		if ( ! empty( $file['error'] ) ) {
			return $file;
		}
		/**
		 * Check for is logged in
		 */

		// File size is too big.
		if ( $file['size'] > $this->max_filesize ) {
			$file['error'] = 9;

			// File is of invalid type.
		} else {
			$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $this->get_allowed_mimes() );

			if ( ! empty( $filetype['ext'] ) && ! empty( $filetype['type'] ) ) {
				//valid mime
			} else {
				//invalid mime
				$file['error'] = 10;
			}
		}

		// Return with error code attached.
		return $file;
	}

	/**
	 * Include the WordPress core needed files.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		foreach ( array_unique( $this->required_wp_files ) as $wp_file ) {
			if ( ! file_exists( ABSPATH . "/wp-admin/includes/{$wp_file}.php" ) ) {
				continue;
			}

			require_once( ABSPATH . "/wp-admin/includes/{$wp_file}.php" );
		}
	}

	/**
	 * Helper to convert utf-8 characters in filenames to their ASCII equivalent.
	 *
	 * @param string $retval Filename.
	 *
	 * @return string
	 * @since 2.9.0
	 *
	 */
	public function sanitize_utf8_filename( $retval ) {
		// PHP 5.4+ or with PECL intl 2.0+
		if ( function_exists( 'transliterator_transliterate' ) && seems_utf8( $retval ) ) {
			$retval = transliterator_transliterate( 'Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $retval );

			// Older.
		} else {
			// Use WP's built-in function to convert accents to their ASCII equivalent.
			$retval = remove_accents( $retval );

			// Still here? use iconv().
			if ( function_exists( 'iconv' ) && seems_utf8( $retval ) ) {
				$retval = iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $retval );
			}
		}

		return $retval;
	}

	/**
	 * This method is responsible for telling WP to upload the file to our avatars folder
	 *
	 * @param array $upload_dir The original Uploads dir.
	 *
	 * @return array The upload directory data.
	 * @since 1.0.0
	 */
	public function upload_dir_filter( $upload_dir = array() ) {

		$user_id = get_current_user_id();

		$path      = $this->get_user_avatar_folder_dir( $user_id );
		$newbdir   = $path;
		$newurl    = $this->get_user_avatar_folder_url( $user_id );
		$newburl   = $newurl;
		$newsubdir = $this->get_user_avatar_subdir( $user_id );

		/**
		 * Filters the avatar upload directory for a user.
		 *
		 * @param array $value Array containing the path, URL, and other helpful settings.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'leira_avatar_upload_dir', array(
			'path'    => $path,
			'url'     => $newurl,
			'subdir'  => $newsubdir,
			'basedir' => $newbdir,
			'baseurl' => $newburl,
			'error'   => false
		) );
	}

	/**
	 * Get allowed avatar types.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_allowed_types() {
		/**
		 * Defaults
		 */
		$allowed_types = $this->allowed_types;

		/**
		 * Filters the list of allowed image types.
		 *
		 * @param array $allowed_types List of image types.
		 *
		 * @since 1.0.0
		 */
		$avatar_types = (array) apply_filters( 'leira_avatar_get_allowed_types', $allowed_types );

		if ( empty( $avatar_types ) ) {
			$avatar_types = $allowed_types;
		} else {
			$avatar_types = array_intersect( $allowed_types, $avatar_types );
		}

		return array_values( $avatar_types );
	}

	/**
	 * Get allowed avatar mime types.
	 *
	 * @return array List of allowed mime types.
	 * @since 1.0.0
	 */
	public function get_allowed_mimes() {
		$allowed_types = $this->get_allowed_types();

		$validate_mimes = wp_match_mime_types( join( ',', $allowed_types ), wp_get_mime_types() );
		$allowed_mimes  = array_map( 'implode', $validate_mimes );

		/**
		 * Include jpg type if jpeg is set
		 */
		if ( isset( $allowed_mimes['jpeg'] ) && ! isset( $allowed_mimes['jpg'] ) ) {
			$allowed_mimes['jpg'] = $allowed_mimes['jpeg'];
		}

		return $allowed_mimes;
	}

	/**
	 * Generate the avatar from image file.
	 *
	 * @param string $file The absolute path to the file.
	 *
	 * @return false|string|WP_Image_Editor|WP_Error
	 * @since 1.0.0
	 */
	public function generate( $file = '' ) {

		if ( ! file_exists( $file ) ) {
			if ( isset( $this->attachment['file'] ) ) {
				$file = $this->attachment['file'];
			} else {
				return false; //or error?
			}
		}

		// Get image size.
		$avatar_data = $this->get_image_metadata( $file );

		if ( ! $avatar_data ) {
			/**
			 * Unable to read image data, its not an image
			 */
			return false;
		}

		// Get the image editor.
		$editor = wp_get_image_editor( $file );

		if ( is_wp_error( $editor ) ) {
			return $editor;
		}

		/**
		 * delete previous avatar
		 */
		$this->delete();

		/**
		 * Start edition process
		 */
		$editor->set_quality( 90 );

		/**
		 * Check image rotation
		 */
		$angles = array(
			3 => 180,
			6 => - 90,
			8 => 90,
		);

		if ( isset( $avatar_data['meta']['orientation'] ) && isset( $angles[ $avatar_data['meta']['orientation'] ] ) ) {
			//We need to rotate the image
			$rotated = $editor->rotate( $angles[ $avatar_data['meta']['orientation'] ] );

			// Something went wrong.
			if ( is_wp_error( $rotated ) ) {
				//dont break the process
				//return $rotated;
			}
		}

		/**
		 * Check if we need to crop
		 */
		if ( isset( $avatar_data['width'] ) && isset( $avatar_data['height'] ) && $avatar_data['width'] != $avatar_data['height'] ) {
			/**
			 * Avatars is not square, lets make it
			 */
			$avatar_width  = $avatar_data['width'];
			$avatar_height = $avatar_data['height'];
			$crop_size     = min( $avatar_width, $avatar_height );

			$crop_x = ( $avatar_width / 2 ) - ( $crop_size / 2 );
			$crop_y = ( $avatar_height / 2 ) - ( $crop_size / 2 );

			$cropped = $editor->crop( $crop_x, $crop_y, $crop_size, $crop_size );

		}

		$avatar_sizes      = array(
			'full'  => 150,
			'thumb' => 50
		);
		$ext               = $avatar_data['mime'] == 'image/png' ? 'png' : 'jpg';
		$avatar_folder_dir = $this->get_user_avatar_folder_dir();
		foreach ( $avatar_sizes as $suffix => $size ) {
			$resized  = $editor->resize( $size, $size );
			$filename = wp_unique_filename( $avatar_folder_dir, uniqid() . "-bp{$suffix}.{$ext}" );
			$dest     = $avatar_folder_dir . '/' . $filename;
			$saved    = $editor->save( $dest );
		}

		//we need to unlink the file
		@unlink( $file );

		return true;
	}

	/**
	 * Get full data for an image
	 *
	 * @param string $file Absolute path to the uploaded image.
	 *
	 * @return bool|array   An associate array containing the width, height and metadatas.
	 *                      False in case an important image attribute is missing.
	 * @since 1.0.0
	 *
	 */
	protected function get_image_metadata( $file ) {
		// Try to get image basic data.
		list( $width, $height, $sourceImageType ) = $data = @getimagesize( $file );

		// No need to carry on if we couldn't get image's basic data.
		if ( is_null( $width ) || is_null( $height ) || is_null( $sourceImageType ) || ! isset( $data['mime'] ) ) {
			return false;
		}

		// Initialize the image data.
		$image_data = array(
			'width'  => $width,
			'height' => $height,
			'mime'   => $data['mime']
		);

		/**
		 * Make sure the wp_read_image_metadata function is reachable for the old Avatar UI
		 * or if WordPress < 3.9 (New Avatar UI is not available in this case)
		 */
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		// Now try to get image's meta data.
		$meta = wp_read_image_metadata( $file );
		if ( ! empty( $meta ) ) {
			$image_data['meta'] = $meta;
		}

		/**
		 * Filter here to add/remove/edit data to the image full data
		 *
		 * @param array $image_data An associate array containing the width, height and metadatas.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'leira_avatar_get_avatar_metadata', $image_data );
	}

	/**
	 * Deletes user avatar
	 *
	 * @param string $user_id The user id you want to delete avatar.
	 *                        If no user id is provided, current user id will be use
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function delete( $user_id = '' ) {
		/**
		 * Filters whether or not to handle deleting an existing avatar.
		 *
		 * If you want to override this function, make sure you return false.
		 *
		 * @since 1.0.0
		 */
		if ( ! apply_filters( 'leira_avatar_pre_delete_existing_avatar', true ) ) {
			return true;
		}
		if ( empty( $user_id ) ) {
			if ( ! get_current_user_id() ) {
				return false;
			}
			$user_id = get_current_user_id();
		}

		/**
		 * User avatar folder dir
		 */
		$avatar_folder_dir = $this->get_user_avatar_folder_dir( $user_id );
		if ( ! is_dir( $avatar_folder_dir ) ) {
			return false;
		}

		if ( $av_dir = opendir( $avatar_folder_dir ) ) {
			while( false !== ( $avatar_file = readdir( $av_dir ) ) ){
				if ( ( preg_match( "/-bpfull/", $avatar_file ) || preg_match( "/-bpthumb/", $avatar_file ) ) && '.' != $avatar_file && '..' != $avatar_file ) {
					@unlink( $avatar_folder_dir . '/' . $avatar_file );
				}
			}
		}
		closedir( $av_dir );

		@rmdir( $avatar_folder_dir );

		/**
		 * Fires after deleting an existing avatar.
		 *
		 * @param array $args Array of arguments used for avatar deletion.
		 *
		 * @since 1.0.0
		 */
		do_action( 'leira_avatar_delete_existing_avatar', $user_id );

		return true;
	}

	/**
	 * Get the avatar url for a give user
	 *
	 * @param string $user_id
	 * @param string $size
	 *
	 * @return mixed|string|void
	 */
	public function avatar( $user_id = '', $size = 'full' ) {
		if ( empty( $user_id ) ) {
			if ( ! get_current_user_id() ) {
				return '';
			}
			$user_id = get_current_user_id();
		}

		//validate size
		if ( is_integer( $size ) ) {
			$size = $size > 50 ? 'full' : 'thumb';
		}

		$avatar_folder_dir = $this->get_user_avatar_folder_dir( $user_id );
		$avatar_folder_url = $this->get_user_avatar_folder_url( $user_id );
		$avatar_size       = ( 'full' == $size ) ? '-bpfull' : '-bpthumb';
		$avatar_url        = '';

		// Open directory.
		if ( $av_dir = opendir( $avatar_folder_dir ) ) {

			// Stash files in an array once to check for one that matches.
			$avatar_files = array();
			while( false !== ( $avatar_file = readdir( $av_dir ) ) ){
				// Only add files to the array (skip directories).
				if ( 2 < strlen( $avatar_file ) ) {
					$avatar_files[] = $avatar_file;
				}
			}

			// Check for array.
			if ( 0 < count( $avatar_files ) ) {

				// Check for current avatar.
				foreach ( $avatar_files as $key => $value ) {
					if ( strpos( $value, $avatar_size ) !== false ) {
						$avatar_url = $avatar_folder_url . '/' . $avatar_files[ $key ];
					}
				}
			}
		}

		// Close the avatar directory.
		closedir( $av_dir );

		return apply_filters( 'leira_avatar_user_avatar_url', $avatar_url );
	}


	/**
	 * Get the path to uploads directory
	 *
	 * @return string
	 */
	public function get_uploads_dir() {
		$dir = '';

		if ( isset( $this->wp_upload_dir['basedir'] ) && ! empty( $this->wp_upload_dir['basedir'] ) ) {
			$dir = $this->wp_upload_dir['basedir'];
		}

		return apply_filters( 'leira_avatar_uploads_dir', $dir );
	}

	/**
	 * Get URL path to uploads directory
	 *
	 * @return string
	 */
	public function get_uploads_url() {
		$url = '';

		if ( isset( $this->wp_upload_dir['baseurl'] ) && ! empty( $this->wp_upload_dir['baseurl'] ) ) {
			$url = $this->wp_upload_dir['baseurl'];

			// Workaround for WP13941, WP15928, WP19037.
			if ( is_ssl() ) {
				$url = str_replace( 'http://', 'https://', $url );
			}
		}

		return apply_filters( 'leira_avatar_uploads_url', $url );
	}

	/**
	 * Returns path to user avatar folder
	 *
	 * @param string|int $user_id User id
	 *
	 * @return string
	 */
	public function get_user_avatar_folder_dir( $user_id = '' ) {

		$dir = $this->get_uploads_dir() . $this->get_user_avatar_subdir( $user_id );

		return apply_filters( 'leira_avatar_user_avatar_folder_dir', $dir );
	}

	/**
	 * Returns url path to user avatar folder
	 *
	 * @param string|int $user_id User id
	 *
	 * @return string
	 */
	public function get_user_avatar_folder_url( $user_id ) {

		$res = $this->get_uploads_url() . $this->get_user_avatar_subdir( $user_id );

		return apply_filters( 'leira_avatar_user_avatar_folder_url', $res );
	}

	/**
	 * Get subdirectory path to user avatar "avatars/user_id/";
	 *
	 * @param string|int $user_id User id
	 *
	 * @return string
	 */
	protected function get_user_avatar_subdir( $user_id ) {
		if ( empty( $user_id ) ) {
			if ( ! get_current_user_id() ) {
				return '';
			}
			$user_id = get_current_user_id();
		}

		$directory = $this->get_avatars_dir_name();

		return '/' . $directory . '/' . $user_id;
	}

	/**
	 * Get avatars upload folder name. Default "avatars"
	 *
	 * @return string
	 */
	public function get_avatars_dir_name() {
		return apply_filters( 'leira_avatar_upload_dir_name', 'avatars' );
	}

}


