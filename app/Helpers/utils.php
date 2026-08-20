<?php

/**
 * Global Utility Functions
 */

if (!function_exists('e')) {
    /**
     * Escape HTML output for XSS protection
     */
    function e(?string $value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_server_lan_ip')) {
    /**
     * Get the server's local network IPv4 address for mobile access
     */
    function get_server_lan_ip(): string {
        $ip = gethostbyname(gethostname());
        if (!empty($ip) && $ip !== '127.0.0.1' && !str_starts_with($ip, '127.')) {
            return $ip;
        }
        return '127.0.0.1';
    }
}

if (!function_exists('get_app_subfolder')) {
    function get_app_subfolder(): string {
        $raw = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = trim(dirname($raw), '/\\');
        if (empty($dir) || $dir === '.' || $dir === 'tests' || $dir === 'app' || $dir === 'public') {
            return '';
        }
        return '/' . $dir;
    }
}

if (!function_exists('base_url')) {
    /**
     * Return base URL with optional path
     */
    function base_url(string $path = ''): string {
        global $appConfig;
        $configured = $appConfig['url'] ?? '';

        // If configured with a valid public domain, use it as default
        $url = !empty($configured) ? rtrim($configured, '/\\') : 'http://localhost:8000';
        
        // Auto-detect current host/port if running dynamically
        if (isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
            
            $proto = $isHttps ? 'https://' : 'http://';
            $url = $proto . $_SERVER['HTTP_HOST'];
            $subfolder = get_app_subfolder();
            if (!empty($subfolder)) {
                $url .= '/' . trim($subfolder, '/\\');
            }
        }
        
        $url = rtrim(str_replace('\\', '/', $url), '/');
        $path = trim(str_replace('\\', '/', $path), '/');
        return empty($path) ? $url : $url . '/' . $path;
    }
}

if (!function_exists('mobile_base_url')) {
    /**
     * Return base URL optimized for mobile phone QR scanning (replaces localhost with LAN IP)
     */
    function mobile_base_url(string $path = ''): string {
        global $appConfig;
        $configured = $appConfig['url'] ?? '';

        // If APP_URL in .env is configured with a real domain or IP, use it
        if (!empty($configured) && !str_contains($configured, 'localhost') && !str_contains($configured, '127.0.0.1')) {
            $path = ltrim($path, '/');
            return empty($path) ? rtrim($configured, '/') : rtrim($configured, '/') . '/' . $path;
        }

        // Otherwise replace localhost with server's LAN IP so phones on Wi-Fi can open it
        $lanIp = get_server_lan_ip();
        $port = $_SERVER['SERVER_PORT'] ?? '8000';
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        
        $url = $proto . $lanIp;
        if ($port && $port != '80' && $port != '443') {
            $url .= ':' . $port;
        }

        $url .= get_app_subfolder();

        $path = ltrim($path, '/');
        return empty($path) ? $url : $url . '/' . $path;
    }
}

if (!function_exists('public_url')) {
    /**
     * Return always-reachable public URL for employees (using Cloudflare / configured domain or LAN IP)
     */
    function public_url(string $path = ''): string {
        global $appConfig;
        $configured = $appConfig['url'] ?? '';

        // 1. If configured with a real public domain in .env (e.g. Cloudflare tunnel), use it
        if (!empty($configured) && !str_contains($configured, 'localhost') && !str_contains($configured, '127.0.0.1')) {
            $path = ltrim($path, '/');
            return empty($path) ? rtrim($configured, '/') : rtrim($configured, '/') . '/' . $path;
        }

        // 2. If accessed via a public domain in request, use that
        if (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost') && !str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
            $proto = $isHttps ? 'https://' : 'http://';
            $url = $proto . $_SERVER['HTTP_HOST'] . get_app_subfolder();
            $path = ltrim($path, '/');
            return empty($path) ? rtrim($url, '/') : rtrim($url, '/') . '/' . $path;
        }

        // 3. Fallback to LAN IP
        return mobile_base_url($path);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Return public asset URL
     */
    function asset_url(string $path = ''): string {
        return base_url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('punch_url')) {
    /**
     * Generate punch URL for employee code (always uses public URL so QR and links work on mobile/public networks)
     */
    function punch_url(string $employeeCode): string {
        $encoded = rawurlencode(trim($employeeCode));
        return public_url('p/' . $encoded);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\Csrf::generate();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : base_url($path);
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $datetime, string $format = 'd M Y, h:i:s A'): string {
        if (empty($datetime)) return '—';
        $timestamp = strtotime($datetime);
        return $timestamp ? date($format, $timestamp) : '—';
    }
}

if (!function_exists('format_time')) {
    function format_time(?string $datetime, string $format = 'h:i A'): string {
        if (empty($datetime)) return '—';
        $timestamp = strtotime($datetime);
        return $timestamp ? date($format, $timestamp) : '—';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string {
        if (empty($date)) return '—';
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '—';
    }
}

if (!function_exists('format_minutes')) {
    function format_minutes(int $totalMinutes): string {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%dh %02dm', $hours, $minutes);
    }
}

if (!function_exists('format_seconds')) {
    function format_seconds(int $seconds): string {
        $hours = floor($seconds / 3600);
        $remainder = $seconds % 3600;
        $minutes = floor($remainder / 60);
        $secs = $remainder % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}

if (!function_exists('parse_user_agent_details')) {
    function parse_user_agent_details(?string $ua): string {
        if (empty($ua)) return 'Unknown Device';
        
        $device = 'Desktop';
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/ipad|playbook|silk/i', $ua)) {
            $device = 'Tablet';
        }

        $platform = 'Unknown OS';
        if (preg_match('/windows nt 10/i', $ua)) $platform = 'Windows 10/11';
        elseif (preg_match('/windows nt 6.3/i', $ua)) $platform = 'Windows 8.1';
        elseif (preg_match('/windows/i', $ua)) $platform = 'Windows';
        elseif (preg_match('/iphone/i', $ua)) $platform = 'iPhone iOS';
        elseif (preg_match('/ipad/i', $ua)) $platform = 'iPad OS';
        elseif (preg_match('/android/i', $ua)) $platform = 'Android';
        elseif (preg_match('/macintosh|mac os x/i', $ua)) $platform = 'Mac OS';
        elseif (preg_match('/linux/i', $ua)) $platform = 'Linux';

        $browser = 'Browser';
        if (preg_match('/edg/i', $ua)) $browser = 'Edge';
        elseif (preg_match('/chrome/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/firefox/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/opera|opr/i', $ua)) $browser = 'Opera';

        return "$device ($platform - $browser)";
    }
}

if (!function_exists('calculate_haversine_distance')) {
    /**
     * Calculate great-circle distance between two GPS coordinates in meters
     *
     * @param float $lat1 Latitude of point 1 (degrees)
     * @param float $lon1 Longitude of point 1 (degrees)
     * @param float $lat2 Latitude of point 2 (degrees)
     * @param float $lon2 Longitude of point 2 (degrees)
     * @return float Distance in meters
     */
    function calculate_haversine_distance(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371000; // Earth's mean radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }
}

if (!function_exists('format_distance')) {
    /**
     * Format distance in meters or kilometers
     */
    function format_distance(?float $meters): string {
        if ($meters === null) return '—';
        if ($meters < 1000) {
            return round($meters) . ' m';
        }
        return round($meters / 1000, 2) . ' km';
    }
}

if (!function_exists('save_base64_image')) {
    /**
     * Save base64 image data to public/uploads directory
     *
     * @param string $dataUrl Base64 data URL (e.g. data:image/jpeg;base64,...)
     * @param string $subfolder Subfolder inside public/uploads (e.g. 'punches' or 'avatars')
     * @return string|null Relative public URL or null on failure
     */
    function save_base64_image(string $dataUrl, string $subfolder = 'punches'): ?string {
        if (empty($dataUrl)) return null;

        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
            $dataUrl = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $ext = strtolower($type[1]); // jpg, png, gif, webp
            if ($ext === 'jpeg') $ext = 'jpg';
            if (!in_array($ext, ['jpg', 'png', 'webp', 'jpeg'])) {
                $ext = 'jpg';
            }
        } else {
            $ext = 'jpg';
        }

        $decoded = base64_decode($dataUrl);
        if ($decoded === false) return null;

        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = 'img_' . uniqid('', true) . '.' . $ext;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($targetPath, $decoded)) {
            return 'public/uploads/' . $subfolder . '/' . $filename;
        }

        return null;
    }
}

if (!function_exists('upload_employee_avatar')) {
    /**
     * Handle uploaded avatar image from $_FILES
     *
     * @param array $file $_FILES['photo']
     * @return string|null Relative public URL or null
     */
    function upload_employee_avatar(array $file): ?string {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            return null;
        }

        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = 'avatar_' . uniqid('', true) . '.' . $ext;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'public/uploads/avatars/' . $filename;
        }

        return null;
    }
}

if (!function_exists('uploaded_url')) {
    /**
     * Return public URL for uploaded files
     */
    function uploaded_url(?string $path): ?string {
        if (empty($path)) return null;
        return base_url(ltrim($path, '/\\'));
    }
}
