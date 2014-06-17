<?php
/*
Plugin Name: Post Display Counter
Version: 1.0
Author: Carlo Roosen, Elena Mukhina
Author URI: http://www.carloroosen.com/
*/

define( 'PDC_PLUGIN_VERSION', '1.0' );
define( 'PDC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'add_meta_boxes', 'pdc_metaboxes_add' );
add_action( 'admin_menu', 'pdc_plugin_menu' );
add_action( 'plugins_loaded', 'pdc_load_translation_file' );
add_action( 'save_post','pdc_metaboxes_save', 10, 2 );
add_action( 'wp_ajax_pdc_count_views', 'pdc_count_views' );
add_action( 'wp_ajax_nopriv_pdc_count_views', 'pdc_count_views' );
add_action( 'wp_ajax_pdc_count_served', 'pdc_count_served' );
add_action( 'wp_ajax_nopriv_pdc_count_served', 'pdc_count_served' );
add_action( 'wp_enqueue_scripts', 'pdc_scripts_and_styles' );

add_filter( 'the_content', 'pdc_print_counters' );
add_filter( 'the_title', 'pdc_wrap_the_title', 10, 2 );

function pdc_plugin_menu() {
	if ( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'plugins.php' && isset( $_GET['page'] ) && $_GET['page'] == 'post-display-counter' ) {
		// Check permissions
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'post-display-counter' ) );
		}
		
		if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
			update_option( 'pdc_hide_counter_line', $_POST[ 'pdc_hide_counter_line' ] );
			wp_redirect( home_url( '/wp-admin/plugins.php?page=post-display-counter&saved=true' ) );
		}
	}
	
	add_plugins_page( 'Post Display Counter', 'Post Display Counter', 'manage_options', 'post-display-counter', 'pdc_plugin_page' );
}

function pdc_plugin_page() {
	global $wpdb;
	global $cmdr_fields_to_hide;
	
	// Check permissions
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'post-display-counter' ) );
	}
	
	if ( isset( $_REQUEST['saved'] ) )
		echo '<div id="message" class="updated fade"><p><strong> ' . __( 'Settings saved.', 'post-display-counter' ) . '</strong></p></div>';
	if ( isset( $_REQUEST['error'] ) )
		echo '<div id="message" class="updated fade"><p><strong> ' . __( 'Post Display Counter error.', 'post-display-counter' ) . '<br />' . __( urldecode( $_REQUEST['error'] ), 'post-display-counter' ) . '</strong></p></div>';
	?>
	<div class="wrap">
		<div id="icon-themes" class="icon32">
			<br>
		</div>
		<form method="post">
			<h2><?php _e( 'Post Display Counter Options', 'post-display-counter' ); ?></h2>
			<div class="inside">
				<table border="0">
					<tbody>
						<tr>
							<td colspan="2"><h3><?php _e( 'General', 'post-display-counter' );?></h3></td>
						</tr>
						<tr>
							<td>
								<label><input type="checkbox" name="pdc_hide_counter_line" value="1"<?php echo ( get_option( 'pdc_hide_counter_line' ) ? ' checked="true"' : '' ); ?> /> <?php echo _e( 'Hide counter line', 'post-display-counter' ); ?></label>
							</td>
						</tr>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<?php
}

function pdc_metaboxes_add() {
	global $post;

	if ( $post && $post->ID ) {
		add_meta_box( 'pdc_counter', __( 'Post display counter', 'post-display-counter' ), 'pdc_counter_meta_box', $post->post_type, 'normal', 'core' );
	}
}

function pdc_counter_meta_box( $post ) {
	$post_id = $post->ID;
	
	echo '<fieldset>';
	echo '<p><input type="hidden" name="pdc_hide_counter" value="0" /><input type="checkbox" id="pdc_hide_counter" name="pdc_hide_counter" value="1"' . ( get_post_meta( $post_id, 'pdc_hide_counter', true ) ? ' checked="checked"' : '' ) . ' /> <label for="pdc_hide_counter">' . __( 'Hide counter', 'post-display-counter' ) . '</label></p>';
	echo '<p>' . __( 'Views', 'post-display-counter' ) . ': ' . ( int ) get_post_meta( $post_id, 'pdc_count_views', true ) . '</p>';
	echo '<p>' . __( 'Served', 'post-display-counter' ) . ': ' . ( int ) get_post_meta( $post_id, 'pdc_count_served', true ) . '</p>';
	echo '</fieldset>';
}

