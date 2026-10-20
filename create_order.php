<?php
session_start(); header('Content-Type: application/json; charset=utf-8');
function fail($m,$c=400){http_response_code($c);echo json_encode(['error'=>$m]);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST')fail('POST required',405);
$config=require __DIR__.'/config.php';
if(str_contains($config['key_id'],'REPLACE_')||str_contains($config['key_secret'],'REPLACE_'))fail('Add Razorpay API credentials to config.php first.',500);
$raw=file_get_contents('php://input');$data=json_decode($raw,true);if(!is_array($data))fail('Invalid request.');
$amount=filter_var($data['amount']??null,FILTER_VALIDATE_INT);$customer=$data['customer']??[];
if(!$amount||$amount<100||$amount>50000000)fail('Invalid amount.');
foreach(['name','phone','service','pickup','destination'] as $k){if(trim((string)($customer[$k]??''))==='')fail('Missing booking field: '.$k);}
$payload=['amount'=>$amount,'currency'=>'INR','receipt'=>'lc365_'.bin2hex(random_bytes(8)),'notes'=>['service'=>substr($customer['service'],0,200),'pickup'=>substr($customer['pickup'],0,200),'destination'=>substr($customer['destination'],0,200)]];
$ch=curl_init('https://api.razorpay.com/v1/orders');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_USERPWD=>$config['key_id'].':'.$config['key_secret'],CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_TIMEOUT=>20]);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
if($body===false)fail('Gateway connection failed: '.$err,502);$r=json_decode($body,true);if($code<200||$code>=300||empty($r['id']))fail($r['error']['description']??'Gateway order creation failed.',502);
$_SESSION['lc365_orders'][$r['id']]=['customer'=>$customer,'amount'=>$amount,'currency'=>'INR','created'=>time()];echo json_encode(['id'=>$r['id'],'amount'=>$r['amount'],'currency'=>$r['currency'],'key_id'=>$config['key_id']]);
