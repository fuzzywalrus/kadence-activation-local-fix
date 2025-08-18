<?php
/**
 * Fix HTTP API issues in Docker environment
 * This is a must-use plugin that loads automatically
 * ONLY APPLIES TO LOCALHOST DOMAINS FOR SECURITY
 */

/**
 * Check if we're running on localhost
 */
if (!function_exists('is_localhost_environment')) {
    function is_localhost_environment() {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $localhost_patterns = array(
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '.local',
            '.test', 
            '.dev'
        );
        
        foreach ($localhost_patterns as $pattern) {
            if (strpos($host, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}

// Only apply these fixes if we're on localhost
if (!is_localhost_environment()) {
    return; // Exit early if not localhost
}

// Disable external HTTP blocking (localhost only)
if (!defined('WP_HTTP_BLOCK_EXTERNAL')) {
    define('WP_HTTP_BLOCK_EXTERNAL', false);
}

// Add specific hosts that are allowed (localhost only)
if (!defined('WP_ACCESSIBLE_HOSTS')) {
    define('WP_ACCESSIBLE_HOSTS', 'www.kadencewp.com,www.kadencethemes.com,api.ithemes.com,www.gravityforms.com');
}

// Disable SSL verification for development
add_filter('https_ssl_verify', '__return_false');
add_filter('https_local_ssl_verify', '__return_false');

// Modify HTTP request arguments to disable SSL verification
add_filter('http_request_args', function($args, $url) {
    // Disable SSL verification
    $args['sslverify'] = false;
    
    // Increase timeout for API calls
    $args['timeout'] = 30;
    
    // Add user agent to prevent blocking
    if (!isset($args['user-agent'])) {
        $args['user-agent'] = 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url');
    }
    
    return $args;
}, 10, 2);

// Debug HTTP requests (remove this after testing)
add_action('http_api_debug', function($response, $context, $transport, $args, $url) {
    if (is_wp_error($response)) {
        error_log('HTTP Request Error for ' . $url . ': ' . $response->get_error_message());
    } else {
        error_log('HTTP Request Success for ' . $url . ': ' . wp_remote_retrieve_response_code($response));
    }
}, 10, 5);
