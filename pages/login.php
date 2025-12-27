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
			<h2 class="card-title justify-center mb-4">Login</h2>
			<form method="POST" class="space-y-4">
				<div class="form-control">
					<label class="label" for="username">
						<span class="label-text">Username</span>
					</label>
					<input
						id="username"
						name="username"
						type="text"
						class="input input-bordered"
						required
						autofocus />
				</div>

				<div class="form-control">
					<label class="label" for="password">
						<span class="label-text">Password</span>
					</label>
					<input
						id="password"
						name="password"
						type="password"
						class="input input-bordered"
						required />
				</div>

				<div class="form-control mt-4">
					<button type="submit" class="btn btn-primary w-full">Masuk</button>
				</div>
			</form>
		</div>
	</div>
</div>
