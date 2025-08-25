<?php
/**
 * Kadence API connection fixes for plugin licensing
 * APPLIES TO KADENCE DOMAINS ONLY FOR SECURITY
 */

// Apply Kadence API fixes for any host (removed localhost restriction)
// This targets only Kadence domains for security

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

// Debug logging removed to prevent log clutter
// The Kadence API fixes are active automatically when needed
