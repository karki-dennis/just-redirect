<?php
/**
 * Plugin Name: Just Redirect
 * Plugin URI:  https://example.com
 * Description: A simple plugin that performs URL redirects with an admin settings page to manage, download the redirect rules, and clean up on uninstall. Now supports HTTP Status Code option.
 * Version:     1.3
 * Author:      Your Name
 * Author URI:  https://roastedpanda.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-redirect-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/* ================================
   FRONT-END REDIRECT LOGIC
   ================================ */

/**
 * Checks the current request URL and performs a redirect if a match is found.
 */
function sr_handle_redirects() {
    // Retrieve the redirect rules from the plugin settings.
    // Expected format: array( 'old-path' => array( 'new_url' => '...', 'status_code' => 301 ), ... )
    $redirect_rules = get_option( 'sr_redirect_rules', array() );

    // Get the current request URI (e.g., "/old-page").
    $request_uri = $_SERVER['REQUEST_URI'];
    // Remove any query string and ensure a trailing slash for normalization.
    $request_path = strtok( $request_uri, '?' );
    $request_path = trailingslashit( $request_path );

    // Loop through each rule and check for a match.
    foreach ( $redirect_rules as $old_path => $rule ) {
        if ( is_array( $rule ) ) {
            $new_url     = isset( $rule['new_url'] ) ? $rule['new_url'] : '';
            $status_code = isset( $rule['status_code'] ) ? intval( $rule['status_code'] ) : 301;
        } else {
            // Fallback for legacy format.
            $new_url     = $rule;
            $status_code = 301;
        }

        // Normalize the old path.
        $normalized_old_path = trailingslashit( $old_path );
        if ( $request_path === $normalized_old_path ) {
            // Perform the redirect using the specified HTTP status code.
            wp_redirect( $new_url, $status_code );
            exit;
        }
    }
}
add_action( 'template_redirect', 'sr_handle_redirects' );


/* ================================
   ADMIN SETTINGS PAGE & DOWNLOAD
   ================================ */

if ( is_admin() ) {
    // Add the settings page.
    add_action( 'admin_menu', 'sr_add_admin_menu' );
    // Register settings.
    add_action( 'admin_init', 'sr_settings_init' );
    // Handle CSV download action.
    add_action( 'admin_post_sr_download_redirects', 'sr_download_redirects' );
}

/**
 * Adds an options page under the "Settings" menu.
 */
function sr_add_admin_menu() {
    add_options_page(
        __( 'Simple Redirect Settings', 'simple-redirect-plugin' ),
        __( 'Simple Redirect', 'simple-redirect-plugin' ),
        'manage_options',
        'simple_redirect',
        'sr_options_page'
    );
}

/**
 * Registers the plugin settings, sections, and fields.
 */
function sr_settings_init() {
    register_setting( 'sr_options_group', 'sr_redirect_rules', 'sr_validate_redirect_rules' );

    add_settings_section(
        'sr_options_section',
        __( 'Redirect Rules', 'simple-redirect-plugin' ),
        'sr_options_section_callback',
        'simple_redirect'
    );

    add_settings_field(
        'sr_redirect_rules_field',
        __( 'Redirect Rules', 'simple-redirect-plugin' ),
        'sr_redirect_rules_field_render',
        'simple_redirect',
        'sr_options_section'
    );
}

/**
 * Section description callback.
 */
function sr_options_section_callback() {
    echo '<p>' . __( 'Enter one redirect per line in the format: <code>/old-path,https://example.com/new-url,HTTP_STATUS_CODE</code>. The HTTP status code is optional and defaults to 301 if omitted.', 'simple-redirect-plugin' ) . '</p>';
}

/**
 * Renders the textarea for redirect rules.
 */
