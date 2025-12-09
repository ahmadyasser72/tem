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
    $sql = "SELECT kode_jabatan, nama_jabatan, tipe_jabatan, level_jabatan, uraian_tugas
        FROM jabatan
        ORDER BY level_jabatan, nama_jabatan";
    $result = $db->query($sql);

    // buat HTML untuk tabel
    $html = '
        <h2 style="text-align:center;">Laporan Jabatan</h2>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead style="background-color:#0066CC; color:white;">
                <tr>
                    <th style="width:10%;">Kode</th>
                    <th style="width:25%;">Nama Jabatan</th>
                    <th style="width:15%;">Tipe</th>
                    <th style="width:10%;">Level</th>
                    <th style="width:40%;">Uraian Tugas</th>
                </tr>
            </thead>
            <tbody>
        ';

    $fill = false;
    while ($row = $result->fetch_assoc()) {
        $bg = $fill ? "background-color:#E0EBFF;" : "";
        $html .= '<tr style="' . $bg . '">';
        $html .=
            '<td style="text-align:center;">' . $row["kode_jabatan"] . "</td>";
        $html .= "<td>" . $row["nama_jabatan"] . "</td>";
        $html .=
            '<td style="text-align:center;">' . $row["tipe_jabatan"] . "</td>";
        $html .=
            '<td style="text-align:center;">' . $row["level_jabatan"] . "</td>";
        $html .= "<td>" . $row["uraian_tugas"] . "</td>";
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
    $mpdf->Output("laporan_jabatan.pdf", "I");
    exit();
}

$title = "Organisasi - Jabatan";

$search = false;
$keyword = trim($_GET["search"] ?? "");

if ($keyword !== "") {
    $search = true;

    $stmt = $db->prepare("
        SELECT * FROM jabatan
        WHERE kode_jabatan LIKE ?
           OR nama_jabatan LIKE ?
           OR tipe_jabatan LIKE ?
        ORDER BY id_jabatan ASC
    ");

    $like = "%$keyword%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $rows = $db->query("SELECT * FROM jabatan ORDER BY id_jabatan ASC");
}
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="flex gap-2">
        <button class="btn btn-primary" onclick="create_modal.showModal()">Tambah jabatan</button>
        <a target="_blank" href="?print=1" class="btn btn-secondary">Laporan jabatan</a>
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
                <th>Kode Jabatan</th>
                <th>Nama Jabatan</th>
                <th>Tipe</th>
                <th>Level</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($rows->num_rows > 0): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <th><?= $row["id_jabatan"] ?></th>
                        <td><?= htmlspecialchars($row["kode_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["tipe_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["level_jabatan"]) ?></td>

                        <td class="flex gap-2">
                            <button
                                class="btn btn-sm btn-warning"
                                onclick="openEditModal(this)"
                                data-id="<?= $row["id_jabatan"] ?>"
                                data-kode="<?= htmlspecialchars(
                                                $row["kode_jabatan"],
                                            ) ?>"
                                data-nama="<?= htmlspecialchars(
                                                $row["nama_jabatan"],
                                            ) ?>"
                                data-tipe="<?= htmlspecialchars(
                                                $row["tipe_jabatan"],
                                            ) ?>"
                                data-level="<?= htmlspecialchars(
                                                $row["level_jabatan"],
                                            ) ?>"
                                data-uraian="<?= htmlspecialchars(
                                                    $row["uraian_tugas"],
                                                ) ?>">Edit</button>

                            <button
                                class="btn btn-sm btn-error"
                                onclick="openDeleteModal(<?= $row["id_jabatan"] ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">
                        <?= $search
                            ? "Jabatan tidak ditemukan."
                            : "Belum ada data jabatan." ?>
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

        <h3 class="text-lg font-bold">Tambah Jabatan</h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="create">

            <label class="floating-label">
                <span>Kode Jabatan</span>
                <input type="text" name="kode_jabatan" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Nama Jabatan</span>
                <input type="text" name="nama_jabatan" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Tipe Jabatan</span>
                <select name="tipe_jabatan" class="select w-full">
                    <option value="Struktural">Struktural</option>
                    <option value="Fungsional">Fungsional</option>
                    <option value="Pelaksana">Pelaksana</option>
                </select>
            </label>

            <label class="floating-label">
                <span>Level Jabatan</span>
                <input type="number" name="level_jabatan" class="input input-md w-full" />
            </label>

            <label class="floating-label">
                <span>Uraian Tugas</span>
                <textarea name="uraian_tugas" class="textarea w-full"></textarea>
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

        <h3 class="text-lg font-bold">Edit Jabatan</h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="edit">
            <input type="hidden" name="id_jabatan" id="edit_id">

            <label class="floating-label">
                <span>Kode Jabatan</span>
                <input type="text" id="edit_kode" name="kode_jabatan" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Nama Jabatan</span>
                <input type="text" id="edit_nama" name="nama_jabatan" class="input input-md w-full" required />
            </label>

            <label class="floating-label">
                <span>Tipe Jabatan</span>
                <select id="edit_tipe" name="tipe_jabatan" class="select w-full">
                    <option value="Struktural">Struktural</option>
                    <option value="Fungsional">Fungsional</option>
                    <option value="Pelaksana">Pelaksana</option>
                </select>
            </label>

            <label class="floating-label">
                <span>Level Jabatan</span>
                <input type="number" id="edit_level" name="level_jabatan" class="input input-md w-full" />
            </label>

            <label class="floating-label">
                <span>Uraian Tugas</span>
                <textarea id="edit_uraian" name="uraian_tugas" class="textarea w-full"></textarea>
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

        <h3 class="text-lg font-bold">Hapus Jabatan</h3>
        <p class="py-4">Yakin ingin menghapus jabatan ini?</p>

        <form method="POST">
            <input type="hidden" name="type" value="delete">
            <input type="hidden" name="id_jabatan" id="delete_id">

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
        document.getElementById('edit_tipe').value = btn.dataset.tipe;
        document.getElementById('edit_level').value = btn.dataset.level;
        document.getElementById('edit_uraian').value = btn.dataset.uraian;
        edit_modal.showModal();
    }

    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        delete_modal.showModal();
    }
</script>
