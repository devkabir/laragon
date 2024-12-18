<?php
/**
 * Application: Laragon | Server Index Page
 * Description: This is the main index page for the Laragon server, displaying server info, server vitals, sendmail
 * mailbox, and applications.
 * Author: Tarek Tarabichi <tarek@2tinteractive.com>
 * Improved CakePHP and Joomla detection
 *
 * Contributors:
 * - @LrkDev in v.2.1.2
 * - @luisAntonioLAGS in v.2.2.1 Spanish
 * - @martic in 2.3.5 Dynamic Hostname Detection
 *
 * Version: 2.4.0
 */

// Load language files
function loadLanguage($lang)
{
    $langFile = __DIR__ . "/assets/languages/{$lang}.json";
    if (file_exists($langFile)) {
        return json_decode(file_get_contents($langFile), true);
    }
    return [];
}

// Detect language preference (default to English)
$lang = $_GET['lang'] ?? 'en';
$translations = loadLanguage($lang);

const SERVER_TYPES = [
    'php' => 'php',
    'apache' => 'apache',
];

// Display server status
function showServerStatus(): void
{
    echo '<h1>Server Status</h1>';
    // Display server uptime
    $uptime = shell_exec('uptime');
    echo '<h2>Uptime</h2><p>' . htmlspecialchars($uptime) . '</p>';

    // Display memory usage
    $free = shell_exec('free -m');
    echo '<h2>Memory Usage (in MB)</h2><pre>' . htmlspecialchars($free) . '</pre>';

    // Display disk usage
    $df = shell_exec('df -h');
    echo '<h2>Disk Usage</h2><pre>' . htmlspecialchars($df) . '</pre>';
}

// Handle incoming query parameters
function handleQueryParameter(string $param): void
{
    switch ($param) {
        case 'info':
            phpinfo();
            break;
        case 'status':
            showServerStatus();
            break;
        default:
            throw new InvalidArgumentException("Unsupported parameter: " . htmlspecialchars($param));
    }
}

if (isset($_GET['q'])) {
    $queryParam = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
    try {
        handleQueryParameter($queryParam);
    } catch (InvalidArgumentException $e) {
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}

// Constants for clarity
const SERVER_PHP = 'php';
const SERVER_APACHE = 'apache';
const SERVER_NGINX = 'nginx';

// Retrieve server extensions
function getServerExtensions(string $server, int $columns = 2): array
{
    switch ($server) {
        case SERVER_PHP:
            $extensions = get_loaded_extensions();
            break;
        case SERVER_APACHE:
            if (function_exists('apache_get_modules')) {
                $extensions = apache_get_modules();
            } else {
                throw new Exception('Apache modules are not available on this server.');
            }
            break;
        default:
            throw new InvalidArgumentException('Invalid server name: ' . htmlspecialchars($server));
    }

    sort($extensions, SORT_STRING);
    return array_chunk($extensions, $columns);
}

// Fetch PHP version
function getPhpVersion(): array
{
    $url = 'https://www.php.net/releases/index.php?json&version=7';
    $options = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];
    $json = file_get_contents($url, false, stream_context_create($options));
    if ($json === false) {
        throw new Exception("Unable to retrieve PHP version info from the official PHP site.");
    }

    $data = json_decode($json, true);
    if ($data === null || !isset($data['version'])) {
        throw new Exception("Invalid JSON or 'version' missing in the data.");
    }

    $lastVersion = $data['version'];
    $currentVersion = phpversion();

    return [
        'lastVersion' => htmlspecialchars($lastVersion),
        'currentVersion' => htmlspecialchars($currentVersion),
        'isUpToDate' => version_compare($currentVersion, $lastVersion, '>='),
    ];
}

// Gather server information
function serverInfo(): array
{
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown Server Software';
    $serverParts = explode(' ', $serverSoftware);

    $httpdVer = $serverParts[0] ?? 'Unknown';
    $openSslVer = isset($serverParts[2]) && strpos($serverParts[2], 'OpenSSL/') === 0 ? substr($serverParts[2], 8) : 'Not available';

    $phpInfo = getPhpVersion();
    $xdebugVersion = extension_loaded('xdebug') ? phpversion('xdebug') : 'Not installed';

    // Determine web server
    $webServer = 'Unknown';
    if (stripos($serverSoftware, 'apache') !== false) {
        $webServer = 'Apache';
    } elseif (stripos($serverSoftware, 'nginx') !== false) {
        $webServer = 'Nginx';
    } elseif (stripos($serverSoftware, 'litespeed') !== false) {
        $webServer = 'LiteSpeed';
    }

    // Determine PHP SAPI
    $phpSapi = php_sapi_name();
    $isFpm = (strpos($phpSapi, 'fpm') !== false);

    return [
        'httpdVer' => htmlspecialchars($httpdVer),
        'openSsl' => htmlspecialchars($openSslVer),
        'phpVer' => htmlspecialchars($phpInfo['currentVersion']),
        'xDebug' => htmlspecialchars($xdebugVersion),
        'docRoot' => htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html'),
        'serverName' => htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'),
        'webServer' => htmlspecialchars($webServer),
        'phpSapi' => htmlspecialchars($phpSapi),
        'isFpm' => $isFpm,
    ];
}

// Retrieve MySQL version
function getSQLVersion(): string
{
    $output = shell_exec('mysql -V');
    if ($output === null) {
        return "Unknown";
    }

    if (preg_match('@[0-9]+\.[0-9]+\.[0-9-\w]+@', $output, $version)) {
        return htmlspecialchars($version[0]);
    }

    return "Unknown";
}

