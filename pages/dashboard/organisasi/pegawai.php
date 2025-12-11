<?php

if (($_GET["print"] ?? "") == "1") {
	$mpdf = new \Mpdf\Mpdf([
		"format" => "A4",
		"margin_left" => 10,
		"margin_right" => 10,
		"margin_top" => 15,
		"margin_bottom" => 15,
		"orientation" => "landscape",
	]);

	// ambil data pegawai + unit + jabatan + pangkat
	$sql = "
        SELECT
            pg.nip,
            pg.nama_lengkap,
            pg.jenis_kelamin,
            pg.status_perkawinan,
            pg.tanggal_masuk,
            pg.status_pegawai,
            u.nama_unit AS unit_kerja,
            j.nama_jabatan AS jabatan,
            p.nama_pangkat AS pangkat,
            pg.telepon,
            pg.email
        FROM pegawai pg
        LEFT JOIN unit_kerja u ON pg.id_unit = u.id_unit
        LEFT JOIN jabatan j ON pg.id_jabatan = j.id_jabatan
        LEFT JOIN pangkat p ON pg.id_pangkat = p.id_pangkat
        ORDER BY u.nama_unit, j.level_jabatan, pg.nama_lengkap
    ";
	$result = $db->query($sql);

	// buat HTML tabel
	$html = '
        <h2 style="text-align:center;">Laporan Pegawai</h2>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead style="background-color:#0066CC; color:white;">
                <tr>
                    <th style="width:10%;">NIP</th>
                    <th style="width:15%;">Nama</th>
                    <th style="width:5%;">JK</th>
                    <th style="width:12%;">Status Kawin</th>
                    <th style="width:12%;">Unit Kerja</th>
                    <th style="width:12%;">Jabatan</th>
                    <th style="width:10%;">Pangkat</th>
                    <th style="width:10%;">Status Pegawai</th>
                    <th style="width:14%;">Kontak</th>
                </tr>
            </thead>
            <tbody>
        ';

	$fill = false;
	while ($row = $result->fetch_assoc()) {
		$bg = $fill ? "background-color:#E0EBFF;" : "";
		$kontak = trim($row["telepon"]);
		if ($row["email"]) {
			$kontak .= "<br>" . $row["email"];
		}

		$html .= '<tr style="' . $bg . '">';
		$html .= '<td style="text-align:center;">' . $row["nip"] . "</td>";
		$html .= "<td>" . $row["nama_lengkap"] . "</td>";
		$html .=
			'<td style="text-align:center;">' . $row["jenis_kelamin"] . "</td>";
		$html .=
			'<td style="text-align:center;">' .
			$row["status_perkawinan"] .
			"</td>";
		$html .= "<td>" . $row["unit_kerja"] . "</td>";
		$html .= "<td>" . $row["jabatan"] . "</td>";
		$html .= '<td style="text-align:center;">' . $row["pangkat"] . "</td>";
		$html .=
			'<td style="text-align:center;">' .
			$row["status_pegawai"] .
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
	$mpdf->Output("laporan_pegawai.pdf", "I");
	exit();
}

$title = "Organisasi - Pegawai";

$search = false;
$keyword = trim($_GET["search"] ?? "");
if ($keyword !== "") {
	$search = true;
	$stmt = $db->prepare("
        SELECT p.*, pg.nama_pangkat, j.nama_jabatan, u.nama_unit
        FROM pegawai p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id_pangkat
        LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
        LEFT JOIN unit_kerja u ON p.id_unit = u.id_unit
        WHERE p.nip LIKE ? OR p.nama_lengkap LIKE ? OR u.nama_unit LIKE ?
        ORDER BY p.id_pegawai ASC
    ");
	$like = "%$keyword%";
	$stmt->bind_param("sss", $like, $like, $like);
	$stmt->execute();
	$rows = $stmt->get_result();
} else {
	$rows = $db->query("
        SELECT p.*, pg.nama_pangkat, j.nama_jabatan, u.nama_unit
        FROM pegawai p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id_pangkat
        LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
        LEFT JOIN unit_kerja u ON p.id_unit = u.id_unit
        ORDER BY p.id_pegawai ASC
    ");
}
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="flex gap-2">
        <button
            hx-get="/fragments/form/pegawai"
            hx-target="body"
            hx-swap="beforeend"
            class="btn btn-primary"
            >Tambah pegawai</button>
        <a target="_blank" href="?print=1" class="btn btn-secondary">Laporan pegawai</a>
    </div>

    <label class="input max-sm:w-full">
        <iconify-icon icon="lucide:search" width="none" class="size-4"></iconify-icon>
        <input type="search" name="search"
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
                <th></th>
                <th>#</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Pangkat</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows > 0): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (
                            	$row["foto_profil"] &&
                            	file_exists(
                            		"uploads/pegawai/" . $row["foto_profil"],
                            	)
                            ): ?>
                                <img src="uploads/pegawai/<?= $row[
                                	"foto_profil"
                                ] ?>"
                                    alt="foto"
                                    class="size-12 border-box object-cover">
                            <?php endif; ?>
                        </td>

                        <th><?= $row["id_pegawai"] ?></th>
                        <td><?= htmlspecialchars($row["nip"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_lengkap"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_pangkat"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_unit"]) ?></td>
                        <td><?= htmlspecialchars($row["status_pegawai"]) ?></td>
                        <td class="flex gap-2">
                            <button
                                hx-get="/fragments/form/pegawai/<?= $row[
                                	"id_pegawai"
                                ] ?>"
                                hx-target="body"
                                hx-swap="beforeend"
                                class="btn btn-sm btn-warning"
                                >Edit</button>

                            <button class="btn btn-sm btn-error" onclick="openDeleteModal(<?= $row[
                            	"id_pegawai"
                            ] ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center"><?= $search
                    	? "Pegawai tidak ditemukan."
                    	: "Belum ada data pegawai." ?></td>
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
        <h3 class="text-lg font-bold">Hapus Pegawai</h3>
        <p class="py-4">Yakin ingin menghapus pegawai ini?</p>
        <form method="POST">
            <input type="hidden" name="type" value="delete">
            <input type="hidden" name="id_pegawai" id="delete_id">
            <div class="modal-action">
                <button type="submit" class="btn btn-error">Hapus</button>
                <button type="button" class="btn" onclick="delete_modal.close()">Batal</button>
            </div>
        </form>
    </div>
</dialog>


<script>
    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        delete_modal.showModal();
    }
</script>
