<?php

$db = new mysqli("localhost", "root", "", "pekaeel");
if ($db->connect_error) {
	die("Koneksi gagal: " . $db->connect_error);
}
