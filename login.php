<?php
require_once 'config/app.php';
if(current_user()) redirect('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check(); $q=$pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1"); $q->execute([trim($_POST['username']??'')]); $u=$q->fetch();
 if($u && password_verify($_POST['password']??'', $u['password'])) { session_regenerate_id(true); $_SESSION['user']=['id'=>$u['id'],'nama'=>$u['nama'],'role'=>$u['role']]; redirect('index.php'); }
 $error='Username atau password tidak sesuai.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Silanjo</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head><body class="login-bg"><div class="login-card"><div class="text-center mb-4"><div class="display-5">🚘</div><h3 class="fw-bold">SILANJO</h3><div class="text-muted">Sistem Layanan Peminjaman Oto</div><small><?=e(APP_INSTANSI)?></small></div><?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" required autofocus></div><div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div><button class="btn btn-primary w-100">Masuk</button></form><div class="text-center text-muted small mt-3">Gunakan username dan password akun yang telah diberikan Administrator.</div></div></body></html>