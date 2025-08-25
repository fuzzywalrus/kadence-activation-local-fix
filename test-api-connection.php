<?php
/**
 * Test API connection for debugging
 * TESTS APPROVED DOMAINS ONLY FOR SECURITY
 */

// Test API connection tools (removed localhost restriction)
// Available for any host to test approved domain connectivity

add_action('admin_notices', function() {
    if (isset($_GET['test_kadence_api']) && current_user_can('manage_options')) {
        echo '<div class="notice notice-info"><p><strong>Testing Kadence API Connection:</strong></p>';
        
        // Test basic connectivity
        $response = wp_remote_get('https://www.kadencewp.com/', array(
            'timeout' => 30,
            'sslverify' => false,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        ));
        
        if (is_wp_error($response)) {
            echo '<p style="color: red;">❌ Error: ' . $response->get_error_message() . '</p>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            echo '<p style="color: green;">✅ Success: HTTP ' . $code . '</p>';
        }
        
        echo '</div>';
    }
    
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info"><p><a href="' . add_query_arg('test_kadence_api', '1') . '">🔧 Test Kadence API Connection</a></p></div>';
    }
});

// Add debug info to Kadence activation page
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'kadence_plugin_activation') {
        ?>
        <script>
        console.log('Kadence API Debug Info:');
        console.log('WP_HTTP_BLOCK_EXTERNAL: <?php echo defined("WP_HTTP_BLOCK_EXTERNAL") ? (WP_HTTP_BLOCK_EXTERNAL ? "true" : "false") : "not defined"; ?>');
        console.log('PHP cURL: <?php echo function_exists("curl_init") ? "enabled" : "disabled"; ?>');
        </script>
        <?php
    }
});
