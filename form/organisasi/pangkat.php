<?php

$editing = false;
$row = [
	"id_pangkat" => "",
	"kode_pangkat" => "",
	"nama_pangkat" => "",
	"golongan" => "",
	"keterangan" => "",
];

if (!empty($id)) {
	$editing = true;
	$stmt = $db->prepare("SELECT * FROM pangkat WHERE id_pangkat = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
}
?>

<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold">
      <?= $editing ? "Edit Pangkat" : "Tambah Pangkat" ?>
    </h3>

    <form method="POST" class="space-y-4">

      <input type="hidden" name="type" value="<?= $editing
      	? "edit"
      	: "create" ?>">

      <?php if ($editing): ?>
        <input type="hidden" name="id_pangkat" value="<?= h(
        	$row["id_pangkat"],
        ) ?>">
      <?php endif; ?>

      <label class="floating-label">
        <span>Kode Pangkat</span>
        <input
          type="text"
          name="kode_pangkat"
          class="input input-md w-full"
          value="<?= h($row["kode_pangkat"]) ?>"
          required>
      </label>

      <label class="floating-label">
        <span>Nama Pangkat</span>
        <input
          type="text"
          name="nama_pangkat"
          class="input input-md w-full"
          value="<?= h($row["nama_pangkat"]) ?>"
          required>
      </label>

      <label class="floating-label">
        <span>Golongan</span>
        <input
          type="text"
          name="golongan"
          class="input input-md w-full"
          value="<?= h($row["golongan"]) ?>">
      </label>

      <label class="floating-label">
        <span>Keterangan</span>
        <textarea
          name="keterangan"
          class="textarea w-full"><?= h($row["keterangan"]) ?></textarea>
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
