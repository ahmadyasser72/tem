<?php

$title = "Dashboard";

// ambil data dari database
// 1. Jumlah Pegawai per Unit Kerja
$unitData = $db
	->query(
		"
    SELECT u.nama_unit, COUNT(pg.id_pegawai) AS total
    FROM pegawai pg
    JOIN unit_kerja u ON pg.id_unit = u.id_unit
    GROUP BY u.id_unit
    ORDER BY total DESC
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 2. Komposisi Pegawai Berdasarkan Jabatan
$jabatanData = $db
	->query(
		"
    SELECT j.tipe_jabatan, COUNT(pg.id_pegawai) AS total
    FROM pegawai pg
    JOIN jabatan j ON pg.id_jabatan = j.id_jabatan
    GROUP BY j.tipe_jabatan
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 3. Status Pegawai
$statusData = $db
	->query(
		"
    SELECT status_pegawai, COUNT(*) AS total
    FROM pegawai
    GROUP BY status_pegawai
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 4. Jenis Kelamin Pegawai
$genderData = $db
	->query(
		"
    SELECT jenis_kelamin, COUNT(*) AS total
    FROM pegawai
    GROUP BY jenis_kelamin
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 5. Pegawai per Golongan / Pangkat
$pangkatData = $db
	->query(
		"
    SELECT p.nama_pangkat, COUNT(pg.id_pegawai) AS total
    FROM pegawai pg
    JOIN pangkat p ON pg.id_pangkat = p.id_pangkat
    GROUP BY p.id_pangkat
    ORDER BY total DESC
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 6. Pegawai Aktif vs Nonaktif
$activeData = $db
	->query(
		"
    SELECT is_active, COUNT(*) AS total
    FROM pegawai
    GROUP BY is_active
",
	)
	->fetch_all(MYSQLI_ASSOC);

// 7. Jumlah Pegawai per Jenis Unit
$unitTypeData = $db
	->query(
		"
    SELECT u.jenis_unit, COUNT(pg.id_pegawai) AS total
    FROM pegawai pg
    JOIN unit_kerja u ON pg.id_unit = u.id_unit
    GROUP BY u.jenis_unit
",
	)
	->fetch_all(MYSQLI_ASSOC);
?>

<h1 class="text-3xl font-bold mb-6 text-center">Dashboard Pegawai Pemadam Kebakaran</h1>

<div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">

    <!-- Jumlah Pegawai per Unit (Bar Chart, lebar 2 kolom) -->
    <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h2 class="text-xl font-semibold mb-2">Jumlah Pegawai per Unit Kerja</h2>
        <div class="w-full">
            <canvas id="unitChart"></canvas>
        </div>
    </div>

    <!-- Pegawai Aktif vs Nonaktif (Pie Chart) -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Pegawai Aktif/Nonaktif</h2>
        <div>
            <canvas id="activeChart"></canvas>
        </div>
    </div>

    <!-- Status Pegawai (Pie Chart) -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Status Pegawai</h2>
        <div>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Jenis Kelamin (Pie Chart) -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Jenis Kelamin</h2>
        <div>
            <canvas id="genderChart"></canvas>
        </div>
    </div>

    <!-- Komposisi Jabatan (Pie Chart) -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Komposisi Jabatan</h2>
        <div>
            <canvas id="jabatanChart"></canvas>
        </div>
    </div>

    <!-- Pegawai per Golongan / Pangkat (Bar Chart) -->
    <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h2 class="text-xl font-semibold mb-2">Pegawai per Golongan / Pangkat</h2>
        <div class="w-full">
            <canvas id="pangkatChart"></canvas>
        </div>
    </div>

    <!-- Jumlah Pegawai per Jenis Unit (Bar Chart) -->
    <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h2 class="text-xl font-semibold mb-2">Jumlah Pegawai per Jenis Unit</h2>
        <div class="w-full">
            <canvas id="unitTypeChart"></canvas>
        </div>
    </div>

</div>


<script>
    const unitLabels = <?= json_encode(array_column($unitData, "nama_unit")) ?>;
    const unitValues = <?= json_encode(array_column($unitData, "total")) ?>;

    const jabatanLabels = <?= json_encode(
    	array_column($jabatanData, "tipe_jabatan"),
    ) ?>;
    const jabatanValues = <?= json_encode(
    	array_column($jabatanData, "total"),
    ) ?>;

    const statusLabels = <?= json_encode(
    	array_column($statusData, "status_pegawai"),
    ) ?>;
    const statusValues = <?= json_encode(array_column($statusData, "total")) ?>;

    const genderLabels = <?= json_encode(
    	array_column($genderData, "jenis_kelamin"),
    ) ?>;
    const genderValues = <?= json_encode(array_column($genderData, "total")) ?>;

    const pangkatLabels = <?= json_encode(
    	array_column($pangkatData, "nama_pangkat"),
    ) ?>;
    const pangkatValues = <?= json_encode(
    	array_column($pangkatData, "total"),
    ) ?>;

    const activeLabels = <?= json_encode(
    	array_map(function ($r) {
    		return $r["is_active"] ? "Aktif" : "Nonaktif";
    	}, $activeData),
    ) ?>;
    const activeValues = <?= json_encode(array_column($activeData, "total")) ?>;

    const unitTypeLabels = <?= json_encode(
    	array_column($unitTypeData, "jenis_unit"),
    ) ?>;
    const unitTypeValues = <?= json_encode(
    	array_column($unitTypeData, "total"),
    ) ?>;

    // Chart.js config
    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        }
    };

    new Chart(document.getElementById('unitChart'), {
        type: 'bar',
        data: {
            labels: unitLabels,
            datasets: [{
                label: 'Jumlah Pegawai',
                data: unitValues,
                backgroundColor: '#3B82F6'
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('jabatanChart'), {
        type: 'pie',
        data: {
            labels: jabatanLabels,
            datasets: [{
                data: jabatanValues,
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B']
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: ['#6366F1', '#EC4899', '#F97316']
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderValues,
                backgroundColor: ['#3B82F6', '#F472B6']
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('pangkatChart'), {
        type: 'bar',
        data: {
            labels: pangkatLabels,
            datasets: [{
                label: 'Jumlah Pegawai',
                data: pangkatValues,
                backgroundColor: '#F59E0B'
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('activeChart'), {
        type: 'pie',
        data: {
            labels: activeLabels,
            datasets: [{
                data: activeValues,
                backgroundColor: ['#10B981', '#EF4444']
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('unitTypeChart'), {
        type: 'bar',
        data: {
            labels: unitTypeLabels,
            datasets: [{
                label: 'Jumlah Pegawai',
                data: unitTypeValues,
                backgroundColor: '#8B5CF6'
            }]
        },
        options: chartOptions
    });
</script>