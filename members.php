<?php require 'inc.php';
if(post()){$c=q('SELECT COUNT(*) FROM member')->fetchColumn()+1;$id='M'.str_pad($c,3,'0',STR_PAD_LEFT);
 q('INSERT INTO member VALUES(?,?,?,?,?,CURDATE())',[$id,$_POST['n'],$_POST['ph'],$_POST['v'],(int)$_POST['c']]);
 q('INSERT INTO user_account(username,password_hash,role,full_name,member_id) VALUES(?,?,?,?,?)',[strtolower($id),password_hash('farmer123',PASSWORD_DEFAULT),'farmer',$_POST['n'],$id]);flash("Member $id registered.");}
start('Members');?>
<div class="card"><form class="row" method="post"><div><label>Full name</label><input name="n" required></div><div><label>Phone</label><input name="ph" required></div><div><label>Village</label><input name="v" required></div><div><label>Cows</label><input name="c" type="number" min="1" required style="width:80px"></div><button>Register</button></form></div>
<div class="card wrap"><table><tr><th>ID</th><th>Name</th><th>Phone</th><th>Village</th><th>Cows</th></tr><?php foreach(q('SELECT * FROM member ORDER BY member_id') as $m)echo '<tr><td>'.$m['member_id'].'</td><td>'.e($m['full_name']).'</td><td>'.e($m['phone']).'</td><td>'.e($m['village']).'</td><td>'.$m['no_of_cows'].'</td></tr>';?></table></div><?php done();
