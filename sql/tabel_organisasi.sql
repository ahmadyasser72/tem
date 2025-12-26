CREATE TABLE IF NOT EXISTS pangkat (
    id_pangkat INT PRIMARY KEY AUTO_INCREMENT,
    kode_pangkat VARCHAR(10) NOT NULL UNIQUE,
    nama_pangkat VARCHAR(50) NOT NULL,
    golongan VARCHAR(5),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jabatan (
    id_jabatan INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    kode_jabatan VARCHAR(10) NOT NULL UNIQUE,
    nama_jabatan VARCHAR(50) NOT NULL,
    tipe_jabatan ENUM('Struktural', 'Fungsional', 'Pelaksana') NOT NULL,
    level_jabatan INT DEFAULT 0,
    uraian_tugas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS unit_kerja (
    id_unit INT PRIMARY KEY AUTO_INCREMENT,
    kode_unit VARCHAR(10) NOT NULL UNIQUE,
    nama_unit VARCHAR(50) NOT NULL,
    jenis_unit ENUM('Kantor Pusat', 'Distrik', 'Posko', 'Regu', 'Seksi', 'Bidang') NOT NULL,
    induk_unit INT NULL,
    alamat_unit TEXT,
    telepon_unit VARCHAR(20),
    email_unit VARCHAR(100),
    kepala_unit INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (induk_unit) REFERENCES unit_kerja(id_unit) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS pegawai (
    id_pegawai INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(30) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    agama VARCHAR(20),
    status_perkawinan ENUM('Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'),
    alamat_rumah TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    id_pangkat INT NOT NULL,
    id_jabatan INT NOT NULL,
    id_unit INT NOT NULL,
    tanggal_masuk DATE NOT NULL,
    status_pegawai ENUM('PNS', 'Honorer', 'Kontrak') NOT NULL,
    foto_profil VARCHAR(255),
    darah ENUM('A', 'B', 'AB', 'O'),
    keterangan TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pangkat) REFERENCES pangkat(id_pangkat),
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan),
    FOREIGN KEY (id_unit) REFERENCES unit_kerja(id_unit)
);

ALTER TABLE jabatan
ADD CONSTRAINT fk_jabatan_parent
FOREIGN KEY (parent_id) REFERENCES jabatan (id_jabatan) ON DELETE SET NULL;

ALTER TABLE unit_kerja
ADD CONSTRAINT fk_kepala_unit
FOREIGN KEY (kepala_unit) REFERENCES pegawai(id_pegawai) ON DELETE SET NULL;

CREATE INDEX idx_pegawai_nama ON pegawai(nama_lengkap);
CREATE INDEX idx_pegawai_nip ON pegawai(nip);
CREATE INDEX idx_pegawai_status ON pegawai(status_pegawai, is_active);
CREATE INDEX idx_pegawai_unit ON pegawai(id_unit);
CREATE INDEX idx_unit_jenis ON unit_kerja(jenis_unit);
CREATE INDEX idx_jabatan_tipe ON jabatan(tipe_jabatan);
CREATE INDEX idx_pangkat_golongan ON pangkat(golongan);
