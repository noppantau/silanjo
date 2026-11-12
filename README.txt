SILANJO - SISTEM LAYANAN PEMINJAMAN OTO
BPKD KABUPATEN PADANG PARIAMAN

INSTALASI XAMPP
1. Ekstrak folder silanjo ke C:\xampp\htdocs\silanjo
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka http://localhost/phpmyadmin
4. Pilih menu Import, pilih file database.sql, lalu klik Import.
5. Periksa config/database.php. Default XAMPP: host localhost, user root, password kosong.
6. Buka http://localhost/silanjo
7. Login awal:
   Username: admin
   Password: password
8. Segera ganti password admin. Fitur pengelolaan akun belum disediakan pada versi awal ini.

FITUR
- Dashboard ringkasan kendaraan, pegawai dan peminjaman
- CRUD data kendaraan dan pegawai
- Pengajuan peminjaman dan pemeriksaan benturan jadwal
- Persetujuan/penolakan oleh pengguna berwenang
- Pencatatan pengembalian, kilometer dan kondisi kendaraan
- Cetak surat peminjaman melalui browser (Print / Save as PDF)
- Laporan rentang tanggal dan ekspor CSV yang dapat dibuka di Microsoft Excel

CATATAN PENTING
- Versi awal ini menggunakan PHP Native + PDO + MySQL dan Bootstrap 5 CDN.
- Pastikan komputer memiliki koneksi internet untuk memuat Bootstrap dari CDN; file aplikasi dan database berjalan lokal.
- Menu persetujuan saat ini dapat diakses oleh pengguna yang sudah login. Terapkan pembatasan role lebih lanjut sebelum dipakai secara resmi.
- Status kendaraan diubah menjadi Dipinjam saat disetujui, dan menjadi Tersedia setelah pengembalian dengan kondisi Baik.
- Ekspor CSV kompatibel dengan Excel. PDF dibuat dengan fitur Cetak browser (pilih Save as PDF).
- Untuk penggunaan produksi, wajib ganti password, aktifkan HTTPS, lakukan backup database, dan lakukan pengujian alur kerja serta kebijakan persetujuan internal.


MANAJEMEN PENGGUNA (PEMBARUAN)
- Login sebagai Admin untuk membuka menu Manajemen Pengguna.
- Admin dapat menambah, mengubah username/nama/peran, mengganti password, dan menghapus akun lain.
- Password minimal 8 karakter dan disimpan menggunakan password_hash.
- Admin tidak dapat menghapus dirinya sendiri atau menghilangkan Admin terakhir.
- Operator: kendaraan, pegawai, pengajuan peminjaman, pengembalian, dan laporan.
- Pimpinan: persetujuan dan laporan.
- Akses halaman juga diperiksa di sisi server, bukan hanya menyembunyikan menu.
