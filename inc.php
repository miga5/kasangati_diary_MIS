<?php
session_start();
const DB_HOST='localhost',DB_NAME='kasangati_dairy',DB_USER='root',DB_PASS='';
const PRICE_A=1300,PRICE_B=1100; // UGX per litre by grade
function db(){static $p;if(!$p){
 try{$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
 catch(PDOException $x){
  if(strpos($x->getMessage(),'1049')!==false){header('Location: install.php');exit;} // database not created yet: run the installer
  die('Cannot connect to MySQL. Start MySQL in the XAMPP Control Panel. ('.htmlspecialchars($x->getMessage()).')');}
 }return $p;}
function q($sql,$a=[]){$s=db()->prepare($sql);$s->execute($a);return $s;}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function n($x){return number_format((float)$x);}
const ACCESS=['manager'=>['index','members','deliveries','sales','stock','reports'],'collector'=>['deliveries','members'],'qc'=>['quality'],
 'cashier'=>['stock','payments'],'finance'=>['payments','reports','index'],'board'=>['index','reports'],'farmer'=>['portal']];
const LABEL=['index'=>'Dashboard','members'=>'Members','deliveries'=>'Milk intake','quality'=>'Quality tests','payments'=>'Payments','sales'=>'Milk sales','stock'=>'Feed & drug stock','reports'=>'Reports','portal'=>'My account'];
function guard(){ // runs before any page logic, so POST requests are protected too
 $f=basename($_SERVER['PHP_SELF'],'.php');
 if(empty($_SESSION['uid'])){header('Location: login.php');exit;}
 if(!in_array($f,ACCESS[$_SESSION['role']])){header('Location: '.ACCESS[$_SESSION['role']][0].'.php');exit;}
}
if(!in_array(basename($_SERVER['PHP_SELF'],'.php'),['login','logout','install']))guard();
function start($title){
 $f=basename($_SERVER['PHP_SELF'],'.php');
 echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' - Kasangati Dairy MIS</title><link rel="stylesheet" href="style.css"></head><body>';
 echo '<header><b>Kasangati Dairy MIS</b><span>'.e($_SESSION['name']).' <a class="btn alt" href="logout.php">Log out</a></span></header><nav>';
 foreach(ACCESS[$_SESSION['role']] as $p) echo '<a href="'.$p.'.php" class="'.($p==$f?'on':'').'">'.LABEL[$p].'</a>';
 echo '</nav><main>';
 if(!empty($_SESSION['flash'])){echo '<div class="msg">'.e($_SESSION['flash']).'</div>';unset($_SESSION['flash']);}
 echo '<h2>'.e($title).'</h2>';
}
function done(){echo '</main></body></html>';}
function flash($m,$to=null){$_SESSION['flash']=$m;header('Location: '.($to?:basename($_SERVER['PHP_SELF'])));exit;}
function post(){return $_SERVER['REQUEST_METHOD']==='POST';}
function members_options(){$o='';foreach(q('SELECT member_id,full_name FROM member ORDER BY member_id') as $m)$o.='<option value="'.$m['member_id'].'">'.$m['member_id'].' - '.e($m['full_name']).'</option>';return $o;}
// Monthly figures per farmer: accepted litres, rejected litres, gross pay, credit deductions
function calc($mon,$member=null){
 $sql="SELECT m.member_id,m.full_name,COUNT(d.delivery_id) deliveries,
 COALESCE(SUM(CASE WHEN t.result='Accepted' THEN d.litres END),0) litres,
 COALESCE(SUM(CASE WHEN t.result='Rejected' THEN d.litres END),0) rejected,
 COALESCE(SUM(CASE WHEN t.grade='A' THEN d.litres*? WHEN t.grade='B' THEN d.litres*? END),0) gross,
 (SELECT COALESCE(SUM(s.amount),0) FROM stock_issue s WHERE s.member_id=m.member_id AND s.on_credit=1 AND DATE_FORMAT(s.issue_date,'%Y-%m')=?) ded
 FROM member m LEFT JOIN delivery d ON d.member_id=m.member_id AND DATE_FORMAT(d.delivery_date,'%Y-%m')=?
 LEFT JOIN quality_test t ON t.delivery_id=d.delivery_id".($member?' WHERE m.member_id=?':'')." GROUP BY m.member_id,m.full_name ORDER BY m.member_id";
 $a=[PRICE_A,PRICE_B,$mon,$mon];if($member)$a[]=$member;
 $rows=q($sql,$a)->fetchAll();foreach($rows as &$r)$r['net']=max(0,$r['gross']-$r['ded']);return $rows;
}
