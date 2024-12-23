<?php
function nav_class( $path ) {
	if ( $_SERVER['REQUEST_URI'] === $path ) {
		return 'p-4 bg-indigo-500 text-gray-600 text-white rounded-lg transition ease-in-out duration-200 mb-2 flex items-center';
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

<body class=" font-sans">
	<div class="flex h-screen">
		<!-- Sidebar -->
		<aside class="w-64 bg-slate-100">
			<div class="p-4">
				<h1 class="text-lg font-bold">Laragon</h1>
			</div>
			<nav class="mt-8 px-4">
				<ul>
					<a href="/"
						class="<?php echo nav_class( '/' ); ?>">
						<span class="mr-3">📁</span>
						Server
					</a>
					<a href="/inbox"
						class="<?php echo nav_class( '/inbox' ); ?> ">
						<span class="mr-3">🔧</span>
						Inbox
					</a>
				</ul>
			</nav>
		</aside>

		<!-- Main Content -->
		<main class="flex-1">
			<?php
			switch ( $_SERVER['REQUEST_URI'] ) {
				case '/':
					include __DIR__ . '/server.php';
					break;
				case '/inbox':
					include __DIR__ . '/inbox.php';
					break;
				default:
					echo $_SERVER['QUERY_STRING'];
					break;
			}
			?>
		</main>
	</div>
</body>

</html>
