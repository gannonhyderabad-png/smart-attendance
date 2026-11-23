const fs = require('fs');
const filePath = 'app/Helpers/utils.php';
let content = fs.readFileSync(filePath, 'utf8');

const targetRegex = /if \(!function_exists\('mobile_base_url'\)\) \{[\s\S]*?if \(!function_exists\('asset_url'\)\)/;

const replacement = `if (!function_exists('mobile_base_url')) {
    /**
     * Return base URL optimized for mobile phone QR scanning & punch links
     */
    function mobile_base_url(string $path = ''): string {
        return public_url($path);
    }
}

if (!function_exists('public_url')) {
    /**
     * Return always-reachable public URL for employees
     */
    function public_url(string $path = ''): string {
        $path = ltrim(str_replace('\\\\', '/', $path), '/');

        // 1. If accessed via live public host or active Cloudflare tunnel domain
        if (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost') && !str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')) {
            return base_url($path);
        }

        // 2. Check if active Cloudflare tunnel URL was written by launcher
        $cfLogPath = dirname(__DIR__, 2) . '/scratch/cloudflare_url.txt';
        if (file_exists($cfLogPath)) {
            $savedUrl = trim((string)@file_get_contents($cfLogPath));
            if (!empty($savedUrl) && str_starts_with($savedUrl, 'http')) {
                return rtrim($savedUrl, '/') . '/' . $path;
            }
        }

        // 3. Configured TUNNEL_URL or APP_URL in .env
        $configured = env('TUNNEL_URL') ?: env('APP_URL');
        if (!empty($configured) && !str_contains($configured, 'localhost') && !str_contains($configured, '127.0.0.1')) {
            return rtrim($configured, '/') . '/' . $path;
        }

        // 4. Fallback to server LAN IP so any phone on the same Wi-Fi/LAN can reach it
        $lanIp = get_server_lan_ip();
        $port = $_SERVER['SERVER_PORT'] ?? '8000';
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $portSuffix = ($port && $port != '80' && $port != '443') ? ':' . $port : '';

        if (!empty($lanIp) && $lanIp !== '127.0.0.1') {
            return $proto . $lanIp . $portSuffix . '/' . $path;
        }

        return base_url($path);
    }
}

if (!function_exists('asset_url'))`;

if (!targetRegex.test(content)) {
    console.error("Target regex did not match!");
    process.exit(1);
}

content = content.replace(targetRegex, replacement);
fs.writeFileSync(filePath, content, 'utf8');
console.log("Successfully updated utils.php!");
