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

	// ambil data pegawai + unit + jabatan + pangkat, dengan filter jika ada keyword
	$keyword = trim($_GET["search"] ?? "");
	if ($keyword !== "") {
		$like = "%$keyword%";
		$stmt = $db->prepare("
        SELECT
            pg.foto_profil,
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
        WHERE pg.nip LIKE ? OR pg.nama_lengkap LIKE ? OR u.nama_unit LIKE ?
        ORDER BY pg.id_pegawai DESC
    ");
		$stmt->bind_param("sss", $like, $like, $like);
		$stmt->execute();
		$result = $stmt->get_result();
	} else {
		$sql = "
        SELECT
            pg.foto_profil,
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
        ORDER BY pg.id_pegawai DESC
    ";
		$result = $db->query($sql);
	}

	// buat HTML tabel
	$html = '
        <h2 style="text-align:center;">Laporan Pegawai</h2>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead style="background-color:#0066CC; color:white;">
                <tr>
                    <th>Foto</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>Status Kawin</th>
                    <th>Unit Kerja</th>
                    <th>Jabatan</th>
                    <th>Pangkat</th>
                    <th>Status Pegawai</th>
                    <th>Kontak</th>
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
		$html .=
			'<td> <img src="uploads/pegawai/' .
			($row["foto_profil"] &&
			file_exists("uploads/pegawai/" . $row["foto_profil"])
				? $row["foto_profil"]
				: "placeholder.png") .
			'" style="width:64px"> </td>';
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

	$mpdf->debug = true;
	// output PDF ke browser
	$mpdf->Output("laporan_pegawai.pdf", "I");
	exit();
}

$title = "Organisasi - Pegawai";

$search = false;
$keyword = trim($_GET["search"] ?? "");
$page = max(1, (int) ($_GET["page"] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;
$totalRows = 0;

if ($keyword !== "") {
	$search = true;
	$like = "%$keyword%";

	$countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM pegawai p
        LEFT JOIN unit_kerja u ON p.id_unit = u.id_unit
        WHERE p.nip LIKE ? OR p.nama_lengkap LIKE ? OR u.nama_unit LIKE ?
    ");
	$countStmt->bind_param("sss", $like, $like, $like);
	$countStmt->execute();
	$countResult = $countStmt->get_result();
	$totalRows = (int) ($countResult->fetch_assoc()["total"] ?? 0);
	$countStmt->close();

	$stmt = $db->prepare("
        SELECT p.*, pg.nama_pangkat, j.nama_jabatan, u.nama_unit
        FROM pegawai p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id_pangkat
        LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
        LEFT JOIN unit_kerja u ON p.id_unit = u.id_unit
        WHERE p.nip LIKE ? OR p.nama_lengkap LIKE ? OR u.nama_unit LIKE ?
        ORDER BY p.id_pegawai DESC
        LIMIT ? OFFSET ?
    ");
	$stmt->bind_param("sssii", $like, $like, $like, $perPage, $offset);
	$stmt->execute();
	$rows = $stmt->get_result();
} else {
	$countResult = $db->query("SELECT COUNT(*) AS total FROM pegawai");
	if ($countResult) {
		$totalRows = (int) ($countResult->fetch_assoc()["total"] ?? 0);
	}

	$rows = $db->query("
        SELECT p.*, pg.nama_pangkat, j.nama_jabatan, u.nama_unit
        FROM pegawai p
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id_pangkat
        LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
        LEFT JOIN unit_kerja u ON p.id_unit = u.id_unit
        ORDER BY p.id_pegawai DESC
        LIMIT $perPage OFFSET $offset
    ");
}

$totalPages = max(1, (int) ceil($totalRows / $perPage));
if ($page > $totalPages) {
	$page = $totalPages;
}
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="join max-sm:join-vertical">
        <button
            hx-get="/fragments/form/pegawai"
            hx-target="body"
            hx-swap="beforeend"
            class="join-item btn btn-primary"
            >Tambah pegawai</button>

        <a
			id="pegawai-print-button"
			target="_blank"
			href="?print=1<?= $keyword !== '' ? '&search=' . urlencode($keyword) : '' ?>"
			class="join-item btn btn-secondary"
		>
			Laporan pegawai
		</a>
    </div>

	<?php render_search_input('pegawai-table-wrapper', $keyword, 'pegawai-print-button'); ?>
</div>

<div id="pegawai-table-wrapper" class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mt-4">
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
                <th>Status Pegawai</th>
                <th>Aktif</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $idx = 1 + $offset; ?>
			<?php if ($rows && $rows->num_rows > 0): ?>
				<?php while ($row = $rows->fetch_assoc()): ?>
					<tr <?= view_transition_attrs('pegawai-row', $row["id_pegawai"]) ?>>
                        <td>
                            <div class="size-24">
                                <img src="/uploads/pegawai/<?= $row[
                                	"foto_profil"
                                ] &&
                                file_exists(
                                	"uploads/pegawai/" . $row["foto_profil"],
                                )
                                	? $row["foto_profil"]
                                	: "placeholder.png" ?>"
                                alt="foto"
                                class="size-full aspect-square border-box object-cover shadow-sm">
                            </div>
                        </td>

                        <th><?= $idx++ ?></th>
                        <td><?= htmlspecialchars($row["nip"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_lengkap"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_pangkat"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_unit"]) ?></td>
                        <td><?= htmlspecialchars($row["status_pegawai"]) ?></td>
                        <td>
                            <form
							method="POST"
							hx-post="/dashboard/organisasi/pegawai"
							hx-target="#pegawai-row-<?= (int) $row["id_pegawai"] ?>"
							hx-select="#pegawai-row-<?= (int) $row["id_pegawai"] ?>"
							hx-swap="outerHTML transition:true"
							hx-select-oob="#toast"
						>
                                <input type="hidden" name="type" value="toggle_active">
                                <input type="hidden" name="id_pegawai" value="<?= (int) $row["id_pegawai"] ?>">
                                <input type="hidden" name="is_active" value="<?= $row["is_active"] ? 0 : 1 ?>">
                                <button type="submit" class="btn btn-sm <?= $row["is_active"] ? 'btn-success' : 'btn-ghost' ?>">
                                    <?= $row["is_active"] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="flex gap-2">
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
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center"><?= $search
	                    ? "Pegawai tidak ditemukan."
	                    : "Belum ada data pegawai." ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
	$extraParams = [];
	if ($keyword !== "") {
		$extraParams["search"] = $keyword;
	}
	render_pagination_join("pegawai-table-wrapper", $page, $totalPages, "", $extraParams);
    ?>
</div>


<dialog id="delete_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="text-lg font-bold">Hapus Pegawai</h3>
        <p class="py-4">Yakin ingin menghapus pegawai ini?</p>
        <form hx-boost="true"
			hx-target="main"
			hx-select="main"
			hx-swap="outerHTML" hx-select-oob="#toast" method="POST">
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
