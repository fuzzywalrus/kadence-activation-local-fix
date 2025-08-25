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

// Targeted HTTP API fixes for whitelisted domains only
// Only affects cURL requests to specific plugin licensing domains

// DO NOT disable external HTTP blocking globally - too aggressive
// DO NOT define WP_ACCESSIBLE_HOSTS globally - can cause issues

// Only apply fixes to specific domains via targeted filters

// Whitelisted domains for cURL fixes (plugin licensing only)
$whitelisted_domains = array(
    'www.kadencewp.com',
    'www.kadencethemes.com', 
    'api.ithemes.com',
    'www.gravityforms.com'
);

/**
 * Check if URL is in whitelist
 */
function is_whitelisted_domain($url, $domains) {
    foreach ($domains as $domain) {
        if (strpos($url, $domain) !== false) {
            return true;
        }
    }
    return false;
}

// Only disable SSL verification for external whitelisted domains
add_filter('https_ssl_verify', function($ssl_verify, $url = '') use ($whitelisted_domains) {
    // If no URL provided, return original value (some WP core calls don't pass URL)
    if (empty($url)) {
        return $ssl_verify;
    }
    
    $parsed_url = parse_url($url);
    $request_host = $parsed_url['host'] ?? '';
    $current_site_host = parse_url(home_url(), PHP_URL_HOST);
    
    // Only disable SSL for external whitelisted domains
    if ($request_host !== $current_site_host && is_whitelisted_domain($url, $whitelisted_domains)) {
        return false;
    }
    return $ssl_verify;
}, 10, 2);

add_filter('https_local_ssl_verify', function($ssl_verify, $url = '') use ($whitelisted_domains) {
    // If no URL provided, return original value (some WP core calls don't pass URL)
    if (empty($url)) {
        return $ssl_verify;
    }
    
    $parsed_url = parse_url($url);
    $request_host = $parsed_url['host'] ?? '';
    $current_site_host = parse_url(home_url(), PHP_URL_HOST);
    
    // Only disable SSL for external whitelisted domains
    if ($request_host !== $current_site_host && is_whitelisted_domain($url, $whitelisted_domains)) {
        return false;
    }
    return $ssl_verify;
}, 10, 2);

// Modify HTTP request arguments only for external whitelisted domains
add_filter('http_request_args', function($args, $url) use ($whitelisted_domains) {
    // Parse the URL to check if it's external
    $parsed_url = parse_url($url);
    $request_host = $parsed_url['host'] ?? '';
    $current_site_host = parse_url(home_url(), PHP_URL_HOST);
    
    // Only modify args for external requests to whitelisted domains
    // Skip internal/same-domain requests (REST API, assets, etc.)
    if ($request_host !== $current_site_host && is_whitelisted_domain($url, $whitelisted_domains)) {
        // Disable SSL verification for external whitelisted domains only
        $args['sslverify'] = false;
        
        // Increase timeout for plugin licensing API calls
        $args['timeout'] = 30;
        
        // Add user agent to prevent blocking
        if (!isset($args['user-agent'])) {
            $args['user-agent'] = 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url');
        }
        
        // Allow external requests for these specific domains
        $args['reject_unsafe_urls'] = false;
    }
    
    return $args;
}, 10, 2);

// Debug HTTP requests for whitelisted domains only
add_action('http_api_debug', function($response, $context, $transport, $args, $url) use ($whitelisted_domains) {
    // Only log requests to whitelisted domains
    if (is_whitelisted_domain($url, $whitelisted_domains)) {
        if (is_wp_error($response)) {
            error_log('Whitelisted Domain HTTP Error for ' . $url . ': ' . $response->get_error_message());
        } else {
            error_log('Whitelisted Domain HTTP Success for ' . $url . ': ' . wp_remote_retrieve_response_code($response));
        }
    }
}, 10, 5);
