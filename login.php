<?php require 'inc.php';$err='';
if(post()){$u=q('SELECT * FROM user_account WHERE username=?',[strtolower(trim($_POST['u']))])->fetch();
 if($u&&password_verify($_POST['p'],$u['password_hash'])){session_regenerate_id(true);$_SESSION=['uid'=>$u['user_id'],'role'=>$u['role'],'name'=>$u['full_name'],'member'=>$u['member_id']];header('Location: '.ACCESS[$u['role']][0].'.php');exit;}
 $err='Wrong username or password.';}
?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><link rel="stylesheet" href="style.css"></head><body>
<div class="card login"><h2>Kasangati Dairy Farmers' Cooperative MIS</h2><form method="post"><input name="u" placeholder="Username or member ID" required><input name="p" type="password" placeholder="Password" required><button style="width:100%">Log in</button></form>
<?php if($err)echo '<p class="msg">'.e($err).'</p>';?><small>Staff: manager / manager123, collector / collect123, quality / quality123, cashier / cashier123, finance / finance123, board / board123. Farmers: member ID (e.g. M001) / farmer123.</small></div></body></html>
