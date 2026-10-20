<?php
session_start();header('Content-Type: application/json; charset=utf-8');
function fail($m,$c=400){http_response_code($c);echo json_encode(['error'=>$m,'verified'=>false]);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST')fail('POST required',405);$config=require __DIR__.'/config.php';$d=json_decode(file_get_contents('php://input'),true);if(!is_array($d))fail('Invalid request.');
$oid=(string)($d['razorpay_order_id']??$d['order_id']??'');$pid=(string)($d['razorpay_payment_id']??'');$sig=(string)($d['razorpay_signature']??'');$saved=$_SESSION['lc365_orders'][$oid]??null;
if(!$oid||!$pid||!$sig||!$saved)fail('Order/session not found. Please check payment status.');$expected=hash_hmac('sha256',$oid.'|'.$pid,$config['key_secret']);if(!hash_equals($expected,$sig))fail('Payment signature verification failed.');
// Verify payment status directly with Razorpay; do not trust the browser response alone.
$ch=curl_init('https://api.razorpay.com/v1/payments/'.rawurlencode($pid));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>$config['key_id'].':'.$config['key_secret'],CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>20]);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$p=json_decode((string)$body,true);
if($code<200||$code>=300||!is_array($p))fail('Could not confirm payment with gateway.',502);
if(($p['order_id']??'')!==$oid||(int)($p['amount']??0)!==(int)$saved['amount']||($p['currency']??'')!=='INR'||!in_array(($p['status']??''),['captured'],true))fail('Payment is not captured or does not match this order.');
$customer=$saved['customer'];$customer['amount']=number_format(((int)$saved['amount'])/100,2,'.','');$customer['currency']='INR';$customer['payment_id']=$pid;$customer['order_id']=$oid;$customer['paid_at']=date('c');$token=bin2hex(random_bytes(24));$_SESSION['lc365_invoices'][$token]=$customer;unset($_SESSION['lc365_orders'][$oid]);echo json_encode(['verified'=>true,'invoice_token'=>$token,'payment_id'=>$pid]);
