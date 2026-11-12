<?php
require_once 'config/app.php';
require_roles(['admin']);
$pageTitle = 'Manajemen Pengguna';
$me = current_user();

// Simpan akun baru atau perubahan akun.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = strtolower(trim($_POST['role'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($aksi === 'simpan') {
        if ($nama === '' || $username === '' || !in_array($role, ['admin','operator','pimpinan'], true)) {
            flash('Nama, username, dan peran wajib diisi dengan benar.', 'danger');
            redirect('pengguna.php' . ($id ? '?edit='.$id : ''));
        }
        try {
            if ($id > 0) {
                $q = $pdo->prepare('SELECT id, role FROM users WHERE id=?');
                $q->execute([$id]);
                $target = $q->fetch();
                if (!$target) { flash('Akun tidak ditemukan.', 'danger'); redirect('pengguna.php'); }
                if ($id === (int)$me['id'] && $role !== 'admin') {
                    flash('Peran akun yang sedang Anda gunakan tidak dapat diubah dari halaman ini.', 'warning');
                    redirect('pengguna.php?edit='.$id);
                }
                if ($target['role'] === 'admin' && $role !== 'admin') {
                    $totalAdmin = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
                    if ($totalAdmin <= 1) { flash('Minimal harus ada satu akun Admin.', 'danger'); redirect('pengguna.php?edit='.$id); }
                }
                if ($password !== '' && strlen($password) < 8) {
                    flash('Password baru minimal 8 karakter.', 'danger'); redirect('pengguna.php?edit='.$id);
                }
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $q = $pdo->prepare('UPDATE users SET nama=?, username=?, role=?, password=? WHERE id=?');
                    $q->execute([$nama,$username,$role,$hash,$id]);
                } else {
                    $q = $pdo->prepare('UPDATE users SET nama=?, username=?, role=? WHERE id=?');
                    $q->execute([$nama,$username,$role,$id]);
                }
                if ($id === (int)$me['id']) {
                    $_SESSION['user']['nama'] = $nama;
                    $_SESSION['user']['username'] = $username;
                }
                flash('Data akun berhasil diperbarui.');
            } else {
                if (strlen($password) < 8) { flash('Password akun baru minimal 8 karakter.', 'danger'); redirect('pengguna.php'); }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $q = $pdo->prepare('INSERT INTO users(nama,username,password,role) VALUES(?,?,?,?)');
                $q->execute([$nama,$username,$hash,$role]);
                flash('Akun pengguna berhasil ditambahkan.');
            }
        } catch (PDOException $e) {
            flash($e->getCode() === '23000' ? 'Username sudah digunakan. Gunakan username lain.' : 'Gagal menyimpan akun. Periksa konfigurasi database.', 'danger');
        }
        redirect('pengguna.php');
    }

    if ($aksi === 'hapus') {
        if ($id === (int)$me['id']) { flash('Akun yang sedang digunakan tidak dapat dihapus.', 'warning'); redirect('pengguna.php'); }
        $q = $pdo->prepare('SELECT role FROM users WHERE id=?');
        $q->execute([$id]);
        $target = $q->fetch();
        if (!$target) { flash('Akun tidak ditemukan.', 'danger'); redirect('pengguna.php'); }
        if ($target['role'] === 'admin') {
            $totalAdmin = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
            if ($totalAdmin <= 1) { flash('Akun Admin terakhir tidak boleh dihapus.', 'danger'); redirect('pengguna.php'); }
        }
        // Transaksi yang dibuat akun tetap dipertahankan; user_id pada peminjaman dibuat NULL.
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE peminjaman SET user_id=NULL WHERE user_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
            $pdo->commit();
            flash('Akun pengguna berhasil dihapus. Riwayat peminjaman tetap tersimpan.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('Akun gagal dihapus. Periksa keterkaitan data.', 'danger');
        }
        redirect('pengguna.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $q = $pdo->prepare('SELECT id,nama,username,role FROM users WHERE id=?');
    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch();
}
$rows = $pdo->query('SELECT id,nama,username,role,created_at FROM users ORDER BY FIELD(role,"admin","pimpinan","operator"), nama')->fetchAll();
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div><h3 class="mb-1">Manajemen Pengguna</h3><div class="text-muted">Kelola akun Admin, Operator, dan Pimpinan</div></div>
  <span class="badge bg-primary fs-6"><?=count($rows)?> akun</span>
</div>
<div class="card mb-4">
 <div class="card-header bg-white fw-bold"><?=$edit ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna'?></div>
 <div class="card-body">
  <form method="post" class="row g-3">
   <input type="hidden" name="csrf" value="<?=csrf_token()?>">
   <input type="hidden" name="aksi" value="simpan">
   <input type="hidden" name="id" value="<?=e($edit['id'] ?? '')?>">
   <div class="col-md-4"><label class="form-label">Nama Lengkap</label><input class="form-control" name="nama" required maxlength="100" value="<?=e($edit['nama'] ?? '')?>"></div>
   <div class="col-md-3"><label class="form-label">Username</label><input class="form-control" name="username" required maxlength="50" value="<?=e($edit['username'] ?? '')?>"></div>
   <div class="col-md-2"><label class="form-label">Peran / Hak Akses</label><select class="form-select" name="role" required><?php foreach(['admin'=>'Admin','operator'=>'Operator','pimpinan'=>'Pimpinan'] as $v=>$label):?><option value="<?=$v?>" <?=($edit['role'] ?? 'operator')===$v?'selected':''?>><?=$label?></option><?php endforeach;?></select></div>
   <div class="col-md-3"><label class="form-label">Password <?=$edit?'(kosongkan jika tidak diubah)':'(minimal 8 karakter)'?></label><input class="form-control" type="password" name="password" <?=$edit?'':'required'?> minlength="8" autocomplete="new-password"></div>
   <div class="col-12"><button class="btn btn-primary">Simpan Akun</button> <?php if($edit):?><a href="pengguna.php" class="btn btn-light">Batal</a><?php endif;?></div>
  </form>
 </div>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
 <thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
 <?php foreach($rows as $r):?><tr>
  <td><?=e($r['nama'])?> <?=$r['id']==$me['id']?'<span class="badge bg-secondary">Akun Anda</span>':''?></td>
  <td><?=e($r['username'])?></td>
  <td><?php $colors=['admin'=>'danger','operator'=>'primary','pimpinan'=>'success'];?><span class="badge bg-<?=$colors[$r['role']] ?? 'secondary'?>"><?=ucfirst(e($r['role']))?></span></td>
  <td><?=e(date('d-m-Y',strtotime($r['created_at'])))?></td>
  <td><a class="btn btn-sm btn-outline-primary" href="?edit=<?=$r['id']?>">Edit</a>
   <?php if($r['id']!=$me['id']):?><form method="post" class="d-inline" onsubmit="return confirm('Hapus akun <?=e($r['username'])?>? Riwayat peminjaman tetap dipertahankan.')"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form><?php endif;?>
  </td>
 </tr><?php endforeach;?>
 <?php if(!$rows):?><tr><td colspan="5" class="text-center p-4">Belum ada akun pengguna.</td></tr><?php endif;?>
 </tbody></table></div></div>
<div class="alert alert-info mt-3 mb-0"><strong>Catatan hak akses:</strong> Admin mengelola seluruh sistem dan akun; Operator mengelola kendaraan, pegawai, pengajuan, serta pengembalian; Pimpinan memproses persetujuan dan melihat laporan. Minimal satu akun Admin harus selalu tersedia.</div>
<?php require 'includes/footer.php'; ?>