// Generate PHP download and changelog links
function phpDlLink(string $version, string $branch = '7', string $architecture = 'x64'): array
{
    $versionEscaped = htmlspecialchars($version, ENT_QUOTES, 'UTF-8');
    $branchEscaped = htmlspecialchars($branch, ENT_QUOTES, 'UTF-8');
    $architectureEscaped = htmlspecialchars($architecture, ENT_QUOTES, 'UTF-8');

    return [
        'changeLog' => "https://www.php.net/ChangeLog-$branchEscaped.php#$versionEscaped",
        'downLink' => "https://windows.php.net/downloads/releases/php-$versionEscaped-Win32-VC15-$architectureEscaped.zip",
    ];
}

// Determine site directory
function getSiteDir(): string
{
    $drive = strtoupper(substr(PHP_OS, 0, 1));
    $rootDir = $drive . ':/laragon/etc/apache2/sites-enabled';
    if (strpos(strtolower($rootDir), 'c:') !== false) {
        $laragonDir = str_replace('D:', 'C:', $rootDir);
    } else {
        $laragonDir = $rootDir;
    }

    if ($rootDir === false) {
        throw new RuntimeException("Unable to determine the root directory.");
    }

    if (!isset($_SERVER['SERVER_SOFTWARE']) || trim($_SERVER['SERVER_SOFTWARE']) === '') {
        throw new InvalidArgumentException("Server software is not defined in the server environment.");
    }

    $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE']);

    if (strpos($serverSoftware, 'apache') !== false) {
        return $rootDir;
    } elseif (strpos($serverSoftware, 'nginx') !== false) {
        return $rootDir;
    }

    throw new InvalidArgumentException("Unsupported server type: " . htmlspecialchars($serverSoftware));
}

// Check for WordPress updates
function checkWordPressUpdates($wpPath)
{
    $command = "cd $wpPath && wp core check-update --format=json";
    $output = shell_exec($command);

    if ($output) {
        $updates = json_decode($output, true);
        if (!empty($updates)) {
            return true;
        }
    }
    return false;
}

// Fetch local sites configuration
function getLocalSites($server = 'apache', $ignoredFiles = ['.', '..', '00-default.conf']): array
{
    try {
        $sitesDir = getSiteDir();
        $files = scandir($sitesDir);
        if ($files === false) {
            throw new Exception("Failed to scan directory: " . htmlspecialchars($sitesDir));
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }

    $scanDir = array_diff($files, $ignoredFiles);
    $sites = [];

    foreach ($scanDir as $filename) {
        $path = realpath("$sitesDir/$filename");
        if ($path === false || !is_file($path)) {
            continue;
        }

        $config = file_get_contents($path);
        if ($config === false) {
            continue;
        }

        if (
            preg_match('/^\s*ServerName\s+(.+)$/m', $config, $domainMatches) &&
            preg_match('/^\s*DocumentRoot\s+(.+)$/m', $config, $documentRootMatches)
        ) {
            $site = [
                'filename' => htmlspecialchars($filename),
                'path' => htmlspecialchars($path),
                'domain' => htmlspecialchars(str_replace(['auto.', '.conf'], '', $domainMatches[1])),
                'documentRoot' => htmlspecialchars($documentRootMatches[1]),
            ];

            if (file_exists($documentRootMatches[1] . '/wp-admin')) {
                $site['framework'] = 'WordPress';
                $site['hasUpdates'] = checkWordPressUpdates($documentRootMatches[1]);
            } elseif (file_exists($documentRootMatches[1] . '/app.py')) {
                $site['framework'] = 'Flask';
            } elseif (file_exists($documentRootMatches[1] . '/package.json')) {
                $site['framework'] = 'Node.js';
            } else {
                $site['framework'] = 'Unknown';
            }

            $sites[] = $site;
        }
    }

    return $sites;
}

// Render HTML links for local sites
function renderLinks(): string
{
    ob_start();
    $sites = getLocalSites();

    foreach ($sites as $site) {
        $httpLink = "http://" . htmlspecialchars($site['domain'], ENT_QUOTES, 'UTF-8');
        $httpsLink = "https://" . htmlspecialchars($site['domain'], ENT_QUOTES, 'UTF-8');

        echo "<div class='row w800 my-2'>";
        echo "<div class='col-md-5 text-truncate tr'><a href='" . $httpLink . "'>" . $httpLink . "</a></div>";
        echo "<div class='col-2 arrows'>&xlArr; &sext; &xrArr;</div>";
        echo "<div class='col-md-5 text-truncate tl'><a href='" . $httpsLink . "'>" . $httpsLink . "</a></div>";

        if ($site['framework'] !== 'Unknown') {
            echo "<div class='col-12'>";
            if ($site['framework'] === 'WordPress' && $site['hasUpdates']) {
                echo "<span class='badge bg-danger'>Update Available</span>";
            }
            echo "<a href='?controlApp&appPath=" . urlencode($site['documentRoot']) . "&framework=" . urlencode($site['framework']) . "&action=start' class='btn btn-success'>Start " . htmlspecialchars($site['framework']) . "</a>";
            echo "<a href='?controlApp&appPath=" . urlencode($site['documentRoot']) . "&framework=" . urlencode($site['framework']) . "&action=stop' class='btn btn-danger'>Stop " . htmlspecialchars($site['framework']) . "</a>";
            echo "</div>";
        }

        echo "</div><hr>";
    }

    return ob_get_clean();
}

$rootPath = realpath(__DIR__);
$folders = array_filter(glob($rootPath . '/*'), 'is_dir');
$ignore_dirs = ['.', '..', 'logs', 'access-logs', 'vendor', 'favicon_io', 'ablepro-90', 'assets'];

foreach ($folders as $folderPath) {
    $host = basename($folderPath);
    if (in_array($host, $ignore_dirs)) {
        continue;
    }
}

$activeTab = $_GET['tab'] ?? 'servers';
