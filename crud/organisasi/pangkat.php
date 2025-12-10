<?php

if (isset($_POST["type"])) {
	$type = $_POST["type"];

	if ($type === "create") {
		$kode = $_POST["kode_pangkat"];
		$nama = $_POST["nama_pangkat"];
		$golongan = $_POST["golongan"];
		$keterangan = $_POST["keterangan"];

		$stmt = $db->prepare(
			"INSERT INTO pangkat (kode_pangkat, nama_pangkat, golongan, keterangan) VALUES (?, ?, ?, ?)",
		);
		$stmt->bind_param("ssss", $kode, $nama, $golongan, $keterangan);
		$stmt->execute();
		$stmt->close();

		header("Location: pangkat.php");
		exit();
	}

	if ($type === "edit") {
		$id = $_POST["id_pangkat"];
		$kode = $_POST["kode_pangkat"];
		$nama = $_POST["nama_pangkat"];
		$golongan = $_POST["golongan"];
		$keterangan = $_POST["keterangan"];

		$stmt = $db->prepare(
			"UPDATE pangkat SET kode_pangkat = ?, nama_pangkat = ?, golongan = ?, keterangan = ? WHERE id_pangkat = ?",
		);
		$stmt->bind_param("ssssi", $kode, $nama, $golongan, $keterangan, $id);
		$stmt->execute();
		$stmt->close();

		header("Location: pangkat.php");
		exit();
	}

	if ($type === "delete") {
		$id = $_POST["id_pangkat"];

		$stmt = $db->prepare("DELETE FROM pangkat WHERE id_pangkat = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();

		header("Location: pangkat.php");
		exit();
	}
}
