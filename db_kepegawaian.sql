-- Buat database
CREATE DATABASE IF NOT EXISTS db_kepegawaian;
USE db_kepegawaian;

-- Buat tabel pegawai
CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(20) NOT NULL,
    jabatan VARCHAR(50) NOT NULL,
    gaji DECIMAL(10, 2) NOT NULL,
    tanggal_masuk DATE NOT NULL
);

-- Buat tabel log_hapus_pegawai untuk trigger
CREATE TABLE IF NOT EXISTS log_hapus_pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT,
    nama VARCHAR(100),
    tanggal_hapus TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat stored procedure untuk menambahkan data pegawai
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS tambah_pegawai(
    IN p_nama VARCHAR(100),
    IN p_nip VARCHAR(20),
    IN p_jabatan VARCHAR(50),
    IN p_gaji DECIMAL(10, 2),
    IN p_tanggal_masuk DATE
)
BEGIN
    INSERT INTO pegawai (nama, nip, jabatan, gaji, tanggal_masuk)
    VALUES (p_nama, p_nip, p_jabatan, p_gaji, p_tanggal_masuk);
END //

DELIMITER ;

-- Buat trigger untuk mencatat log saat data pegawai dihapus
DELIMITER //

CREATE TRIGGER IF NOT EXISTS after_delete_pegawai
AFTER DELETE ON pegawai
FOR EACH ROW
BEGIN
    INSERT INTO log_hapus_pegawai (id_pegawai, nama)
    VALUES (OLD.id, OLD.nama);
END //

DELIMITER ;


-- Tampilkan struktur tabel dan data
SELECT * FROM pegawai;
SELECT * FROM log_hapus_pegawai;