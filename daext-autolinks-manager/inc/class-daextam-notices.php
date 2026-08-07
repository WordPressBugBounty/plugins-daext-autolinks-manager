<?php
/**
 * Admin notices and UI helper methods.
 *
 * This class encapsulates methods related to admin notices, dismissible notices,
 * SVG icon rendering, and license activation UI.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin notices and UI helper class.
 */
class Daextam_Notices {

	/**
	 * Shared plugin instance.
	 *
	 * @var Daextam_Shared
	 */
	private $shared;

	/**
	 * Constructor.
	 *
	 * @param Daextam_Shared $shared Shared plugin instance.
	 */
	public function __construct( $shared ) {
		$this->shared = $shared;
	}



	/**
	 * Save a dismissible notice in the "daextam_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $element_class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daextam_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daextam_dismissible_notice_a" WordPress option.
		update_option( 'daextam_dismissible_notice_a', $dismissible_notice_a );
	}



	/**
	 * Display a notice to the user to activate the license.
	 *
	 * @return void
	 */
	public function display_license_activation_notice() {
		return;
	}




	/**
	 * Get the current page URL with the daextam_verify_license=1 parameter and a nonce.
	 *
	 * @return string|null
	 */
	public function get_current_admin_page_verify_license_url() {
		return null;
	}


	/**
	 * Display the dismissible notices stored in the "daextam_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daextam_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->shared->get_admin_helper()->echo_icon_svg( 'x' ); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daextam_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daextam_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}
}
