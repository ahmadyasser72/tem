<?php

if (($_GET["print"] ?? "") == "1") {
    $mpdf = new \Mpdf\Mpdf([
        "format" => "A4",
        "margin_left" => 10,
        "margin_right" => 10,
        "margin_top" => 15,
        "margin_bottom" => 15,
    ]);

    // ambil data dari database
    $sql = "SELECT kode_pangkat, nama_pangkat, golongan, keterangan
            FROM pangkat
            ORDER BY golongan, nama_pangkat";
    $result = $db->query($sql);

    // buat HTML untuk tabel
    $html = '
        <h2 style="text-align:center;">Laporan Pangkat</h2>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead style="background-color:#0066CC; color:white;">
                <tr>
                    <th style="width:15%;">Kode</th>
                    <th style="width:25%;">Nama Pangkat</th>
                    <th style="width:15%;">Golongan</th>
                    <th style="width:45%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
        ';

    $fill = false;
    while ($row = $result->fetch_assoc()) {
        $bg = $fill ? "background-color:#E0EBFF;" : "";
        $html .= '<tr style="' . $bg . '">';
        $html .=
            '<td style="text-align:center;">' . $row["kode_pangkat"] . "</td>";
        $html .= "<td>" . $row["nama_pangkat"] . "</td>";
        $html .= '<td style="text-align:center;">' . $row["golongan"] . "</td>";
        $html .= "<td>" . $row["keterangan"] . "</td>";
        $html .= "</tr>";
        $fill = !$fill;
    }

    $html .= '
            </tbody>
        </table>
    ';

    // tulis HTML ke mPDF
    $mpdf->WriteHTML($html);

    // output PDF ke browser
    $mpdf->Output("laporan_pangkat.pdf", "I");
    exit();
}

$title = "Organisasi - Pangkat";

$search = false;
$keyword = trim($_GET["search"] ?? "");
if ($keyword !== "") {
    $search = true;

    $stmt = $db->prepare("
        SELECT * FROM pangkat
        WHERE kode_pangkat LIKE ?
           OR nama_pangkat LIKE ?
           OR golongan LIKE ?
        ORDER BY id_pangkat ASC
    ");
    $like = "%$keyword%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $query = "SELECT * FROM pangkat ORDER BY id_pangkat ASC";
    $rows = $db->query($query);
}
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="flex gap-2">
        <button class="btn btn-primary" onclick="create_modal.showModal()">Tambah pangkat</button>
        <a target="_blank" href="?print=1" class="btn btn-secondary">Laporan pangkat</a>
    </div>

    <label class="input max-sm:w-full">
        <iconify-icon icon="lucide:search" width="none" class="size-4"></iconify-icon>
        <input
            type="search"
            name="search"
            hx-get
            hx-trigger="input changed delay:500ms"
            hx-target="tbody"
            hx-swap="outerHTML"
            hx-select="tbody"
            value="<?= htmlspecialchars($keyword) ?>" />
    </label>
</div>

<div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mt-4">
    <table class="table table-zebra w-full">
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Pangkat</th>
                <th>Nama Pangkat</th>
                <th>Golongan</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($rows->num_rows > 0): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <th><?= $row["id_pangkat"] ?></th>
                        <td><?= htmlspecialchars($row["kode_pangkat"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_pangkat"]) ?></td>
                        <td><?= htmlspecialchars($row["golongan"]) ?></td>
                        <td class="flex gap-2">
                            <button
                                class="btn btn-sm btn-warning"
                                onclick="openEditModal(this)"
                                data-id="<?= $row["id_pangkat"] ?>"
                                data-kode="<?= htmlspecialchars(
                                                $row["kode_pangkat"],
                                            ) ?>"
                                data-nama="<?= htmlspecialchars(
                                                $row["nama_pangkat"],
                                            ) ?>"
                                data-golongan="<?= htmlspecialchars(
                                                    $row["golongan"],
                                                ) ?>"
                                data-keterangan="<?= htmlspecialchars(
                                                        $row["keterangan"],
                                                    ) ?>">Edit</button>

                            <button
                                class="btn btn-sm btn-error"
                                onclick="openDeleteModal(<?= $row["id_pangkat"] ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">
                        <?= $search
                            ? "Pangkat tidak ditemukan."
                            : "Belum ada data pangkat." ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

    </table>
</div>


<dialog id="create_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box space-y-4">

        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="text-lg font-bold">Tambah Pangkat</h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="create">

            <label class="floating-label">
                <span>Kode Pangkat</span>
                <input type="text" name="kode_pangkat" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Nama Pangkat</span>
                <input type="text" name="nama_pangkat" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Golongan</span>
                <input type="text" name="golongan" class="input input-md w-full" />
            </label>

            <label class="floating-label">
                <span>Keterangan</span>
                <textarea name="keterangan" class="textarea w-full"></textarea>
            </label>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn" onclick="create_modal.close()">Batal</button>
            </div>
        </form>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<dialog id="edit_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box space-y-4">

        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="text-lg font-bold">Edit Pangkat</h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="edit">
            <input type="hidden" name="id_pangkat" id="edit_id">

            <label class="floating-label">
                <span>Kode Pangkat</span>
                <input type="text" name="kode_pangkat" id="edit_kode" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Nama Pangkat</span>
                <input type="text" name="nama_pangkat" id="edit_nama" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Golongan</span>
                <input type="text" name="golongan" id="edit_golongan" class="input input-md w-full" />
            </label>

            <label class="floating-label">
                <span>Keterangan</span>
                <textarea name="keterangan" id="edit_keterangan" class="textarea w-full"></textarea>
            </label>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn" onclick="edit_modal.close()">Batal</button>
            </div>
        </form>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<dialog id="delete_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">

        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="text-lg font-bold">Hapus Pangkat</h3>
        <p class="py-4">Yakin ingin menghapus pangkat ini?</p>

        <form method="POST">
            <input type="hidden" name="type" value="delete">
            <input type="hidden" name="id_pangkat" id="delete_id">

            <div class="modal-action">
                <button type="submit" class="btn btn-error">Hapus</button>
                <button type="button" class="btn" onclick="delete_modal.close()">Batal</button>
            </div>
        </form>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>


<script>
    function openEditModal(btn) {
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_kode').value = btn.dataset.kode;
        document.getElementById('edit_nama').value = btn.dataset.nama;
        document.getElementById('edit_golongan').value = btn.dataset.golongan;
        document.getElementById('edit_keterangan').value = btn.dataset.keterangan;
        edit_modal.showModal();
    }

    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        delete_modal.showModal();
    }
</script>
