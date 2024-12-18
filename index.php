<?php
require_once __DIR__ . '/server.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <title><?php echo $translations['title'] ?? 'Welcome to the Laragon Dashboard'; ?></title>

    <link href="https://fonts.googleapis.com/css?family=Pt+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,700&display=swap">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap-grid.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap-reboot.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/brands.min.css" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/fontawesome.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <script>
    $(document).ready(function() {
        $('.tab').click(function() {
            var tab_id = $(this).attr('data-tab');

            $('.tab').removeClass('active');
            $('.tab-content').removeClass('active');

            $(this).addClass('active');
            $("#" + tab_id).addClass('active');
        });

        $('#language-selector').change(function() {
            var lang = $(this).val();
            window.location.href = "?lang=" + lang;
        });
    });

    function fetchServerVitals() {
        $.ajax({
            url: 'server_vitals.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#uptime').text(data.uptime);
                $('#memory-usage').text(data.memoryUsage);
                $('#disk-usage').text(data.diskUsage);

                // Update charts
                uptimeChart.data.labels = data.uptimeLabels;
                uptimeChart.data.datasets[0].data = data.uptimeData;
                uptimeChart.update();

                memoryUsageChart.data.labels = data.memoryUsageLabels;
                memoryUsageChart.data.datasets[0].data = data.memoryUsageData;
                memoryUsageChart.update();

                diskUsageChart.data.labels = data.diskUsageLabels;
                diskUsageChart.data.datasets[0].data = data.diskUsageData;
                diskUsageChart.update();
            }
        });
    }

    // Fetch server vitals every 5 seconds
    setInterval(fetchServerVitals, 5000);
    fetchServerVitals();
    </script>
    
</head>

<body>
    <header class="header">
        <h4><?php echo $translations['header'] ?? 'Header'; ?></h4>

        <?php
$currentTime = new DateTime();
$hours = $currentTime->format('H');

if ($hours < 12) {
    $greeting = $translations['good_morning'] ?? 'Good morning';
} elseif ($hours < 18) {
    $greeting = $translations['good_afternoon'] ?? 'Good afternoon';
} else {
    $greeting = $translations['good_evening'] ?? 'Good evening';
}

echo "<h4>" . $greeting . "!</h4>";
?>

        <div>
            <select id="language-selector">
                <?php
$langFiles = glob(__DIR__ . "/assets/languages/*.json");
foreach ($langFiles as $file) {
    $langCode = basename($file, ".json");
    $selected = $lang === $langCode ? "selected" : "";
    echo "<option value='$langCode' $selected>$langCode</option>";
}
?>
            </select>
        </div>
    </header>
    <nav>
        <div class="tab <?php echo $activeTab === 'servers' ? 'active' : ''; ?>" data-tab="servers"><?php echo $translations['servers_tab'] ?? 'Servers'; ?></div>
        <div class="tab <?php echo $activeTab === 'mailbox' ? 'active' : ''; ?>" data-tab="mailbox"><?php echo $translations['inbox_tab'] ?? 'Mailbox'; ?></div>
        <div class="tab <?php echo $activeTab === 'vitals' ? 'active' : ''; ?>" data-tab="vitals"><?php echo $translations['vitals_tab'] ?? 'Server Vitals'; ?></div>
    </nav>

    <div class="grid-container">

        <div class="tab-content <?php echo $activeTab === 'servers' ? 'active' : ''; ?>" id="servers">
            <header class="header">
                <div class="header__search"><?php echo $translations['breadcrumb_server_servers'] ?? 'My Development Server Servers & Applications'; ?></div>
                <div class="header__avatar"><?php echo $translations['welcome_back'] ?? 'Welcome Back!'; ?></div>
            </header>
            <div class="main-overview">
                <div class="overviewcard4">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info"><img src="assets/Server.png" style="width:64px;"></div>
                </div>

                <?php $serverInfo = serverInfo();?> <div class="overviewcard">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info">
                        <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?>
                    </div>
                </div>

                <div class="overviewcard">
                    <div class="overviewcard_icon"><?php echo $translations['web_server'] ?? 'Web Server'; ?></div>
                    <div class="overviewcard_info"><?php echo $serverInfo['webServer']; ?></div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard_icon">PHP <?php echo ($serverInfo['isFpm']) ? 'FPM' : 'SAPI'; ?></div>
                    <div class="overviewcard_info"><?php echo $serverInfo['phpSapi']; ?></div>
                </div>

                <div class="overviewcard">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info">
                        <?=$serverInfo['openSsl'];?>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard_icon">PHP</div>
                    <div class="overviewcard_info">
                        <?php echo htmlspecialchars(phpversion()); ?>
                    </div>
                </div>
            </div>
            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard_icon">MySQL</div>
                    <div class="overviewcard_info">
                        <?php
