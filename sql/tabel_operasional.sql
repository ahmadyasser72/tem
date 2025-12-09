-- Tabel Operasional: Laporan Kejadian (Kebakaran / Penyelamatan)
CREATE TABLE laporan_kejadian (
    id_laporan INT PRIMARY KEY AUTO_INCREMENT,
    nomor_laporan VARCHAR(20) NOT NULL UNIQUE,
    tanggal_kejadian DATETIME NOT NULL,
    jenis_kejadian ENUM('Kebakaran', 'Penyelamatan', 'Lainnya') NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Indeks untuk pencarian cepat
CREATE INDEX idx_laporan_tanggal ON laporan_kejadian(tanggal_kejadian);
CREATE INDEX idx_laporan_jenis ON laporan_kejadian(jenis_kejadian);
CREATE INDEX idx_laporan_lokasi ON laporan_kejadian(lokasi);

-- Tabel Operasional: Detail Kejadian (Lokasi, Korban, Kerugian)
CREATE TABLE detail_kejadian (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_laporan INT NOT NULL,
    alamat_detail VARCHAR(255),
    jumlah_korban INT DEFAULT 0,
    jumlah_meninggal INT DEFAULT 0,
    jumlah_luka INT DEFAULT 0,
    kerugian_rp BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan_kejadian(id_laporan) ON DELETE CASCADE
);

-- Indeks untuk pencarian cepat
CREATE INDEX idx_detail_laporan ON detail_kejadian(id_laporan);

-- Tabel Operasional: Tim Respon (Pegawai yang ditugaskan)
CREATE TABLE tim_respon (
    id_tim INT PRIMARY KEY AUTO_INCREMENT,
    id_laporan INT NOT NULL,
    id_pegawai INT NOT NULL,
    peran ENUM('Komandan','Anggota','Medis','Logistik') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan_kejadian(id_laporan) ON DELETE CASCADE,
    FOREIGN KEY (id_pegawai) REFERENCES pegawai(id_pegawai)
);

-- Indeks untuk pencarian cepat
CREATE INDEX idx_tim_laporan ON tim_respon(id_laporan);
CREATE INDEX idx_tim_pegawai ON tim_respon(id_pegawai);
