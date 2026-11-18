<?php
ini_set("display_errors",1);
// Run once in the browser: http://localhost/kasangati_mis_php/install.php
$p=new PDO('mysql:host=localhost;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$p->exec('CREATE DATABASE IF NOT EXISTS kasangati_dairy CHARACTER SET utf8mb4');$p->exec('USE kasangati_dairy');
$p->exec(file_get_contents(__DIR__.'/database.sql'));
if($p->query('SELECT COUNT(*) FROM member')->fetchColumn()>0)die('Database already installed. <a href="login.php">Go to login</a>');
$i=$p->prepare('INSERT INTO member(member_id,full_name,phone,village,no_of_cows,join_date) VALUES(?,?,?,?,?,CURDATE())');
foreach([['M001','Nakato Sarah','0770000001','Kasangati',6],['M002','Ssemakula John','0770000002','Gayaza',4],['M003','Namubiru Grace','0770000003','Bukerere',9],['M004','Kato Moses','0770000004','Kabanyolo',3],['M005','Nabukenya Rose','0770000005','Kasangati',5],['M006','Mugisha David','0770000006','Wakiso',7]] as $m)$i->execute($m);
$u=$p->prepare('INSERT INTO user_account(username,password_hash,role,full_name,member_id) VALUES(?,?,?,?,?)');
foreach([['manager','manager123','manager','Manager'],['collector','collect123','collector','Milk Collection Officer'],['quality','quality123','qc','Quality Controller'],['cashier','cashier123','cashier','Cashier / Feed Attendant'],['finance','finance123','finance','Finance Officer'],['board','board123','board','Board Chairperson']] as $x)$u->execute([$x[0],password_hash($x[1],PASSWORD_DEFAULT),$x[2],$x[3],null]);
foreach($p->query('SELECT member_id,full_name FROM member')->fetchAll(PDO::FETCH_NUM) as $m)$u->execute([strtolower($m[0]),password_hash('farmer123',PASSWORD_DEFAULT),'farmer',$m[1],$m[0]]);
$d=$p->prepare('INSERT INTO delivery(member_id,recorded_by,delivery_date,delivery_time,litres) VALUES(?,2,?,?,?)');
$t=$p->prepare('INSERT INTO quality_test(delivery_id,tested_by,fat_percent,temperature,adulterated,result,grade) VALUES(?,3,?,?,0,?,?)');
foreach(range(5,0) as $ago)foreach(['M001','M002','M003','M004','M005','M006'] as $m){
 $d->execute([$m,date('Y-m-d',strtotime("-$ago day")),'06:30',mt_rand(15,45)]);$id=$p->lastInsertId();
 if($ago>0){$fat=mt_rand(30,42)/10;$ok=$fat>=3.0;$t->execute([$id,$fat,mt_rand(50,95)/10,$ok?'Accepted':'Rejected',$ok?($fat>=3.5?'A':'B'):'-']);}}
$p->exec("INSERT INTO buyer(buyer_name,phone,buyer_type) VALUES('Brookside Processors','0780000001','Processor'),('Kampala Milk Traders','0780000002','Trader')");
$p->exec("INSERT INTO milk_sale(buyer_id,sale_date,litres,price_per_litre) VALUES(1,CURDATE()-INTERVAL 2 DAY,600,1500),(2,CURDATE()-INTERVAL 1 DAY,350,1450)");
$p->exec("INSERT INTO stock_item(item_name,category,quantity,reorder_level,unit_price) VALUES('Dairy meal (50 kg)','Feed',20,8,78000),('Mineral salt (2 kg)','Feed',40,10,9000),('Acaricide (1 L)','Drug',4,5,45000),('Dewormer','Drug',25,6,15000)");
$p->exec("INSERT INTO stock_issue(member_id,item_id,quantity,on_credit,issue_date,amount) VALUES('M001',1,1,1,CURDATE(),78000)");
echo 'Installed. <a href="login.php">Go to login</a>';
