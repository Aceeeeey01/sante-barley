<?php require_once '../includes/functions.php';$err='';
if($_SERVER['REQUEST_METHOD']=='POST'&&csrf_ok()){$a=q("SELECT * FROM admins WHERE username=?",[trim($_POST['u'])])->fetch();
 if($a&&password_verify($_POST['p'],$a['password'])){session_regenerate_id(true);$_SESSION['aid']=$a['id'];go('index.php');}$err='Invalid credentials.';}
$title='Admin Login';require '../includes/header.php';?><div class="box"><h2>Admin Login</h2><?=$err?'<p style="color:#c62828">'.e($err).'</p>':''?><form method="post"><?=csrf()?><label>Username</label><input name="u" required><label>Password</label><input type="password" name="p" required><button class="btn">Login</button></form></div><?php require '../includes/footer.php';?>
