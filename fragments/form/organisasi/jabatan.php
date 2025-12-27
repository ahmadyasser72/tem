<?php

$editing = false;
$row = [
	"id_jabatan" => "",
	"kode_jabatan" => "",
	"nama_jabatan" => "",
	"tipe_jabatan" => "",
	"parent_id" => "",
	"uraian_tugas" => "",
];

if (!empty($id)) {
	$editing = true;
	$stmt = $db->prepare("SELECT * FROM jabatan WHERE id_jabatan = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
}

$parents = $db
	->query(
		"
        SELECT id_jabatan, nama_jabatan
        FROM jabatan
        ORDER BY nama_jabatan
    ",
	)
	->fetch_all(MYSQLI_ASSOC);
?>

<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold">
      <?= $editing ? "Edit Jabatan" : "Tambah Jabatan" ?>
    </h3>

    <form hx-boost="true"
			hx-target="main"
			hx-select="main"
			hx-swap="outerHTML transition:true scroll:top" hx-select-oob="#toast" method="POST" class="space-y-4">

      <input type="hidden" name="type" value="<?= $editing
      	? "edit"
      	: "create" ?>">

      <?php if ($editing): ?>
        <input type="hidden" name="id_jabatan" value="<?= h(
        	$row["id_jabatan"],
        ) ?>">
      <?php endif; ?>

      <label class="floating-label">
        <span>Kode Jabatan</span>
        <input
          type="text"
          name="kode_jabatan"
          class="input input-md w-full"
          value="<?= h($row["kode_jabatan"]) ?>"
          required>
      </label>

      <label class="floating-label">
        <span>Nama Jabatan</span>
        <input
          type="text"
          name="nama_jabatan"
          class="input input-md w-full"
          value="<?= h($row["nama_jabatan"]) ?>"
          required>
      </label>

      <label class="floating-label">
        <span>Tipe Jabatan</span>
        <select name="tipe_jabatan" class="select w-full">
          <?php
          $options = ["Struktural", "Fungsional", "Pelaksana"];
          foreach ($options as $opt) {
          	echo "<option value='$opt' " .
          		sel($row["tipe_jabatan"], $opt) .
          		">$opt</option>";
          }
          ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Parent Jabatan</span>
        <select name="parent_id" class="select w-full">
          <option value="">-- Tidak Ada (Root) --</option>

          <?php foreach ($parents as $p): ?>
            <?php if ($editing && $p["id_jabatan"] == $row["id_jabatan"]) {
            	continue;
            } ?>

            <option
              value="<?= $p["id_jabatan"] ?>"
              <?= sel($row["parent_id"], $p["id_jabatan"]) ?>>
              <?= h($p["nama_jabatan"]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="floating-label">
        <span>Uraian Tugas</span>
        <textarea
          name="uraian_tugas"
          class="textarea w-full"><?= h($row["uraian_tugas"]) ?></textarea>
      </label>

      <div class="modal-action">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button formmethod="dialog" type="submit" class="btn">Batal</button>
      </div>

    </form>
  </div>

  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
