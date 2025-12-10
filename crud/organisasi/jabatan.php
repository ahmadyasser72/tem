<?php

if (isset($_POST["type"])) {
	$type = $_POST["type"];

	if ($type === "create") {
		$kode = $_POST["kode_jabatan"];
		$nama = $_POST["nama_jabatan"];
		$tipe = $_POST["tipe_jabatan"];
		$level = $_POST["level_jabatan"];
		$uraian = $_POST["uraian_tugas"];

		$stmt = $db->prepare(
			"INSERT INTO jabatan (kode_jabatan, nama_jabatan, tipe_jabatan, level_jabatan, uraian_tugas) VALUES (?, ?, ?, ?, ?)",
		);
		$stmt->bind_param("sssds", $kode, $nama, $tipe, $level, $uraian);
		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}

	if ($type === "edit") {
		$id = $_POST["id_jabatan"];
		$kode = $_POST["kode_jabatan"];
		$nama = $_POST["nama_jabatan"];
		$tipe = $_POST["tipe_jabatan"];
		$level = $_POST["level_jabatan"];
		$uraian = $_POST["uraian_tugas"];

		$stmt = $db->prepare(
			"UPDATE jabatan SET kode_jabatan = ?, nama_jabatan = ?, tipe_jabatan = ?, level_jabatan = ?, uraian_tugas = ? WHERE id_jabatan = ?",
		);
		$stmt->bind_param("sssdsi", $kode, $nama, $tipe, $level, $uraian, $id);
		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}

	if ($type === "delete") {
		$id = $_POST["id_jabatan"];

		$stmt = $db->prepare("DELETE FROM jabatan WHERE id_jabatan = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}
}