error_reporting(0);
$laraconfig = parse_ini_file('../usr/laragon.ini');

$link = mysqli_connect('localhost', 'root', $laraconfig['MySQLRootPassword']);
if (!$link) {
    $link = mysqli_connect('localhost', 'root', '');
}
if (!$link) {
    echo 'MySQL not running!';
} else {
    printf("server version: %s\n", htmlspecialchars(mysqli_get_server_info($link)));
}
?>
                    </div>
                </div>

                <div class="overviewcard">
                    <div class="overviewcard_icon"><?php echo $translations['document_root'] ?? 'Document Root'; ?></div>
                    <div class="overviewcard_info">
                        <?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?><br>
                        <small><span><?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></span></small>
                    </div>
                </div>

                <div class="overviewcard">
                    <div class="overviewcard_icon">PhpMyAdmin</div>
                    <div class="overviewcard_info">
                        <a href="http://localhost/phpmyadmin" target="_blank">
                            <?php echo $translations['manage_mysql'] ?? 'Manage MySQL'; ?>
                        </a>
                    </div>
                </div>

                <div class="overviewcard">
                    <div class="overviewcard_icon">
                        Laragon
                    </div>
                    <div class="overviewcard_info">
                        Full 6.0.220916
                    </div>
                </div>

                <!-- Until one day we can send exec commands
<div class="overviewcard_server_controls">
                    <h3>Server Controls</h3>
                    <button class="btn-custom btn-success" onclick="startServer()">Start Server</button>
                    <button class="btn-custom btn-danger" onclick="stopServer()">Stop Server</button>
                </div> -->
            </div>

            <div class="main-overview wrapper">
                <?php
$ignored = ['favicon_io'];
$folders = array_filter(glob('*'), 'is_dir');

