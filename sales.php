<?php require 'inc.php';
if(post()){$b=q('SELECT buyer_id FROM buyer WHERE buyer_name=?',[trim($_POST['b'])])->fetchColumn();
 if(!$b){q('INSERT INTO buyer(buyer_name) VALUES(?)',[trim($_POST['b'])]);$b=db()->lastInsertId();}
 q('INSERT INTO milk_sale(buyer_id,sale_date,litres,price_per_litre) VALUES(?,CURDATE(),?,?)',[$b,$_POST['l'],$_POST['p']]);flash('Sale recorded.');}
start('Milk sales');?><div class="card"><form class="row" method="post"><div><label>Buyer</label><input name="b" required></div><div><label>Litres</label><input name="l" type="number" required style="width:100px"></div><div><label>Price per litre</label><input name="p" type="number" required style="width:110px"></div><button>Save sale</button></form></div>
<div class="card wrap"><table><tr><th>Date</th><th>Buyer</th><th>Litres</th><th>Price</th><th>Amount</th></tr><?php foreach(q('SELECT s.*,b.buyer_name FROM milk_sale s JOIN buyer b USING(buyer_id) ORDER BY sale_id DESC') as $s)echo '<tr><td>'.$s['sale_date'].'</td><td>'.e($s['buyer_name']).'</td><td>'.$s['litres'].'</td><td>'.n($s['price_per_litre']).'</td><td>'.n($s['litres']*$s['price_per_litre']).'</td></tr>';?></table></div><?php done();
