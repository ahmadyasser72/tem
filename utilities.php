<?php

function h($str)
{
	return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

function sel($value, $target)
{
	return $value === $target ? "selected" : "";
}

function add_toast(string $type, string $message): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$_SESSION["toasts"][] = [
		"type" => $type,
		"message" => $message,
	];
}

function get_and_clear_toasts(): array
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$toasts = $_SESSION["toasts"] ?? [];
	unset($_SESSION["toasts"]);

	return $toasts;
}
