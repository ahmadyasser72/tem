<?php

$pangkat_result = $db->query(
	"SELECT id_pangkat, nama_pangkat FROM pangkat ORDER BY nama_pangkat ASC",
);
$jabatan_result = $db->query(
	"SELECT id_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC",
);
$unit_result = $db->query(
	"SELECT id_unit, nama_unit FROM unit_kerja ORDER BY nama_unit ASC",
);

$editing = false;
$row = [
	"id_pegawai" => "",
	"nip" => "",
	"nama_lengkap" => "",
	"tempat_lahir" => "",
	"tanggal_lahir" => "",
	"jenis_kelamin" => "",
	"agama" => "",
	"status_perkawinan" => "",
	"alamat_rumah" => "",
	"telepon" => "",
	"email" => "",
	"id_pangkat" => "",
	"id_jabatan" => "",
	"id_unit" => "",
	"tanggal_masuk" => "",
	"status_pegawai" => "",
	"foto_profil" => "",
	"darah" => "",
	"keterangan" => "",
];

if (!empty($id)) {
	$editing = true;
	$stmt = $db->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
}

$jk_opt = ["L" => "Laki-laki", "P" => "Perempuan"];
$sp_opt = ["Belum Kawin", "Kawin", "Cerai Hidup", "Cerai Mati"];
$stat_opt = ["PNS", "Honorer", "Kontrak"];
$darah_opt = ["A", "B", "AB", "O"];
?>

<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold"><?= $editing
    	? "Edit Pegawai"
    	: "Tambah Pegawai" ?></h3>

    <form hx-boost="true"
			hx-target="main"
			hx-select="main"
			hx-swap="outerHTML transition:true scroll:top"
      hx-select-oob="#toast" method="POST" class="space-y-4" enctype="multipart/form-data">

      <input type="hidden" name="type" value="<?= $editing
      	? "edit"
      	: "create" ?>">
      <?php if ($editing): ?>
        <input type="hidden" name="id_pegawai" value="<?= h(
        	$row["id_pegawai"],
        ) ?>">
      <?php endif; ?>

      <label class="floating-label">
        <span>NIP</span>
        <input type="text" name="nip" class="input input-md w-full" value="<?= h(
        	$row["nip"],
        ) ?>" required>
      </label>

      <label class="floating-label">
        <span>Nama Lengkap</span>
        <input type="text"
          name="nama_lengkap"
          class="input input-md w-full"
          value="<?= h($row["nama_lengkap"]) ?>"
          required>
      </label>

      <label class="floating-label">
        <span>Tempat Lahir</span>
        <input type="text"
          name="tempat_lahir"
          class="input input-md w-full"
          value="<?= h($row["tempat_lahir"]) ?>">
      </label>

      <label class="floating-label">
        <span>Tanggal Lahir</span>
        <input type="date"
          name="tanggal_lahir"
          class="input input-md w-full"
          value="<?= h($row["tanggal_lahir"]) ?>">
      </label>

      <label class="floating-label">
        <span>Jenis Kelamin</span>
        <select name="jenis_kelamin" class="select w-full">
          <?php foreach ($jk_opt as $k => $v): ?>
            <option value="<?= $k ?>" <?= sel(
	$row["jenis_kelamin"],
	$k,
) ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Agama</span>
        <input type="text"
          name="agama"
          class="input input-md w-full"
          value="<?= h($row["agama"]) ?>">
      </label>

      <label class="floating-label">
        <span>Status Perkawinan</span>
        <select name="status_perkawinan" class="select w-full">
          <?php foreach ($sp_opt as $opt): ?>
            <option value="<?= $opt ?>" <?= sel(
	$row["status_perkawinan"],
	$opt,
) ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Alamat Rumah</span>
        <textarea name="alamat_rumah" class="textarea w-full"><?= h(
        	$row["alamat_rumah"],
        ) ?></textarea>
      </label>

      <label class="floating-label">
        <span>Telepon</span>
        <input type="text"
          name="telepon"
          class="input input-md w-full"
          value="<?= h($row["telepon"]) ?>">
      </label>

      <label class="floating-label">
        <span>Email</span>
        <input type="email"
          name="email"
          class="input input-md w-full"
          value="<?= h($row["email"]) ?>">
      </label>

      <label class="floating-label">
        <span>Pangkat</span>
        <select name="id_pangkat" class="select w-full">
          <?php
          $pangkat_result->data_seek(0);
          while ($p = $pangkat_result->fetch_assoc()): ?>
            <option value="<?= $p["id_pangkat"] ?>" <?= sel(
	$row["id_pangkat"],
	$p["id_pangkat"],
) ?>>
              <?= h($p["nama_pangkat"]) ?>
            </option>
          <?php endwhile;
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Jabatan</span>
        <select name="id_jabatan" class="select w-full">
          <?php
          $jabatan_result->data_seek(0);
          while ($j = $jabatan_result->fetch_assoc()): ?>
            <option value="<?= $j["id_jabatan"] ?>" <?= sel(
	$row["id_jabatan"],
	$j["id_jabatan"],
) ?>>
              <?= h($j["nama_jabatan"]) ?>
            </option>
          <?php endwhile;
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Unit Kerja</span>
        <select name="id_unit" class="select w-full">
          <?php
          $unit_result->data_seek(0);
          while ($u = $unit_result->fetch_assoc()): ?>
            <option value="<?= $u["id_unit"] ?>" <?= sel(
	$row["id_unit"],
	$u["id_unit"],
) ?>>
              <?= h($u["nama_unit"]) ?>
            </option>
          <?php endwhile;
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Tanggal Masuk</span>
        <input type="date" name="tanggal_masuk" class="input input-md w-full" value="<?= h(
        	$row["tanggal_masuk"],
        ) ?>">
      </label>

      <label class="floating-label">
        <span>Status Pegawai</span>
        <select name="status_pegawai" class="select w-full">
          <?php foreach ($stat_opt as $opt): ?>
            <option value="<?= $opt ?>" <?= sel(
	$row["status_pegawai"],
	$opt,
) ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Golongan Darah</span>
        <select name="darah" class="select w-full">
          <option value="">-</option>
          <?php foreach ($darah_opt as $opt): ?>
            <option value="<?= $opt ?>" <?= sel(
	$row["darah"],
	$opt,
) ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Foto Profil</span>
        <input type="file" name="foto_profil" class="file-input w-full">
      </label>

      <label class="floating-label">
        <span>Keterangan</span>
        <textarea name="keterangan" class="textarea w-full"><?= h(
        	$row["keterangan"],
        ) ?></textarea>
      </label>

      <div class="modal-action">
        <button class="btn btn-primary">Simpan</button>
        <button formmethod="dialog" class="btn">Batal</button>
      </div>

    </form>
  </div>

  <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
