<?php
require_once 'config/app.php'; require_login();
$pageTitle='Dashboard';
$counts=[];
$counts['kendaraan']=$pdo->query("SELECT COUNT(*) FROM kendaraan")->fetchColumn();
$counts['tersedia']=$pdo->query("SELECT COUNT(*) FROM kendaraan WHERE status='Tersedia'")->fetchColumn();
$counts['pegawai']=$pdo->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
$counts['menunggu']=$pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='Menunggu Persetujuan'")->fetchColumn();
$counts['digunakan']=$pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='Sedang Digunakan'")->fetchColumn();
$recent=$pdo->query("SELECT p.*,k.nomor_polisi,k.merk,g.nama FROM peminjaman p JOIN kendaraan k ON k.id=p.kendaraan_id JOIN pegawai g ON g.id=p.pegawai_id ORDER BY p.id DESC LIMIT 8")->fetchAll();
require 'includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><h3 class="mb-1">Dashboard</h3><div class="text-muted">Ringkasan layanan kendaraan dinas</div></div><?php if(in_array(strtolower(current_user()['role']), ['admin','operator'], true)): ?><a class="btn btn-primary" href="peminjaman.php?action=add">+ Pengajuan Baru</a><?php endif; ?></div>
<div class="row g-3 mb-4">
<?php foreach([['Total Kendaraan',$counts['kendaraan'],'🚘'],['Kendaraan Tersedia',$counts['tersedia'],'✓'],['Data Pegawai',$counts['pegawai'],'👥'],['Menunggu Persetujuan',$counts['menunggu'],'⌛'],['Sedang Digunakan',$counts['digunakan'],'↗']] as $c): ?>
<div class="col-sm-6 col-xl"><div class="stat-card"><div class="stat-icon"><?=$c[2]?></div><div class="text-muted small"><?=$c[0]?></div><div class="fs-3 fw-bold"><?=$c[1]?></div></div></div><?php endforeach; ?>
</div>
<div class="card"><div class="card-header bg-white fw-bold">Pengajuan Terbaru</div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>No. Pengajuan</th><th>Peminjam</th><th>Kendaraan</th><th>Tanggal Pinjam</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><?=e($r['nomor_peminjaman'])?></td><td><?=e($r['nama'])?></td><td><?=e($r['merk'].' / '.$r['nomor_polisi'])?></td><td><?=e(date('d-m-Y H:i',strtotime($r['tanggal_pinjam'])))?></td><td><?=status_badge($r['status'])?></td><td><a class="btn btn-sm btn-outline-primary" href="surat.php?id=<?=$r['id']?>">Surat</a></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="6" class="text-center text-muted p-4">Belum ada pengajuan peminjaman.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require 'includes/footer.php'; ?>