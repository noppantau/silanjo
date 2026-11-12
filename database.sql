CREATE DATABASE IF NOT EXISTS db_peminjaman_kendaraan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_peminjaman_kendaraan;
CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,nama VARCHAR(100) NOT NULL,username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,role ENUM('admin','operator','pimpinan') DEFAULT 'operator',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS kendaraan (
 id INT AUTO_INCREMENT PRIMARY KEY,kode_kendaraan VARCHAR(30) NOT NULL UNIQUE,nomor_polisi VARCHAR(20) NOT NULL UNIQUE,
 merk VARCHAR(100) NOT NULL,tipe VARCHAR(100),tahun INT,warna VARCHAR(30),nomor_rangka VARCHAR(100),nomor_mesin VARCHAR(100),
 jenis ENUM('Mobil','Motor','Lainnya') DEFAULT 'Mobil',kapasitas INT,
 status ENUM('Tersedia','Dipinjam','Perawatan','Tidak Aktif') DEFAULT 'Tersedia',keterangan TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pegawai (
 id INT AUTO_INCREMENT PRIMARY KEY,nip VARCHAR(30) UNIQUE,nama VARCHAR(150) NOT NULL,jabatan VARCHAR(100),
 bidang VARCHAR(100),no_hp VARCHAR(20),alamat TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS peminjaman (
 id INT AUTO_INCREMENT PRIMARY KEY,nomor_peminjaman VARCHAR(50) NOT NULL UNIQUE,pegawai_id INT NOT NULL,kendaraan_id INT NOT NULL,
 tanggal_pengajuan DATE NOT NULL,tanggal_pinjam DATETIME NOT NULL,tanggal_kembali_rencana DATETIME NOT NULL,
 tujuan VARCHAR(255) NOT NULL,lokasi_tujuan VARCHAR(255),keperluan TEXT,pengemudi VARCHAR(150),
 status ENUM('Menunggu Persetujuan','Disetujui','Ditolak','Sedang Digunakan','Selesai','Dibatalkan') DEFAULT 'Menunggu Persetujuan',
 disetujui_oleh VARCHAR(150),catatan TEXT,user_id INT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_peminjaman_pegawai FOREIGN KEY(pegawai_id) REFERENCES pegawai(id),
 CONSTRAINT fk_peminjaman_kendaraan FOREIGN KEY(kendaraan_id) REFERENCES kendaraan(id),
 CONSTRAINT fk_peminjaman_user FOREIGN KEY(user_id) REFERENCES users(id),
 INDEX idx_tanggal_pinjam(tanggal_pinjam),INDEX idx_status(status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pengembalian (
 id INT AUTO_INCREMENT PRIMARY KEY,peminjaman_id INT NOT NULL UNIQUE,tanggal_kembali DATETIME NOT NULL,
 kilometer_awal INT,kilometer_akhir INT,kondisi_kendaraan ENUM('Baik','Rusak Ringan','Rusak Berat') DEFAULT 'Baik',
 jumlah_bbm VARCHAR(50),catatan TEXT,petugas_penerima VARCHAR(150),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_pengembalian_peminjaman FOREIGN KEY(peminjaman_id) REFERENCES peminjaman(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS riwayat_status (
 id INT AUTO_INCREMENT PRIMARY KEY,peminjaman_id INT NOT NULL,status_sebelumnya VARCHAR(50),status_baru VARCHAR(50) NOT NULL,
 keterangan TEXT,diubah_oleh VARCHAR(150),tanggal_perubahan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_riwayat_peminjaman FOREIGN KEY(peminjaman_id) REFERENCES peminjaman(id)
) ENGINE=InnoDB;
-- Password default admin adalah: password
INSERT INTO users(nama,username,password,role) VALUES
('Administrator Silanjo','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.','admin')
ON DUPLICATE KEY UPDATE username=username;
