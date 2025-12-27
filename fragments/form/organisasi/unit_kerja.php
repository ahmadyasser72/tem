<?php

$pegawai_result = $db->query(
	"SELECT id_pegawai, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC",
);
$unit_result = $db->query(
	"SELECT id_unit, nama_unit FROM unit_kerja ORDER BY nama_unit ASC",
);

$editing = false;
$row = [
	"id_unit" => "",
	"kode_unit" => "",
	"nama_unit" => "",
	"jenis_unit" => "",
	"induk_unit" => "",
	"alamat_unit" => "",
	"telepon_unit" => "",
	"email_unit" => "",
	"kepala_unit" => "",
];

if (!empty($id)) {
	$editing = true;
	$stmt = $db->prepare("SELECT * FROM unit_kerja WHERE id_unit = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
}

$jenis_options = [
	"Kantor Pusat",
	"Distrik",
	"Posko",
	"Regu",
	"Seksi",
	"Bidang",
];
?>

<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold"><?= $editing
    	? "Edit Unit Kerja"
    	: "Tambah Unit Kerja" ?></h3>

    <form hx-boost="true"
			hx-target="main"
			hx-select="main"
			hx-swap="outerHTML transition:true scroll:top" hx-select-oob="#toast" method="POST" class="space-y-4">

      <input type="hidden" name="type" value="<?= $editing
      	? "edit"
      	: "create" ?>">
      <?php if ($editing): ?>
        <input type="hidden" name="id_unit" value="<?= h($row["id_unit"]) ?>">
      <?php endif; ?>

      <label class="floating-label">
        <span>Kode Unit</span>
        <input type="text" name="kode_unit" class="input input-md w-full" required
          value="<?= h($row["kode_unit"]) ?>">
      </label>

      <label class="floating-label">
        <span>Nama Unit</span>
        <input type="text" name="nama_unit" class="input input-md w-full" required
          value="<?= h($row["nama_unit"]) ?>">
      </label>

      <label class="floating-label">
        <span>Jenis Unit</span>
        <select name="jenis_unit" class="select w-full">
          <?php foreach ($jenis_options as $opt): ?>
            <option value="<?= $opt ?>" <?= sel(
	$row["jenis_unit"],
	$opt,
) ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Induk Unit</span>
        <select name="induk_unit" class="select select-md w-full">
          <option value="">- Kosong -</option>
          <?php
          $unit_result->data_seek(0);
          while ($u = $unit_result->fetch_assoc()): ?>
            <option value="<?= $u["id_unit"] ?>"
              <?= sel($row["induk_unit"], $u["id_unit"]) ?>>
              <?= h($u["nama_unit"]) ?>
            </option>
          <?php endwhile;
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Kepala Unit</span>
        <select name="kepala_unit" class="select select-md w-full">
          <option value="">- Kosong -</option>
          <?php
          $pegawai_result->data_seek(0);
          while ($p = $pegawai_result->fetch_assoc()): ?>
            <option value="<?= $p["id_pegawai"] ?>"
              <?= sel($row["kepala_unit"], $p["id_pegawai"]) ?>>
              <?= h($p["nama_lengkap"]) ?>
            </option>
          <?php endwhile;
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Alamat Unit</span>
        <textarea name="alamat_unit" class="textarea w-full"><?= h(
        	$row["alamat_unit"],
        ) ?></textarea>
      </label>

      <label class="floating-label">
        <span>Telepon Unit</span>
        <input type="text" name="telepon_unit" class="input input-md w-full"
          value="<?= h($row["telepon_unit"]) ?>">
      </label>

      <label class="floating-label">
        <span>Email Unit</span>
        <input type="email" name="email_unit" class="input input-md w-full"
          value="<?= h($row["email_unit"]) ?>">
      </label>

      <div class="modal-action">
        <button class="btn btn-primary">Simpan</button>
        <button formmethod="dialog" class="btn">Batal</button>
      </div>

    </form>
  </div>

  <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
