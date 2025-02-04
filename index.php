<?php
function nav_class( $path ) {
	if ( $_SERVER['REQUEST_URI'] === $path ) {
		return 'p-4 bg-gray-200 text-gray-600 rounded-lg transition ease-in-out duration-200 mb-2 flex items-center';
	}
	return 'p-4 hover:bg-indigo-500 text-gray-600 hover:text-white rounded-lg transition ease-in-out duration-200 mb-2 flex items-center';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="w-full h-full font-sans grid grid-cols-1 lg:grid-cols-12">
    <!-- Left Sidebar -->
    <aside class="h-full bg-gray-100 p-4 lg:col-span-2">
        <div class="p-4">
            <h1 class="text-lg font-bold">Laragon</h1>
        </div>
        <nav class="mt-8 px-4">
            <ul>
                <a href="/" class="<?php echo nav_class( '/' ); ?>">
                    <span class="mr-3">ℹ️</span>
                    Server Info
                </a>
                <a href="/inbox" class="<?php echo nav_class( '/inbox' ); ?> ">
                    <span class="mr-3">📧</span>
                    Inbox
                </a>
                <a href="/logs" class="<?php echo nav_class( '/logs' ); ?> ">
                    <span class="mr-3">🏴󠁡󠁦󠁬󠁯󠁧󠁿</span>
                    Logs
                </a>
            </ul>
        </nav>
    </aside>
    <!-- Main Content -->
    <main class="bg-white lg:col-span-10 min-h-screen">
        <?php
			switch ( $_SERVER['REQUEST_URI'] ) {
				case '/logs':
					include __DIR__ . '/logs.php';
					break;
				case '/inbox':
					include __DIR__ . '/inbox.php';
					break;
				default:
					phpinfo();
					break;
			}
			?>
    </main>
</body>

</html>