function pdc_load_translation_file() {
	load_plugin_textdomain( 'post-display-counter', '', PDC_PLUGIN_PATH . 'translations' );
}

function pdc_metaboxes_save( $post_id, $post ) {
	if ( ! current_user_can( 'edit_posts' ) )
		return false;

	if ( ( basename( $_SERVER[ 'PHP_SELF' ] ) == 'post.php' || basename( $_SERVER[ 'PHP_SELF' ] ) == 'post-new.php' ) && $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
		update_post_meta( $post->ID, 'pdc_hide_counter', $_POST[ 'pdc_hide_counter' ] );
	}
}

function pdc_count_views() {
	$post_id = $_POST[ 'post_id' ];
	if ( $post_id && get_post( $post_id ) ) {
		$c = get_post_meta( $post_id, 'pdc_count_views', true );
		$c ++;
		update_post_meta( $post_id, 'pdc_count_views', $c );
	}

	$responce->status = 'OK';
	
	die();
}

function pdc_count_served() {
	$post_id = $_POST[ 'post_id' ];
	if ( $post_id && get_post( $post_id ) ) {
		$c = get_post_meta( $post_id, 'pdc_count_served', true );
		$c ++;
		update_post_meta( $post_id, 'pdc_count_served', $c );
	}

	$responce->status = 'OK';
	
	die();
}

function pdc_scripts_and_styles() {
	wp_enqueue_script( 'post-display-counter', PDC_PLUGIN_URL . 'js/post-display-counter.js', array( 'jquery' ), PDC_PLUGIN_VERSION, true );
	wp_localize_script( 'post-display-counter', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function pdc_print_counters( $content ) {
	global $post;
	
	if ( ! get_option( 'pdc_hide_counter_line' ) ) {
		$content = get_counters( $post->ID ) . $content;
	}

	return $content;
}

function pdc_wrap_the_title( $content, $post_id ) {
	global $post;
	
	if ( in_the_loop() && $post_id == $post->ID ) {
		$attr = array(
			'class="countable"',
			'data-served-id="' . $post->ID . '"'
		);
		if ( is_singular() ) {
			$attr[] = 'data-view-id="' . $post->ID . '"';
		}
		
		$content = '<span ' . implode( ' ', $attr ) . '>' . $content . '</span>';
	}

	return $content;
}

/* Service functions */
function get_countable_title( $post_id ) {
	$content = get_the_title( $post_id );
	
	if ( get_post( $post_id ) ) {
		$attr = array(
			'class = "countable"',
			'data-served-id="' . $post_id . '"'
		);

		$content = '<span ' . implode( ' ', $attr ) . '>' . $content . '</span>';
	}
	
	return $content;
}

function count_remove() {
	remove_filter( 'the_title', 'pdc_wrap_the_title', 10 );
}

function count_restore() {
	add_filter( 'the_title', 'pdc_wrap_the_title', 10, 2 );
}

function get_counters( $post_id ) {
	if ( ! get_post_meta( $post_id, 'pdc_hide_counter', true ) ) {
		return '<p class="counterdisplay">' . get_counter_served( $post_id ) . ' ' . __( 'x served', 'post-display-counter' ) . ' &amp; ' . get_counter_views( $post_id ) . ' ' . __( 'x viewed', 'post-display-counter' ) . '</p>';
	} else {
		return '';
	}
}

function get_counter_views( $post_id ) {
	global $post;
	
	if ( $post_id == $post->ID && is_singular() ) {
		return get_post_meta( $post_id, 'pdc_count_views', true ) + 1;
	} else {
		return ( int ) get_post_meta( $post_id, 'pdc_count_views', true );
	}
}

function get_counter_served( $post_id ) {
	return get_post_meta( $post_id, 'pdc_count_served', true ) + 1;
}
