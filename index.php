<?php require 'inc.php';start('Dashboard');$mon=date('Y-m');
$k=fn($s,$a=[])=>q($s,$a)->fetchColumn();
$today=$k('SELECT COALESCE(SUM(litres),0) FROM delivery WHERE delivery_date=CURDATE()');
$tested=$k("SELECT COALESCE(SUM(d.litres),0) FROM delivery d JOIN quality_test t USING(delivery_id)");
$rej=$k("SELECT COALESCE(SUM(d.litres),0) FROM delivery d JOIN quality_test t USING(delivery_id) WHERE t.result='Rejected'");
$due=0;foreach(calc($mon) as $r){if(!$k('SELECT COUNT(*) FROM payment WHERE member_id=? AND pay_month=?',[$r['member_id'],$mon]))$due+=$r['net'];}
$inc=$k("SELECT COALESCE(SUM(litres*price_per_litre),0) FROM milk_sale WHERE DATE_FORMAT(sale_date,'%Y-%m')=?",[$mon]);
$low=q('SELECT item_name,quantity FROM stock_item WHERE quantity<=reorder_level')->fetchAll();
$days=q("SELECT delivery_date d,SUM(litres) l FROM delivery WHERE delivery_date>=CURDATE()-INTERVAL 6 DAY GROUP BY delivery_date ORDER BY d")->fetchAll();$mx=max(1,...array_column($days,'l')?:[1]);
?><div class="card kpis"><div class="kpi">Members<b><?=$k('SELECT COUNT(*) FROM member')?></b></div><div class="kpi">Litres today<b><?=n($today)?></b></div><div class="kpi">Rejection rate<b><?=$tested?round($rej/$tested*100,1):0?>%</b></div>
<div class="kpi">Farmer pay due (<?=$mon?>)<b>UGX <?=n($due)?></b></div><div class="kpi">Milk sales (<?=$mon?>)<b>UGX <?=n($inc)?></b></div><div class="kpi">Low-stock items<b><?=count($low)?></b></div></div>
<?php foreach($low as $l)echo '<div class="msg">Low stock: '.e($l['item_name']).' ('.$l['quantity'].' left)</div>';?>
<div class="card"><h3>Milk collected, last 7 days (litres)</h3><div style="display:flex;align-items:flex-end;gap:10px;height:160px"><?php foreach($days as $d)echo '<div style="flex:1;text-align:center;font-size:12px">'.n($d['l']).'<span style="display:block;background:#7fb069;height:'.round($d['l']/$mx*120).'px"></span>'.substr($d['d'],5).'</div>';?></div></div>
<?php done();
