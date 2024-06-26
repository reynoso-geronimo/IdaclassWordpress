<?php
namespace YayExtra;

defined( 'ABSPATH' ) || exit;

add_action( 'network_admin_notices', 'YayExtra\\YayeDeactiveNotice' );
add_action( 'admin_notices', 'YayExtra\\YayeDeactiveNotice' );

function YayeDeactiveNotice() {
	if ( current_user_can( 'activate_plugins' ) ) {
		?>
	  <div class="notice notice-error is-dismissible">
	  <p>
		<strong><?php esc_html_e( 'It looks like you have another YayExtra version installed, please delete it before activating this new version. All current settings and data are still preserved.', 'yayextra' ); ?>
		<a href=""><?php esc_html_e( 'Read more details.', 'yayextra' ); ?></a>
		</strong>
	  </p>
	  </div>
		<?php
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