if ($laraconfig['SSLEnabled'] == 0 || $laraconfig['Port'] == 80) {
    $url = 'http';
} else {
    $url = 'https';
}
$ignore_dirs = ['.', '..', 'logs', 'access-logs', 'vendor', 'favicon_io', 'ablepro-90', 'assets'];
foreach ($folders as $host) {
    if (in_array($host, $ignore_dirs) || !is_dir($host)) {
        continue;
    }

    $admin_link = '';
    $app_name = '';
    $avatar = '';

    switch (true) {
        case (file_exists($host . '/core') || file_exists($host . '/web/core')):
            $app_name = ' Drupal ';
            $avatar = 'assets/Drupal.svg';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/user" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Admin</a>';
            break;
        case file_exists($host . '/wp-admin'):
            $app_name = ' Wordpress ';
            $avatar = 'assets/Wordpress.png';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/wp-admin" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Admin</a>';
            break;
        case file_exists($host . '/administrator'):
            $app_name = ' Joomla ';
            $avatar = 'assets/Joomla.png';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/administrator" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Admin</a>';
            break;
        case file_exists($host . '/public/index.php') && is_dir($host . '/app') && file_exists($host . '/.env'):
            $app_name = ' Laravel ';
            $avatar = 'assets/Laravel.png';
            $admin_link = '';
            break;
        case file_exists($host . '/bin/console'):
            $app_name = ' Symfony ';
            $avatar = 'assets/Symfony.png';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/admin" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Admin</a>';
            break;
        case (file_exists($host . '/') && is_dir($host . '/app.py') && is_dir($host . '/static') && file_exists($host . '/.env')):
            $app_name = ' Python ';
            $avatar = 'assets/Python.png';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/Public" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Public Folder</a>';

            $command = 'python ' . htmlspecialchars($host) . '/app.py';
            exec($command, $output, $returnStatus);
            break;
        case file_exists($host . '/bin/cake'):
            $app_name = ' CakePHP ';
            $avatar = 'assets/CakePHP.png';
            $admin_link = '<a href="' . $url . '://' . htmlspecialchars($host) . '.local/admin" target="_blank"><small style="font-size: 8px; color: #cccccc;">' . $app_name . '</small><br>Admin</a>';
            break;
        default:
            $admin_link = '';
            $avatar = 'assets/Unknown.png';
            break;
    }

    echo '<div class="overviewcard_sites"><div class="overviewcard_avatar"><img src="' . $avatar . '" style="width:20px; height:20px;"></div><div class="overviewcard_icon"><a href="' . $url . '://' . htmlspecialchars($host) . '.local">' . htmlspecialchars($host) . '</a></div><div class="overviewcard_info">' . $admin_link . '</div></div>';
}
?>
            </div>
        </div>

        <div class="tab-content <?php echo $activeTab === 'mailbox' ? 'active' : ''; ?>" id="mailbox">
            <header class="header">
                <div class="header__search"><?php echo $translations['breadcrumb_server_mailbox'] ?? 'My Development Server Mailbox'; ?></div>
                <div class="header__avatar"><?php echo $translations['welcome_back'] ?? 'Welcome Back!'; ?></div>
            </header>
            <?php include 'assets/inbox/inbox.php';?>
        </div>

        <div class="tab-content <?php echo $activeTab === 'vitals' ? 'active' : ''; ?>" id="vitals">
            <header class="header">
                <div class="header__search"><?php echo $translations['breadcrumb_server_vitals'] ?? 'My Development Server Vitals'; ?></div>
                <div class="header__avatar"><?php echo $translations['welcome_back'] ?? 'Welcome Back!'; ?></div>
            </header>
            <div class="container mt-5" style="width: 1440px!important;background-color: #f8f9fa;padding: 20px;border-radius: 5px;color=#000000">
                <h1 style="text-align: center;color: #000000">Server's Vitals</h1>

                <div class="row">

                    <div class="col-md-6">
                        <h2><?php echo $translations['uptime'] ?? 'Uptime'; ?></h2>
                        <p id="uptime"><?php echo htmlspecialchars(shell_exec('uptime')); ?></p>
                        <canvas id="uptimeChart"></canvas>
                    </div>

                    <div class="col-md-6">
                        <h2><?php echo $translations['memory_usage'] ?? 'Memory Usage (in MB)'; ?></h2>
                        <pre id="memory-usage"><?php echo htmlspecialchars(shell_exec('free -m')); ?></pre>
                        <canvas id="memoryUsageChart"></canvas>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">
                        <h2><?php echo $translations['disk_usage'] ?? 'Disk Usage'; ?></h2>
                        <pre id="disk-usage"><?php echo htmlspecialchars(shell_exec('df -h')); ?></pre>
                        <!--				<canvas id="diskUsageChart"></canvas>-->
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
    function startServer() {
        alert('Starting server...');
        // Add your server start logic here
    }

    function stopServer() {
        alert('Stopping server...');
        // Add your server stop logic here
    }
    </script>

    <footer class="footer">
        <div class="footer__copyright">
            <?php echo $translations['default_footer'] ?? "&copy; 2024 " . htmlspecialchars(date('Y')) . ", Tarek Tarabichi"; ?>
        </div>
        <div class="footer__signature">
            <?php echo $translations['made_with_love'] ?? "Made with <span style=\"color: #e25555;\">&hearts;</span> and powered by Laragon"; ?>
        </div>
    </footer>

    <script>
    const uptimeData = [ /* Add your uptime data here */ ];
    const memoryUsageData = [ /* Add your memory usage data here */ ];
    const diskUsageData = [ /* Add your disk usage data here */ ];

    const ctxUptime = document.getElementById('uptimeChart').getContext('2d');
    const uptimeChart = new Chart(ctxUptime, {
        type: 'line',
        data: {
            labels: ['Time1', 'Time2', 'Time3'],
            datasets: [{
                label: 'Uptime',
                data: uptimeData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxMemory = document.getElementById('memoryUsageChart').getContext('2d');
    const memoryUsageChart = new Chart(ctxMemory, {
        type: 'bar',
        data: {
            labels: ['Total', 'Used', 'Free'],
            datasets: [{
                label: 'Memory Usage (MB)',
                data: memoryUsageData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxDisk = document.getElementById('diskUsageChart').getContext('2d');
    const diskUsageChart = new Chart(ctxDisk, {
        type: 'doughnut',
        data: {
            labels: ['Used', 'Available'],
            datasets: [{
                label: 'Disk Usage',
                data: diskUsageData,
                backgroundColor: [
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    </script>

</body>

</html>
