<?php

if (isset($_POST["type"])) {
	$type = $_POST["type"];

	// helper hitung level
	function hitungLevel($db, $parent_id)
	{
		if (empty($parent_id)) {
			return 1;
		}

		$stmt = $db->prepare(
			"SELECT level_jabatan FROM jabatan WHERE id_jabatan = ?",
		);
		$stmt->bind_param("i", $parent_id);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		if (!$res) {
			die("Parent jabatan tidak valid");
		}

		return $res["level_jabatan"] + 1;
	}

	if ($type === "create") {
		$kode = $_POST["kode_jabatan"];
		$nama = $_POST["nama_jabatan"];
		$tipe = $_POST["tipe_jabatan"];
		$parent = !empty($_POST["parent_id"])
			? (int) $_POST["parent_id"]
			: null;
		$uraian = $_POST["uraian_tugas"];

		$level = hitungLevel($db, $parent);

		$stmt = $db->prepare(
			"INSERT INTO jabatan
			 (kode_jabatan, nama_jabatan, tipe_jabatan, parent_id, level_jabatan, uraian_tugas)
			 VALUES (?, ?, ?, ?, ?, ?)",
		);

		$stmt->bind_param(
			"sssiss",
			$kode,
			$nama,
			$tipe,
			$parent,
			$level,
			$uraian,
		);

		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}

	if ($type === "edit") {
		$id = (int) $_POST["id_jabatan"];
		$kode = $_POST["kode_jabatan"];
		$nama = $_POST["nama_jabatan"];
		$tipe = $_POST["tipe_jabatan"];
		$parent = !empty($_POST["parent_id"])
			? (int) $_POST["parent_id"]
			: null;
		$uraian = $_POST["uraian_tugas"];

		// cegah self-parent
		if ($parent === $id) {
			die("Jabatan tidak boleh menjadi parent dirinya sendiri");
		}

		$level = hitungLevel($db, $parent);

		$stmt = $db->prepare(
			"UPDATE jabatan SET
				kode_jabatan = ?,
				nama_jabatan = ?,
				tipe_jabatan = ?,
				parent_id = ?,
				level_jabatan = ?,
				uraian_tugas = ?
			 WHERE id_jabatan = ?",
		);

		$stmt->bind_param(
			"sssissi",
			$kode,
			$nama,
			$tipe,
			$parent,
			$level,
			$uraian,
			$id,
		);

		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}

	if ($type === "delete") {
		$id = (int) $_POST["id_jabatan"];

		$stmt = $db->prepare("DELETE FROM jabatan WHERE id_jabatan = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();

		header("Location: jabatan.php");
		exit();
	}
}
