<?php
/**
 * Handle ChatGPT integration
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

namespace TutorPro\ChatGPT;

/**
 * Init Class
 *
 * @since 2.1.8
 */
class Init {

	/**
	 * Register hooks and dependencies.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$this->include_files();
		spl_autoload_register( array( $this, 'loader' ) );

		add_action( 'admin_footer', array( $this, 'load_modal' ) );
		add_action( 'wp_footer', array( $this, 'load_modal' ) );

		new Assets();
		new Ajax();
		new Settings();
	}

	/**
	 * Handle class autoload.
	 *
	 * @since 2.1.8
	 *
	 * @param string $className class name.
	 * 
	 * @return void
	 */
	private function loader( $className ) {
		if ( ! class_exists( $className ) ) {
			$className = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$className
			);

			$className = str_replace( 'TutorPro/ChatGPT' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $className );
			$file_name = tutor_chatgpt()->path . $className . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Include files.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	private function include_files() {
		include_once TUTOR_CHATGPT_DIR . 'includes/functions.php';
	}

	/**
	 * Load ChatGPT prompt modal.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function load_modal() {
		include_once tutor_chatgpt()->views . 'prompt-modal.php';
		include_once tutor_chatgpt()->views . 'api-key-modal.php';
	}

}
