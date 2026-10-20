<?php
session_start();
$token=(string)($_GET['token']??'');$x=$_SESSION['lc365_invoices'][$token]??null;if(!$x){http_response_code(403);exit('Invoice unavailable. Complete and verify payment first.');}
function clean_pdf($s){$s=(string)$s;if(function_exists('iconv')){$v=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);if($v!==false)$s=$v;}return preg_replace('/[^\x20-\x7E]/','?',$s);}
function esc($s){return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],clean_pdf($s));}
$lines=['LifeCare365 - PAYMENT INVOICE','Status: PAID (verified with payment gateway)','Invoice reference: '.$x['payment_id'],'Order reference: '.$x['order_id'],'Paid at: '.$x['paid_at'],'Amount: INR '.$x['amount'],'Customer: '.$x['name'],'Mobile: '.$x['phone'],'Service: '.$x['service'],'Pickup / billing address: '.$x['pickup'],'Destination: '.$x['destination'],'Currency: '.$x['currency'],'','This invoice is generated after server-side payment verification.'];
$stream="BT
/F1 12 Tf
50 790 Td
";foreach($lines as $i=>$line){if($i>0)$stream.="0 -28 Td
";$stream.='('.esc($line).") Tj
";}$stream.="ET";
$objs=[];$objs[]='<< /Type /Catalog /Pages 2 0 R >>';$objs[]='<< /Type /Pages /Kids [3 0 R] /Count 1 >>';$objs[]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>';$objs[]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';$objs[]='<< /Length '.strlen($stream)." >>
stream
".$stream."
endstream";
$pdf="%PDF-1.4
";$offsets=[0];foreach($objs as $i=>$o){$offsets[]=strlen($pdf);$pdf.=($i+1)." 0 obj
".$o."
endobj
";}$xref=strlen($pdf);$pdf.="xref
0 ".(count($objs)+1)."
0000000000 65535 f 
";for($i=1;$i<count($offsets);$i++)$pdf.=sprintf('%010d 00000 n ',$offsets[$i])."
";$pdf.="trailer
<< /Size ".(count($objs)+1)." /Root 1 0 R >>
startxref
$xref
%%EOF";
header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="LifeCare365-invoice-'.preg_replace('/[^A-Za-z0-9_-]/','',$x['payment_id']).'.pdf"');header('Content-Length: '.strlen($pdf));echo $pdf;
