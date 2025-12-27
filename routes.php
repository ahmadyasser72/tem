<?php

require_once __DIR__ . "/router.php";

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

any("/login", "pages/login.php");

$publicPaths = ["/login"];
$currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: "/";

if (!in_array($currentPath, $publicPaths, true) && empty($_SESSION["user"])) {
	header("Location: /login");
	exit();
}

get("/dashboard", "pages/dashboard/index.php");

$tables = ["pangkat", "jabatan", "unit_kerja", "pegawai"];
foreach ($tables as $table) {
	get(
		"/dashboard/organisasi/$table",
		"pages/dashboard/organisasi/$table.php",
	);

	get("/fragments/form/$table", "fragments/form/organisasi/$table.php");
	get(
		"/fragments/form/$table" . '/$id',
		"fragments/form/organisasi/$table.php",
	);

	post("/dashboard/organisasi/$table", "crud/organisasi/$table.php");
}

any("/logout", function () {
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$_SESSION = [];
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(
			session_name(),
			"",
			time() - 42000,
			$params["path"],
			$params["domain"],
			$params["secure"],
			$params["httponly"],
		);
	}
	session_destroy();
	header("Location: /login");
	exit();
});

get("/fragments/chart/jabatan", "fragments/jabatan_chart.php");
get("/fragments/chart/unit", "fragments/unit_chart.php");

any("/404", "pages/404.php");

// // ##################################################
// // ##################################################
// // ##################################################

// // Static GET
// // In the URL -> http://localhost
// // The output -> Index
// get('/', 'views/index.php');

// // Dynamic GET. Example with 1 variable
// // The $id will be available in user.php
// get('/user/$id', 'views/user');

// // Dynamic GET. Example with 2 variables
// // The $name will be available in full_name.php
// // The $last_name will be available in full_name.php
// // In the browser point to: localhost/user/X/Y
// get('/user/$name/$last_name', 'views/full_name.php');

// // Dynamic GET. Example with 2 variables with static
// // In the URL -> http://localhost/product/shoes/color/blue
// // The $type will be available in product.php
// // The $color will be available in product.php
// get('/product/$type/color/$color', 'product.php');

// // A route with a callback
// get('/callback', function(){
//   echo 'Callback executed';
// });

// // A route with a callback passing a variable
// // To run this route, in the browser type:
// // http://localhost/user/A
// get('/callback/$name', function($name){
//   echo "Callback executed. The name is $name";
// });

// // Route where the query string happends right after a forward slash
// get('/product', '');

// // A route with a callback passing 2 variables
// // To run this route, in the browser type:
// // http://localhost/callback/A/B
// get('/callback/$name/$last_name', function($name, $last_name){
//   echo "Callback executed. The full name is $name $last_name";
// });

// // ##################################################
// // ##################################################
// // ##################################################
// // Route that will use POST data
// post('/user', '/api/save_user');

// ##################################################
// ##################################################
// ##################################################
// any can be used for GETs or POSTs

// For GET or POST
// The 404.php which is inside the views folder will be called
// The 404.php has access to $_GET and $_POST
// any('/404', 'views/404.php');
