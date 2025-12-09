<?php

if (isset($_POST["type"])) {
  $type = $_POST["type"];

  if ($type === "create") {
    $kode = $_POST["kode_unit"];
    $nama = $_POST["nama_unit"];
    $jenis = $_POST["jenis_unit"];
    $induk = $_POST["induk_unit"] ?: null;
    $alamat = $_POST["alamat_unit"];
    $telepon = $_POST["telepon_unit"];
    $email = $_POST["email_unit"];
    $kepala = $_POST["kepala_unit"] ?: null;

    $stmt = $db->prepare("
            INSERT INTO unit_kerja
            (kode_unit, nama_unit, jenis_unit, induk_unit, alamat_unit, telepon_unit, email_unit, kepala_unit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
    $stmt->bind_param(
      "ssissssi",
      $kode,
      $nama,
      $jenis,
      $induk,
      $alamat,
      $telepon,
      $email,
      $kepala,
    );
    $stmt->execute();
    $stmt->close();

    header("Location: unit_kerja.php");
    exit();
  }

  if ($type === "edit") {
    $id = $_POST["id_unit"];
    $kode = $_POST["kode_unit"];
    $nama = $_POST["nama_unit"];
    $jenis = $_POST["jenis_unit"];
    $induk = $_POST["induk_unit"] ?: null;
    $alamat = $_POST["alamat_unit"];
    $telepon = $_POST["telepon_unit"];
    $email = $_POST["email_unit"];
    $kepala = $_POST["kepala_unit"] ?: null;

    $stmt = $db->prepare("
            UPDATE unit_kerja SET
                kode_unit = ?, nama_unit = ?, jenis_unit = ?, induk_unit = ?,
                alamat_unit = ?, telepon_unit = ?, email_unit = ?, kepala_unit = ?
            WHERE id_unit = ?
        ");
    $stmt->bind_param(
      "ssisssssi",
      $kode,
      $nama,
      $jenis,
      $induk,
      $alamat,
      $telepon,
      $email,
      $kepala,
      $id,
    );
    $stmt->execute();
    $stmt->close();

    header("Location: unit_kerja.php");
    exit();
  }

  if ($type === "delete") {
    $id = $_POST["id_unit"];
    $stmt = $db->prepare("DELETE FROM unit_kerja WHERE id_unit = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: unit_kerja.php");
    exit();
  }
}