function sr_redirect_rules_field_render() {
    // Retrieve the current redirect rules.
    $rules = get_option( 'sr_redirect_rules', array() );
    $lines = array();

    // Convert the rules array into a newline-separated string.
    if ( is_array( $rules ) ) {
        foreach ( $rules as $old_path => $rule ) {
            if ( is_array( $rule ) ) {
                $new_url     = isset( $rule['new_url'] ) ? $rule['new_url'] : '';
                $status_code = isset( $rule['status_code'] ) ? $rule['status_code'] : 301;
                $lines[]     = $old_path . ',' . $new_url . ',' . $status_code;
            } else {
                // Fallback for legacy stored data.
                $lines[] = $old_path . ',' . $rule;
            }
        }
    }
    $value = implode( "\n", $lines );
    ?>
    <textarea cols="50" rows="10" name="sr_redirect_rules"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description"><?php _e( 'Example: /old-page,https://example.com/new-page,302', 'simple-redirect-plugin' ); ?></p>
    <?php
}

/**
 * Sanitizes and validates the input from the textarea.
 *
 * Expected input: Each line should contain an old path, a new URL, and an optional HTTP status code, separated by commas.
 *
 * @param mixed $input The raw textarea input.
 * @return array The sanitized redirect rules array.
 */
function sr_validate_redirect_rules( $input ) {
    // If the input is already an array (for example, saved from a previous version), return it directly.
    if ( is_array( $input ) ) {
        return $input;
    }

    $rules = array();
    // Split the input into lines.
    $lines = explode( "\n", $input );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        // Split the line into parts; limit to 3 parts.
        $parts = explode( ',', $line, 3 );
        if ( count( $parts ) < 2 ) {
            continue; // Skip improperly formatted lines.
        }
        $old_path = trim( $parts[0] );
        $new_url  = trim( $parts[1] );
        $status_code = 301; // Default status code.
        if ( isset( $parts[2] ) ) {
            $status_code = intval( trim( $parts[2] ) );
            if ( $status_code < 100 ) { // Basic check to ensure a valid status code.
                $status_code = 301;
            }
        }
        $rules[ $old_path ] = array(
            'new_url'     => $new_url,
            'status_code' => $status_code,
        );
    }
    return $rules;
}

/**
 * Renders the settings page.
 */
function sr_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Simple Redirect Settings', 'simple-redirect-plugin' ); ?></h1>
        <form action="options.php" method="post">
            <?php
                // Output security fields for the registered setting.
                settings_fields( 'sr_options_group' );
                // Output setting sections and their fields.
                do_settings_sections( 'simple_redirect' );
                // Output save settings button.
                submit_button();
            ?>
        </form>
        <hr>
        <h2><?php _e( 'Download Redirects', 'simple-redirect-plugin' ); ?></h2>
        <p><?php _e( 'Click the button below to download your current redirect rules as a CSV file.', 'simple-redirect-plugin' ); ?></p>
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=sr_download_redirects' ) ); ?>" class="button button-secondary">
                <?php _e( 'Download Redirects', 'simple-redirect-plugin' ); ?>
            </a>
        </p>
    </div>
    <?php
}

/**
 * Handles the CSV download of redirect rules.
 */
function sr_download_redirects() {
    // Verify the current user's capability.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'simple-redirect-plugin' ) );
    }

    // Retrieve the redirect rules.
    $rules = get_option( 'sr_redirect_rules', array() );

    // Set headers to force download of a CSV file.
    $filename = 'redirects-' . date( 'Y-m-d' ) . '.csv';
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . $filename );

    // Open output stream.
    $output = fopen( 'php://output', 'w' );
    // Output CSV header row.
    fputcsv( $output, array( 'Old Path', 'New URL', 'HTTP Status' ) );

    // Output each redirect rule.
    if ( is_array( $rules ) ) {
        foreach ( $rules as $old_path => $rule ) {
            if ( is_array( $rule ) ) {
                $new_url     = isset( $rule['new_url'] ) ? $rule['new_url'] : '';
                $status_code = isset( $rule['status_code'] ) ? $rule['status_code'] : 301;
            } else {
                $new_url     = $rule;
                $status_code = 301;
            }
            fputcsv( $output, array( $old_path, $new_url, $status_code ) );
        }
    }
    fclose( $output );
    exit;
}


/* ================================
   UNINSTALL HANDLING
   ================================ */

/**
 * Cleanup function to remove plugin options when uninstalled.
 */
function sr_uninstall_cleanup() {
    delete_option( 'sr_redirect_rules' );
}
register_uninstall_hook( __FILE__, 'sr_uninstall_cleanup' );
