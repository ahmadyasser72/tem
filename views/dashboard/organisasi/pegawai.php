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

$pangkat_result = $db->query(
    "SELECT id_pangkat, nama_pangkat FROM pangkat ORDER BY nama_pangkat ASC",
);
$jabatan_result = $db->query(
    "SELECT id_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC",
);
$unit_result = $db->query(
    "SELECT id_unit, nama_unit FROM unit_kerja ORDER BY nama_unit ASC",
);
?>

<div class="flex max-sm:flex-col gap-y-4 sm:justify-between">
    <div class="flex gap-2">
        <button class="btn btn-primary" onclick="create_modal.showModal()">Tambah pegawai</button>
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
                        <th><?= $row["id_pegawai"] ?></th>
                        <td><?= htmlspecialchars($row["nip"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_lengkap"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_pangkat"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_jabatan"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_unit"]) ?></td>
                        <td><?= htmlspecialchars($row["status_pegawai"]) ?></td>
                        <td class="flex gap-2">
                            <button class="btn btn-sm btn-warning" onclick="openEditModal(this)"
                                data-id="<?= $row["id_pegawai"] ?>"
                                data-nip="<?= htmlspecialchars($row["nip"]) ?>"
                                data-nama="<?= htmlspecialchars(
                                                $row["nama_lengkap"],
                                            ) ?>"
                                data-tempat="<?= htmlspecialchars(
                                                    $row["tempat_lahir"],
                                                ) ?>"
                                data-tanggal="<?= $row["tanggal_lahir"] ?>"
                                data-jk="<?= $row["jenis_kelamin"] ?>"
                                data-agama="<?= htmlspecialchars(
                                                $row["agama"],
                                            ) ?>"
                                data-status_perkawinan="<?= $row["status_perkawinan"] ?>"
                                data-alamat="<?= htmlspecialchars(
                                                    $row["alamat_rumah"],
                                                ) ?>"
                                data-telepon="<?= htmlspecialchars(
                                                    $row["telepon"],
                                                ) ?>"
                                data-email="<?= htmlspecialchars(
                                                $row["email"],
                                            ) ?>"
                                data-pangkat="<?= $row["id_pangkat"] ?>"
                                data-jabatan="<?= $row["id_jabatan"] ?>"
                                data-unit="<?= $row["id_unit"] ?>"
                                data-masuk="<?= $row["tanggal_masuk"] ?>"
                                data-status_pegawai="<?= $row["status_pegawai"] ?>"
                                data-darah="<?= $row["darah"] ?>"
                                data-keterangan="<?= htmlspecialchars(
                                                        $row["keterangan"],
                                                    ) ?>">Edit</button>

                            <button class="btn btn-sm btn-error" onclick="openDeleteModal(<?= $row["id_pegawai"] ?>)">Hapus</button>
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


