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

    $stmt = $db->prepare("
            INSERT INTO pegawai
            (nip, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, status_perkawinan, alamat_rumah, telepon, email, id_pangkat, id_jabatan, id_unit, tanggal_masuk, status_pegawai, darah, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    $stmt->bind_param(
      "ssssssssssiiissss",
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

    $stmt = $db->prepare("
            UPDATE pegawai SET
            nip = ?, nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, agama = ?, status_perkawinan = ?, alamat_rumah = ?, telepon = ?, email = ?, id_pangkat = ?, id_jabatan = ?, id_unit = ?, tanggal_masuk = ?, status_pegawai = ?, darah = ?, keterangan = ?
            WHERE id_pegawai = ?
        ");
    $stmt->bind_param(
      "ssssssssssiiissssi",
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
      $id,
    );
    $stmt->execute();
    $stmt->close();

    header("Location: pegawai.php");
    exit();
  }

  if ($type === "delete") {
    $id = $_POST["id_pegawai"];
    $stmt = $db->prepare("DELETE FROM pegawai WHERE id_pegawai = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: pegawai.php");
    exit();
  }
}
