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
        ORDER BY id_jabatan ASC";
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
    <div class="join max-sm:join-vertical">
        <button
            hx-get="/fragments/form/jabatan"
            hx-target="body"
            hx-swap="beforeend"
            class="join-item btn btn-primary"
            >Tambah jabatan</button>

        <a target="_blank" href="?print=1" class="join-item btn btn-secondary">Laporan jabatan</a>

        <button
            hx-get="/fragments/chart/jabatan"
            hx-target="body"
            hx-swap="beforeend"
            class="join-item btn btn-info"
            >Hirarki jabatan</button>
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
                                hx-get="/fragments/form/jabatan/<?= $row[
                                	"id_jabatan"
                                ] ?>"
                                hx-target="body"
                                hx-swap="beforeend"
                                class="btn btn-sm btn-warning"
                                >Edit</button>

                            <button
                                class="btn btn-sm btn-error"
                                onclick="openDeleteModal(<?= $row[
                                	"id_jabatan"
                                ] ?>)">Hapus</button>
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
    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        delete_modal.showModal();
    }
</script>