<dialog id="create_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box space-y-4 sm:w-11/12 sm:max-w-4xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="text-lg font-bold">Tambah Pegawai</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="create">

            <!-- Data Pribadi -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Data Pribadi</legend>

                <label class="floating-label">
                    <span>NIP</span>
                    <input type="text" name="nip" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Nama Lengkap</span>
                    <input type="text" name="nama_lengkap" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Tempat Lahir</span>
                    <input type="text" name="tempat_lahir" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Tanggal Lahir</span>
                    <input type="date" name="tanggal_lahir" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Jenis Kelamin</span>
                    <select name="jenis_kelamin" class="select select-md w-full" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Agama</span>
                    <input type="text" name="agama" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Status Perkawinan</span>
                    <select name="status_perkawinan" class="select select-md w-full">
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Alamat</span>
                    <textarea name="alamat_rumah" class="textarea w-full"></textarea>
                </label>

                <label class="floating-label">
                    <span>Telepon</span>
                    <input type="text" name="telepon" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Email</span>
                    <input type="email" name="email" class="input input-md w-full" />
                </label>
            </fieldset>

            <!-- Data Kepegawaian -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Data Kepegawaian</legend>

                <label class="floating-label">
                    <span>Pangkat</span>
                    <select name="id_pangkat" class="select select-md w-full" required>
                        <?php
                        $pangkat_result->data_seek(0);
                        while ($p = $pangkat_result->fetch_assoc()): ?>
                            <option value="<?= $p["id_pangkat"] ?>"><?= htmlspecialchars(
                                                                        $p["nama_pangkat"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Jabatan</span>
                    <select name="id_jabatan" class="select select-md w-full" required>
                        <?php
                        $jabatan_result->data_seek(0);
                        while ($j = $jabatan_result->fetch_assoc()): ?>
                            <option value="<?= $j["id_jabatan"] ?>"><?= htmlspecialchars(
                                                                        $j["nama_jabatan"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Unit Kerja</span>
                    <select name="id_unit" class="select select-md w-full" required>
                        <?php
                        $unit_result->data_seek(0);
                        while ($u = $unit_result->fetch_assoc()): ?>
                            <option value="<?= $u["id_unit"] ?>"><?= htmlspecialchars(
                                                                        $u["nama_unit"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Tanggal Masuk</span>
                    <input type="date" name="tanggal_masuk" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Status Pegawai</span>
                    <select name="status_pegawai" class="select select-md w-full" required>
                        <option value="PNS">PNS</option>
                        <option value="Honorer">Honorer</option>
                        <option value="Kontrak">Kontrak</option>
                    </select>
                </label>
            </fieldset>

            <!-- Lain-lain -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Lain-lain</legend>

                <label class="floating-label">
                    <span>Golongan Darah</span>
                    <select name="darah" class="select select-md w-full">
                        <option value="">- Kosong -</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Keterangan</span>
                    <textarea name="keterangan" class="textarea w-full"></textarea>
                </label>
            </fieldset>

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
    <div class="modal-box space-y-4 sm:w-11/12 sm:max-w-4xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <h3 class="text-lg font-bold">Edit Pegawai</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="type" value="edit">
            <input type="hidden" name="id_pegawai" id="edit_id">

            <!-- Data Pribadi -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Data Pribadi</legend>

                <label class="floating-label">
                    <span>NIP</span>
                    <input type="text" name="nip" id="edit_nip" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Nama Lengkap</span>
                    <input type="text" name="nama_lengkap" id="edit_nama" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Tempat Lahir</span>
                    <input type="text" name="tempat_lahir" id="edit_tempat" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Tanggal Lahir</span>
                    <input type="date" name="tanggal_lahir" id="edit_tanggal" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Jenis Kelamin</span>
                    <select name="jenis_kelamin" id="edit_jk" class="select select-md w-full" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Agama</span>
                    <input type="text" name="agama" id="edit_agama" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Status Perkawinan</span>
                    <select name="status_perkawinan" id="edit_status" class="select select-md w-full">
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Alamat</span>
                    <textarea name="alamat_rumah" id="edit_alamat" class="textarea w-full"></textarea>
                </label>

                <label class="floating-label">
                    <span>Telepon</span>
                    <input type="text" name="telepon" id="edit_telepon" class="input input-md w-full" />
                </label>

                <label class="floating-label">
                    <span>Email</span>
                    <input type="email" name="email" id="edit_email" class="input input-md w-full" />
                </label>
            </fieldset>

            <!-- Data Kepegawaian -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Data Kepegawaian</legend>

                <label class="floating-label">
                    <span>Pangkat</span>
                    <select name="id_pangkat" id="edit_pangkat" class="select select-md w-full" required>
                        <?php
                        $pangkat_result->data_seek(0);
                        while ($p = $pangkat_result->fetch_assoc()): ?>
                            <option value="<?= $p["id_pangkat"] ?>"><?= htmlspecialchars(
                                                                        $p["nama_pangkat"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Jabatan</span>
                    <select name="id_jabatan" id="edit_jabatan" class="select select-md w-full" required>
                        <?php
                        $jabatan_result->data_seek(0);
                        while ($j = $jabatan_result->fetch_assoc()): ?>
                            <option value="<?= $j["id_jabatan"] ?>"><?= htmlspecialchars(
                                                                        $j["nama_jabatan"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Unit Kerja</span>
                    <select name="id_unit" id="edit_unit" class="select select-md w-full" required>
                        <?php
                        $unit_result->data_seek(0);
                        while ($u = $unit_result->fetch_assoc()): ?>
                            <option value="<?= $u["id_unit"] ?>"><?= htmlspecialchars(
                                                                        $u["nama_unit"],
                                                                    ) ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Tanggal Masuk</span>
                    <input type="date" name="tanggal_masuk" id="edit_masuk" class="input input-md w-full" required />
                </label>

                <label class="floating-label">
                    <span>Status Pegawai</span>
                    <select name="status_pegawai" id="edit_status_pegawai" class="select select-md w-full" required>
                        <option value="PNS">PNS</option>
                        <option value="Honorer">Honorer</option>
                        <option value="Kontrak">Kontrak</option>
                    </select>
                </label>
            </fieldset>

            <!-- Lain-lain -->
            <fieldset class="fieldset gap-y-4 gap-x-8 sm:grid-cols-2 bg-base-200 border-base-300 rounded-box border p-4">
                <legend class="fieldset-legend">Lain-lain</legend>

                <label class="floating-label">
                    <span>Golongan Darah</span>
                    <select name="darah" id="edit_darah" class="select select-md w-full">
                        <option value="">- Kosong -</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </label>

                <label class="floating-label">
                    <span>Keterangan</span>
                    <textarea name="keterangan" id="edit_keterangan" class="textarea w-full"></textarea>
                </label>
            </fieldset>

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
    function openEditModal(btn) {
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_nip').value = btn.dataset.nip;
        document.getElementById('edit_nama').value = btn.dataset.nama;
        document.getElementById('edit_tempat').value = btn.dataset.tempat;
        document.getElementById('edit_tanggal').value = btn.dataset.tanggal;
        document.getElementById('edit_jk').value = btn.dataset.jk;
        document.getElementById('edit_agama').value = btn.dataset.agama;
        document.getElementById('edit_status').value = btn.dataset.status_perkawinan;
        document.getElementById('edit_alamat').value = btn.dataset.alamat;
        document.getElementById('edit_telepon').value = btn.dataset.telepon;
        document.getElementById('edit_email').value = btn.dataset.email;
        document.getElementById('edit_pangkat').value = btn.dataset.pangkat;
        document.getElementById('edit_jabatan').value = btn.dataset.jabatan;
        document.getElementById('edit_unit').value = btn.dataset.unit;
        document.getElementById('edit_masuk').value = btn.dataset.masuk;
        document.getElementById('edit_status_pegawai').value = btn.dataset.status_pegawai;
        document.getElementById('edit_darah').value = btn.dataset.darah;
        document.getElementById('edit_keterangan').value = btn.dataset.keterangan;
        edit_modal.showModal();
    }

    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        delete_modal.showModal();
    }
</script>
