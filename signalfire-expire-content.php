<?php
/**
 * Plugin Name: Signalfire Expire Content
 * Plugin URI: https://wordpress.org/plugins/signalfire-expire-content/
 * Description: Adds expiration functionality to posts and pages with customizable actions when content expires.
 * Version: 1.0.0
 * Author: Signalfire
 * Author URI: https://signalfire.com
 * Text Domain: signalfire-expire-content
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package SignalfireExpireContent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class for Signalfire Expire Content.
 *
 * @since 1.0.0
 */
class SignalfireExpireContent {

	/**
	 * Meta key for expiration date.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $meta_key_date = 'sec_expiration_date';

	/**
	 * Meta key for expiration time.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $meta_key_time = 'sec_expiration_time';

	/**
	 * Meta key for expiration action.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $meta_key_action = 'sec_expiration_action';

	/**
	 * Meta key for expiration URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $meta_key_url = 'sec_expiration_url';

	/**
	 * Constructor - Set up hooks and actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		add_action( 'wp', array( $this, 'check_expiration' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'transition_post_status', array( $this, 'handle_post_republish' ), 10, 3 );

		// Admin columns.
		add_filter( 'manage_posts_columns', array( $this, 'add_expiration_column' ) );
		add_filter( 'manage_pages_columns', array( $this, 'add_expiration_column' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'display_expiration_column' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'display_expiration_column' ), 10, 2 );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}
    
	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		load_plugin_textdomain( 'signalfire-expire-content', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Plugin activation tasks if needed.
	}
    
	/**
	 * Add meta box to post/page edit screens.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		$post_types = array( 'post', 'page' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'sec_expiration_settings',
				__( 'Expiration Settings', 'signalfire-expire-content' ),
				array( $this, 'meta_box_callback' ),
				$post_type,
				'side',
				'high'
			);
		}
	}
    
	/**
	 * Meta box callback function.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object.
	 */
	public function meta_box_callback( $post ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		wp_nonce_field( 'sec_save_expiration', 'sec_expiration_nonce' );
        
		$expiration_date   = get_post_meta( $post->ID, $this->meta_key_date, true );
		$expiration_time   = get_post_meta( $post->ID, $this->meta_key_time, true );
		$expiration_action = get_post_meta( $post->ID, $this->meta_key_action, true );
		$expiration_url    = get_post_meta( $post->ID, $this->meta_key_url, true );
        
		// Set defaults.
		if ( empty( $expiration_time ) ) {
			$expiration_time = '23:59';
		}
		if ( empty( $expiration_action ) ) {
			$expiration_action = 'draft';
		}
		?>
		<div class="sec-expiration-fields">
			<p>
				<label for="sec_expiration_date">
					<strong><?php echo esc_html__( 'Expiration Date:', 'signalfire-expire-content' ); ?></strong>
				</label><br>
				<input type="date"
					   id="sec_expiration_date"
					   name="sec_expiration_date"
					   value="<?php echo esc_attr( $expiration_date ); ?>"
					   class="widefat" />
			</p>
            
			<p>
				<label for="sec_expiration_time">
					<strong><?php echo esc_html__( 'Expiration Time:', 'signalfire-expire-content' ); ?></strong>
				</label><br>
				<input type="time"
					   id="sec_expiration_time"
					   name="sec_expiration_time"
					   value="<?php echo esc_attr( $expiration_time ); ?>"
					   class="widefat" />
			</p>
            
            <p>
                <label for="sec_expiration_action">
                    <strong><?php echo esc_html__('When Expired:', 'signalfire-expire-content'); ?></strong>
                </label><br>
                <select id="sec_expiration_action" name="sec_expiration_action" class="widefat">
                    <option value="draft" <?php selected($expiration_action, 'draft'); ?>>
                        <?php echo esc_html__('Change to Draft', 'signalfire-expire-content'); ?>
                    </option>
                    <option value="redirect" <?php selected($expiration_action, 'redirect'); ?>>
                        <?php echo esc_html__('Redirect to URL', 'signalfire-expire-content'); ?>
                    </option>
                </select>
            </p>
            
            <p id="sec_redirect_url_field" <?php echo ($expiration_action !== 'redirect') ? 'style="display:none;"' : ''; ?>>
                <label for="sec_expiration_url">
                    <strong><?php echo esc_html__('Redirect URL:', 'signalfire-expire-content'); ?></strong>
                </label><br>
                <input type="url" 
                       id="sec_expiration_url" 
                       name="sec_expiration_url" 
                       value="<?php echo esc_attr($expiration_url); ?>" 
                       class="widefat" 
                       placeholder="https://example.com" />
                <small class="description">
                    <?php echo esc_html__('Enter the full URL to redirect to when content expires.', 'signalfire-expire-content'); ?>
                </small>
            </p>
            
            <?php if ($expiration_date): ?>
            <p>
                <small class="description">
                    <?php 
                    $datetime = $expiration_date . ' ' . $expiration_time;
                    $timestamp = strtotime($datetime);
                    if ($timestamp) {
                        $formatted_date = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
                        echo sprintf(
                            /* translators: %s: formatted expiration date and time */
                            esc_html__('Content will expire on: %s', 'signalfire-expire-content'),
                            '<strong>' . esc_html($formatted_date) . '</strong>'
                        );
                    }
                    ?>
                </small>
            </p>
            <?php endif; ?>
        </div>
        
        <style>
        .sec-expiration-fields p {
            margin-bottom: 15px;
        }
        .sec-expiration-fields label {
            display: block;
            margin-bottom: 5px;
        }
        .sec-expiration-fields .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #666;
        }
        </style>
        <?php
    }
    
    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, array('post', 'page'))) {
            return;
        }
        
        wp_enqueue_script('jquery');
        
        $inline_script = "
        jQuery(document).ready(function($) {
            function toggleRedirectField() {
                var action = $('#sec_expiration_action').val();
                if (action === 'redirect') {
                    $('#sec_redirect_url_field').show();
                } else {
                    $('#sec_redirect_url_field').hide();
                }
            }
            
            $('#sec_expiration_action').on('change', toggleRedirectField);
            toggleRedirectField(); // Initialize on page load
        });
        ";
        
        wp_add_inline_script('jquery', $inline_script);
    }
    
	/**
	 * Save meta box data.
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID.
	 */
	public function save_meta_box( $post_id ) {
		// Security checks.
		$nonce = isset( $_POST['sec_expiration_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sec_expiration_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'sec_save_expiration' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sanitize and save data.
		$expiration_date   = isset( $_POST['sec_expiration_date'] ) ? sanitize_text_field( wp_unslash( $_POST['sec_expiration_date'] ) ) : '';
		$expiration_time   = isset( $_POST['sec_expiration_time'] ) ? sanitize_text_field( wp_unslash( $_POST['sec_expiration_time'] ) ) : '';
		$expiration_action = isset( $_POST['sec_expiration_action'] ) ? sanitize_text_field( wp_unslash( $_POST['sec_expiration_action'] ) ) : '';
		$expiration_url    = isset( $_POST['sec_expiration_url'] ) ? esc_url_raw( wp_unslash( $_POST['sec_expiration_url'] ) ) : '';
        
		// Validate action.
		if ( ! in_array( $expiration_action, array( 'draft', 'redirect' ), true ) ) {
			$expiration_action = 'draft';
		}

		// Validate date format.
		if ( ! empty( $expiration_date ) && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $expiration_date ) ) {
			$expiration_date = '';
		}

		// Validate time format.
		if ( ! empty( $expiration_time ) && ! preg_match( '/^\d{2}:\d{2}$/', $expiration_time ) ) {
			$expiration_time = '23:59';
		}

		// If redirect is selected but no URL provided, change action to draft.
		if ( 'redirect' === $expiration_action && empty( $expiration_url ) ) {
			$expiration_action = 'draft';
		}
        
		// Save or delete meta data.
		if ( ! empty( $expiration_date ) ) {
			update_post_meta( $post_id, $this->meta_key_date, $expiration_date );
			update_post_meta( $post_id, $this->meta_key_time, $expiration_time );
			update_post_meta( $post_id, $this->meta_key_action, $expiration_action );

			if ( 'redirect' === $expiration_action && ! empty( $expiration_url ) ) {
				update_post_meta( $post_id, $this->meta_key_url, $expiration_url );
			} else {
				delete_post_meta( $post_id, $this->meta_key_url );
			}
		} else {
			// Remove all expiration meta if no date is set.
			delete_post_meta( $post_id, $this->meta_key_date );
			delete_post_meta( $post_id, $this->meta_key_time );
			delete_post_meta( $post_id, $this->meta_key_action );
			delete_post_meta( $post_id, $this->meta_key_url );
		}
    }
    
    public function handle_post_republish($new_status, $old_status, $post) {
        // Only proceed if post is being published
        if ($new_status !== 'publish') {
            return;
        }
        
        // Only handle posts and pages
        if (!in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        // Check if this post has expiration data
        $expiration_date = get_post_meta($post->ID, $this->meta_key_date, true);
        if (empty($expiration_date)) {
            return; // No expiration set, nothing to do
        }
        
        // Check if the post was previously expired
        $expiration_time = get_post_meta($post->ID, $this->meta_key_time, true);
        if (empty($expiration_time)) {
            $expiration_time = '23:59';
        }
        
        $expiration_datetime = $expiration_date . ' ' . $expiration_time;
        $expiration_timestamp = strtotime($expiration_datetime);
        
        if ($expiration_timestamp && $expiration_timestamp <= current_time('timestamp')) {
            // Post was expired, clear all expiration data when republishing
            delete_post_meta($post->ID, $this->meta_key_date);
            delete_post_meta($post->ID, $this->meta_key_time);
            delete_post_meta($post->ID, $this->meta_key_action);
            delete_post_meta($post->ID, $this->meta_key_url);
            
            // Optional: Log this action for debugging
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf(
                    '[Signalfire Expire Content] Expiration data cleared for republished post ID: %d',
                    $post->ID
                ));
            }
        }
    }
    
    public function check_expiration() {
        if (is_admin() || !is_singular()) {
            return;
        }
        
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }
        
        $expiration_date = get_post_meta($post_id, $this->meta_key_date, true);
        if (empty($expiration_date)) {
            return;
        }
        
        $expiration_time = get_post_meta($post_id, $this->meta_key_time, true);
        if (empty($expiration_time)) {
            $expiration_time = '23:59';
        }
        
        // Create datetime string and check if expired
        $expiration_datetime = $expiration_date . ' ' . $expiration_time;
        $expiration_timestamp = strtotime($expiration_datetime);
        
        if (!$expiration_timestamp || $expiration_timestamp > current_time('timestamp')) {
            return; // Not expired yet
        }
        
        // Content has expired, perform action
        $action = get_post_meta($post_id, $this->meta_key_action, true);
        
        if ($action === 'redirect') {
            $redirect_url = get_post_meta($post_id, $this->meta_key_url, true);
            if (!empty($redirect_url)) {
                wp_redirect($redirect_url, 302);
                exit;
            }
            // Fall back to draft if no redirect URL
            $action = 'draft';
        }
        
        if ($action === 'draft') {
            // Change post status to draft
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'draft'
            ));
            
            // Clear any caching
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
            
            // Redirect to homepage or show 404
            wp_redirect(home_url(), 302);
            exit;
        }
    }
    
    public function add_expiration_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add expiration column after title
            if ($key === 'title') {
                $new_columns['expiration'] = __('Expiration', 'signalfire-expire-content');
            }
        }
        
        return $new_columns;
    }
    
    public function display_expiration_column($column, $post_id) {
        if ($column !== 'expiration') {
            return;
        }
        
        $expiration_date = get_post_meta($post_id, $this->meta_key_date, true);
        
        if (empty($expiration_date)) {
            echo '<span style="color: #999;">—</span>';
            return;
        }
        
        $expiration_time = get_post_meta($post_id, $this->meta_key_time, true);
        if (empty($expiration_time)) {
            $expiration_time = '23:59';
        }
        
        $expiration_datetime = $expiration_date . ' ' . $expiration_time;
        $expiration_timestamp = strtotime($expiration_datetime);
        
        if (!$expiration_timestamp) {
            echo '<span style="color: #999;">—</span>';
            return;
        }
        
        $formatted_date = wp_date('M j, Y g:i A', $expiration_timestamp);
        $is_expired = $expiration_timestamp <= current_time('timestamp');
        
        $action = get_post_meta($post_id, $this->meta_key_action, true);
        $action_text = ($action === 'redirect') ? __('Redirect', 'signalfire-expire-content') : __('Draft', 'signalfire-expire-content');
        
        if ($is_expired) {
            echo '<span style="color: #dc3232; font-weight: bold;">' . esc_html($formatted_date) . '</span><br>';
            echo '<small style="color: #dc3232;">(' . esc_html__('EXPIRED', 'signalfire-expire-content') . ' - ' . esc_html($action_text) . ')</small>';
        } else {
            echo '<span>' . esc_html($formatted_date) . '</span><br>';
            echo '<small style="color: #666;">(' . esc_html($action_text) . ')</small>';
        }
    }
}

// Initialize the plugin.
new SignalfireExpireContent();