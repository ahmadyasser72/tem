<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$title = "Login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$username = trim($_POST["username"] ?? "");
	$password = trim($_POST["password"] ?? "");

	$validUsername = "admin";
	$validPassword = "admin";

	if ($username === $validUsername && $password === $validPassword) {
		$_SESSION["user"] = [
			"username" => $username,
		];

		if (function_exists("add_toast")) {
			add_toast("success", "Login berhasil");
		}

		header("Location: /dashboard");
		exit();
	}

	if (function_exists("add_toast")) {
		add_toast("error", "Username atau password salah");
	}

	header("Location: /login");
	exit();
}
?>

<div class="min-h-screen flex items-center justify-center bg-base-200">
	<div class="card w-full max-w-sm shadow-md bg-base-100">
		<div class="card-body">
			<div>
				<h2 class="card-title justify-center">DamkarHub DPKP Banjar</h2>
				<img src="/images/dpkp.png" alt="DPKP" class="h-64 mx-auto">
			</div>

			<form method="POST" class="relative">
				<label class="label" for="username">
					Username
				</label>
				<input
					id="username"
					name="username"
					type="text"
					class="input mb-2 w-full"
					required
					autofocus />

				<label class="label" for="password">
					Password
				</label>
				<input
					id="password"
					name="password"
					type="password"
					class="input mb-6 w-full"
					required />

				<button type="submit" class="btn btn-primary w-full">Masuk</button>
			</form>
		</div>
	</div>
</div>
