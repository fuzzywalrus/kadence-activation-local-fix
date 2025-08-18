<?php
/**
 * Temporary fix for Kadence API connection issues in Docker
 * ONLY APPLIES TO LOCALHOST DOMAINS FOR SECURITY
 */

// Only apply these fixes if we're on localhost
if (!is_localhost_environment()) {
    return; // Exit early if not localhost
}

// Function is_localhost_environment() is defined in fix-http-api.php

// Hook into WordPress init to ensure this runs early
add_action('init', function() {
    // Override the wp_safe_remote_get function calls for Kadence API
    add_filter('pre_http_request', function($response, $args, $url) {
        // Only intercept Kadence API calls
        if (strpos($url, 'kadencewp.com') !== false || strpos($url, 'kadencethemes.com') !== false) {
            // Force SSL verification off
            $args['sslverify'] = false;
            $args['timeout'] = 30;
            
            // Let WordPress handle the request normally with our modified args
            return false; // Return false to let WordPress continue with the request
        }
        
        return $response; // Don't intercept other requests
    }, 10, 3);
});

// Add debug logging for Kadence API calls
add_action('wp_loaded', function() {
    if (class_exists('Kadence_Plugin_API_Manager')) {
        error_log('Kadence Plugin API Manager class found - HTTP fixes should be active');
    }
});
