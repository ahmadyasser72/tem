<?php

if (isset($_POST["type"])) {
	$type = $_POST["type"];

	if ($type === "create") {
		$nip = $_POST["nip"];
		$nama = $_POST["nama_lengkap"];
		$tempat = $_POST["tempat_lahir"];
		$tanggal = $_POST["tanggal_lahir"];
		$jk = $_POST["jenis_kelamin"];
		$agama = $_POST["agama"];
		$status = $_POST["status_perkawinan"];
		$alamat = $_POST["alamat_rumah"];
		$telepon = $_POST["telepon"];
		$email = $_POST["email"];
		$pangkat = $_POST["id_pangkat"];
		$jabatan = $_POST["id_jabatan"];
		$unit = $_POST["id_unit"];
		$masuk = $_POST["tanggal_masuk"];
		$status_pegawai = $_POST["status_pegawai"];
		$darah = $_POST["darah"];
		$keterangan = $_POST["keterangan"];

		// -----------------------
		// HANDLE FOTO PROFIL
		// -----------------------
		$filename = null;

		if (!empty($_FILES["foto_profil"]["name"])) {
			$ext = pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION);
			$filename = "pegawai_" . time() . "_" . rand(100, 999) . "." . $ext;

			$folder = "uploads/pegawai/";

			if (!is_dir($folder)) {
				mkdir($folder, 0777, true);
			}

			move_uploaded_file(
				$_FILES["foto_profil"]["tmp_name"],
				$folder . $filename,
			);
		}

		$stmt = $db->prepare("
        INSERT INTO pegawai
        (nip, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, status_perkawinan,
        alamat_rumah, telepon, email, id_pangkat, id_jabatan, id_unit, tanggal_masuk,
        status_pegawai, darah, keterangan, foto_profil)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

		$stmt->bind_param(
			"ssssssssssiiisssss",
			$nip,
			$nama,
			$tempat,
			$tanggal,
			$jk,
			$agama,
			$status,
			$alamat,
			$telepon,
			$email,
			$pangkat,
			$jabatan,
			$unit,
			$masuk,
			$status_pegawai,
			$darah,
			$keterangan,
			$filename,
		);

		$stmt->execute();
		$stmt->close();

		header("Location: pegawai.php");
		exit();
	}

	if ($type === "edit") {
		$id = $_POST["id_pegawai"];
		$nip = $_POST["nip"];
		$nama = $_POST["nama_lengkap"];
		$tempat = $_POST["tempat_lahir"];
		$tanggal = $_POST["tanggal_lahir"];
		$jk = $_POST["jenis_kelamin"];
		$agama = $_POST["agama"];
		$status = $_POST["status_perkawinan"];
		$alamat = $_POST["alamat_rumah"];
		$telepon = $_POST["telepon"];
		$email = $_POST["email"];
		$pangkat = $_POST["id_pangkat"];
		$jabatan = $_POST["id_jabatan"];
		$unit = $_POST["id_unit"];
		$masuk = $_POST["tanggal_masuk"];
		$status_pegawai = $_POST["status_pegawai"];
		$darah = $_POST["darah"];
		$keterangan = $_POST["keterangan"];

		// Ambil foto lama
		$result = $db->query(
			"SELECT foto_profil FROM pegawai WHERE id_pegawai = '$id'",
		);
		$old = $result->fetch_assoc();
		$foto_lama = $old["foto_profil"];

		// -----------------------
		// HANDLE FOTO PROFIL
		// -----------------------
		$filename = $foto_lama;

		if (!empty($_FILES["foto_profil"]["name"])) {
			// hapus foto lama
			if ($foto_lama && file_exists("uploads/pegawai/" . $foto_lama)) {
				unlink("uploads/pegawai/" . $foto_lama);
			}

			$ext = pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION);
			$filename = "pegawai_" . time() . "_" . rand(100, 999) . "." . $ext;

			$folder = "uploads/pegawai/";
			move_uploaded_file(
				$_FILES["foto_profil"]["tmp_name"],
				$folder . $filename,
			);
		}

		$stmt = $db->prepare("
        UPDATE pegawai SET
        nip=?, nama_lengkap=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, agama=?, status_perkawinan=?,
        alamat_rumah=?, telepon=?, email=?, id_pangkat=?, id_jabatan=?, id_unit=?,
        tanggal_masuk=?, status_pegawai=?, darah=?, keterangan=?, foto_profil=?
        WHERE id_pegawai=?
    ");

		$stmt->bind_param(
			"ssssssssssiiisssssi",
			$nip,
			$nama,
			$tempat,
			$tanggal,
			$jk,
			$agama,
			$status,
			$alamat,
			$telepon,
			$email,
			$pangkat,
			$jabatan,
			$unit,
			$masuk,
			$status_pegawai,
			$darah,
			$keterangan,
			$filename,
			$id,
		);

		$stmt->execute();
		$stmt->close();

		header("Location: pegawai.php");
		exit();
	}

	if ($type === "delete") {
		$id = $_POST["id_pegawai"];

		// ambil nama foto
		$result = $db->query(
			"SELECT foto_profil FROM pegawai WHERE id_pegawai = '$id'",
		);
		$row = $result->fetch_assoc();

		if (
			$row["foto_profil"] &&
			file_exists("uploads/pegawai/" . $row["foto_profil"])
		) {
			unlink("uploads/pegawai/" . $row["foto_profil"]);
		}

		$stmt = $db->prepare("DELETE FROM pegawai WHERE id_pegawai = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();

		header("Location: pegawai.php");
		exit();
	}
}
