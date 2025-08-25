# WordPress Kadence HTTP API Fix for Docker Development

This collection of must-use plugins fixes common HTTP API connection issues that occur in Docker development environments, specifically for plugin licensing systems like Kadence and Gravity Forms.

## Security First

~~**IMPORTANT**: These plugins only apply their fixes when running on localhost domains for security. They will automatically detect the environment and **only work on development domains**.~~

The activation issue seems to be a problem on WPengine as well, thus this has been modified, see commit https://github.com/fuzzywalrus/kadence-activation-local-fix/tree/960cd857e41ef5c97b7418bb8246e7d16722f61c for the previous version used for local only.

~~*### Supported Development Domains:~~*
~~*- `localhost` (e.g., `localhost:8081`)~~*
~~*- `127.0.0.1`~~*
~~*- `0.0.0.0`~~*
~~*- `*.local`~~*
~~*- `*.test`~~*
~~*- `*.dev`~~*

## 📁 Plugin Files

### 1. `fix-http-api.php` - Core HTTP API Fixes
**Main plugin that handles WordPress HTTP API configuration issues.**

**What it does:**
- Disables external HTTP request blocking (`WP_HTTP_BLOCK_EXTERNAL`)
- Allows specific hosts (Kadence, Gravity Forms, iThemes APIs)
- Disables SSL verification for development
- Increases HTTP timeout to 30 seconds
- Adds proper User-Agent headers
- Provides debug logging for HTTP requests

**Configuration:**
```php
// Allowed external hosts
WP_ACCESSIBLE_HOSTS: 'www.kadencewp.com,www.kadencethemes.com,api.ithemes.com,www.gravityforms.com'
```

### 2. `kadence-api-fix.php` - Kadence-Specific Fixes
**Targeted fixes for Kadence plugin licensing API calls.**

**What it does:**
- Specifically intercepts Kadence API URLs
- Forces 30-second timeout for license validation
- Disables SSL verification for Kadence domains
- Provides debug logging for Kadence API calls

**Targets:**
- `www.kadencewp.com`
- `www.kadencethemes.com`

### 3. `test-api-connection.php` - Debug & Testing Tools
**Admin tools for testing and debugging API connections.**

**Features:**
- **Test Button**: Adds "🔧 Test Kadence API Connection" to admin notices
- **Connection Testing**: Tests actual HTTP connectivity to Kadence servers
- **Debug Console**: Logs configuration info to browser console
- 
## Installation

1. **Copy files** to your WordPress `mu-plugins` directory:
   ```
   wp-content/mu-plugins/
   ├── fix-http-api.php
   ├── kadence-api-fix.php
   ├── test-api-connection.php
   └── README.md
   ```

2. **Restart Docker** containers to apply changes:
   ```bash
   docker compose restart
   ```

3. **Test the connection** - you should see a test link in WordPress admin notices

## Usage

### For Kadence Plugin Activation:
1. Go to **Settings → Kadence License Activation**
2. The "Connection failed to the License Key API server" error should be resolved
3. You can now activate your Kadence license keys

### For Gravity Forms:
1. Go to **Forms → Settings**
2. License validation should work normally
3. Plugin updates should be available

### Testing API Connection:
1. Look for "🔧 Test Kadence API Connection" in admin notices
2. Click to test connectivity
3. See results: ✅ Success or ❌ Error with details

## 🐛 Troubleshooting

### Still seeing connection errors?
1. **Check the test tool**: Use the "Test Kadence API Connection" link
2. **Check Docker logs**: `docker logs [container-name]`
3. **Verify localhost detection**: Check browser console on Kadence activation page

### Debug Information Available:
- HTTP request success/failure logs
- Configuration status in browser console
- Real-time connectivity testing

### Common Issues:

**"Critical Error" / Fatal Error:**
- Usually caused by function redeclaration
- Make sure only one copy of each plugin exists
- Restart Docker after making changes

**Still getting "Connection failed":**
- Verify you're accessing via `localhost` (not `127.0.0.1` or IP)
- Check Docker container can reach external internet
- Test with the built-in connection tester

**Plugins not loading:**
- Ensure files are in `wp-content/mu-plugins/` (not a subdirectory)
- Check file permissions
- Restart Docker containers

## 🔄 Production Deployment

**IMPORTANT**: When deploying to production:

**These plugins are SAFE for production** - they automatically detect localhost and won't apply any fixes on live domains.

**No action needed** - the plugins will detect the production environment and remain inactive.

**WordPress security intact** - all normal WordPress HTTP security features remain active on production sites.

## Technical Details

### How Localhost Detection Works:
```php
function is_localhost_environment() {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    // Checks for localhost, 127.0.0.1, .local, .test, .dev domains
}
```

### What Gets Modified (Localhost Only):
- `WP_HTTP_BLOCK_EXTERNAL` → `false`
- `https_ssl_verify` → `false`
- `https_local_ssl_verify` → `false`
- HTTP timeout → `30 seconds`
- SSL verification → `disabled`

### WordPress Hooks Used:
- `https_ssl_verify` - Disable SSL verification
- `https_local_ssl_verify` - Disable local SSL verification  
- `http_request_args` - Modify HTTP request parameters
- `pre_http_request` - Intercept specific API calls
- `http_api_debug` - Log HTTP request results

## Support

If you're still experiencing issues:

1. **Enable WordPress debug logging**
2. **Use the test connection tool**
3. **Check Docker container logs**
4. **Verify localhost domain access**

The plugins include extensive logging to help diagnose connection issues in development environments.

## Important Notes

- **Development only**: Automatically detects and only runs on localhost
- **Docker optimized**: Designed for Docker WordPress environments  
- **Must-use plugins**: Load automatically, no activation needed
- **Security preserved**: No impact on production WordPress security
- **Plugin licensing**: Specifically fixes Kadence, Gravity Forms, and iThemes licensing

---

*These plugins solve the common "Connection failed to the License Key API server" error in Docker development environments while maintaining security on production sites.*
