<?php

if (($_GET["print"] ?? "") == "1") {
	$mpdf = new \Mpdf\Mpdf([
		"format" => "A4",
		"margin_left" => 10,
		"margin_right" => 10,
		"margin_top" => 15,
		"margin_bottom" => 15,
		"orientation" => "landscape",
		"debug" => true,
	]);

	// ambil data unit kerja + kepala unit + jumlah pegawai
	$sql = "
        SELECT
            u.kode_unit,
            u.nama_unit,
            u.jenis_unit,
            IFNULL(i.nama_unit, '-') AS induk_unit,
            IFNULL(p.nama_lengkap, '-') AS kepala_unit,
            COUNT(pg.id_pegawai) AS jumlah_pegawai,
            u.alamat_unit, u.telepon_unit, u.email_unit
        FROM unit_kerja u
        LEFT JOIN unit_kerja i ON u.induk_unit = i.id_unit
        LEFT JOIN pegawai p ON u.kepala_unit = p.id_pegawai
        LEFT JOIN pegawai pg ON pg.id_unit = u.id_unit
        GROUP BY u.id_unit
        ORDER BY u.id_unit
    ";
	$result = $db->query($sql);

	// buat HTML tabel
	$html = '
        <h2 style="text-align:center;">Laporan Unit Kerja</h2>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead style="background-color:#0066CC; color:white;">
                <tr>
                    <th style="width:8%;">Kode</th>
                    <th style="width:18%;">Nama Unit</th>
                    <th style="width:12%;">Jenis</th>
                    <th style="width:12%;">Induk Unit</th>
                    <th style="width:15%;">Kepala Unit</th>
                    <th style="width:10%;">Jumlah Pegawai</th>
                    <th style="width:25%;">Alamat / Kontak</th>
                </tr>
            </thead>
            <tbody>
        ';

	$fill = false;
	while ($row = $result->fetch_assoc()) {
		$bg = $fill ? "background-color:#E0EBFF;" : "";
		$kontak = trim($row["alamat_unit"] ?? "-");
		if ($row["telepon_unit"]) {
			$kontak .= "<br>Telp: " . $row["telepon_unit"];
		}
		if ($row["email_unit"]) {
			$kontak .= "<br>Email: " . $row["email_unit"];
		}

		$html .= '<tr style="' . $bg . '">';
		$html .=
			'<td style="text-align:center;">' . $row["kode_unit"] . "</td>";
		$html .= "<td>" . $row["nama_unit"] . "</td>";
		$html .=
			'<td style="text-align:center;">' . $row["jenis_unit"] . "</td>";
		$html .= "<td>" . $row["induk_unit"] . "</td>";
		$html .= "<td>" . $row["kepala_unit"] . "</td>";
		$html .=
			'<td style="text-align:center;">' .
			$row["jumlah_pegawai"] .
			"</td>";
		$html .= "<td>" . $kontak . "</td>";
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
	$mpdf->Output("laporan_unit_kerja.pdf", "I");
	exit();
}

$title = "Organisasi - Unit Kerja";

$search = false;
$keyword = trim($_GET["search"] ?? "");
if ($keyword !== "") {
	$search = true;
	$stmt = $db->prepare("
        SELECT u.*, k.nama_lengkap AS kepala_nama, i.nama_unit AS induk_nama
        FROM unit_kerja u
        LEFT JOIN pegawai k ON u.kepala_unit = k.id_pegawai
        LEFT JOIN unit_kerja i ON u.induk_unit = i.id_unit
        WHERE u.kode_unit LIKE ? OR u.nama_unit LIKE ? OR u.jenis_unit LIKE ?
        ORDER BY u.id_unit ASC
    ");
	$like = "%$keyword%";
	$stmt->bind_param("sss", $like, $like, $like);
	$stmt->execute();
	$rows = $stmt->get_result();
} else {
	$rows = $db->query("
        SELECT u.*, k.nama_lengkap AS kepala_nama, i.nama_unit AS induk_nama
        FROM unit_kerja u
        LEFT JOIN pegawai k ON u.kepala_unit = k.id_pegawai
        LEFT JOIN unit_kerja i ON u.induk_unit = i.id_unit
        ORDER BY u.id_unit ASC
    ");
}
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="join max-sm:join-vertical">
        <button
            hx-get="/fragments/form/unit_kerja"
            hx-target="body"
            hx-swap="beforeend"
            class="join-item btn btn-primary"
            >Tambah unit kerja</button>

        <a target="_blank" href="?print=1" class="join-item btn btn-secondary">Laporan unit kerja</a>

        <button
            hx-get="/fragments/chart/unit"
            hx-target="body"
            hx-swap="beforeend"
            class="join-item btn btn-info"
            >Hirarki unit kerja</button>
    </div>

    <label class="input max-sm:w-full">
        <iconify-icon icon="lucide:search" width="none" class="size-4"></iconify-icon>
        <input
            type="search"
            name="search"
            hx-get hx-trigger="input changed delay:500ms"
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
                <th>Kode Unit</th>
                <th>Nama Unit</th>
                <th>Jenis</th>
                <th>Induk</th>
                <th>Kepala</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows > 0): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <th><?= $row["id_unit"] ?></th>
                        <td><?= htmlspecialchars($row["kode_unit"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_unit"]) ?></td>
                        <td><?= htmlspecialchars($row["jenis_unit"]) ?></td>
                        <td><?= htmlspecialchars(
                        	$row["induk_nama"] ?? "-",
                        ) ?></td>
                        <td><?= htmlspecialchars(
                        	$row["kepala_nama"] ?? "-",
                        ) ?></td>
                        <td class="flex gap-2">
                            <button
                                hx-get="/fragments/form/unit_kerja/<?= $row[
                                	"id_unit"
                                ] ?>"
                                hx-target="body"
                                hx-swap="beforeend"
                                class="btn btn-sm btn-warning"
                                >Edit</button>

                            <button class="btn btn-sm btn-error" onclick="openDeleteModal(<?= $row[
                            	"id_unit"
                            ] ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center"><?= $search
                    	? "Unit kerja tidak ditemukan."
                    	: "Belum ada data unit kerja." ?></td>
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
        <h3 class="text-lg font-bold">Hapus Unit Kerja</h3>
        <p class="py-4">Yakin ingin menghapus unit kerja ini?</p>
        <form method="POST">
            <input type="hidden" name="type" value="delete">
            <input type="hidden" name="id_unit" id="delete_id">
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
