<?php
require_once "DBManager.php";
const FILE_PATH = '/var/www/html/aircon/php/new_exec.php';
$room_temp=[];
$room_hum=[];
$labels=[];
$discom=[];
$who=trim(shell_exec('whoami'));
// print $who;

try {
    $db=getDb();
    //$stt = $db->prepare('SELECT * FROM '.date('Y_m_d'));
      $date=date('Y_m_d');
    
    $stt = $db->prepare('SELECT * FROM '.$date);
    // $stt = $db->prepare('SELECT * FROM 2020_08_19');
    $stt->execute();
    while ($row=$stt->fetch(PDO::FETCH_ASSOC)) {
        $res[]=$row;
    }
    
foreach ($res as $key => $value) {
  $room_temp[]=$value['room_temp'];
  $room_hum[]=$value['room_hum'];
  $labels[]=date('H:i',$value['time']);
  $discom[]=ceil(0.81*$value['room_temp']+0.01*$value['room_hum']*(0.99*$value['room_temp']-14.3)+46.4);
}

print "室温は".(int)(end($room_temp))."度。<br>";
print "湿度は".(int)(end($room_hum))."%。<br>";

print "快適指数は".ceil(end($discom))."。<br>";
if(end($discom)<55){
  print "寒いです。<br>";
}else if (55<=end($discom) && end($discom)<60) {
  print "肌寒いです。<br>";
  
}else if (60<=end($discom) && end($discom)<65) {
  print "普通です。<br>";
  // code...
}else if (65<=end($discom) && end($discom)<70) {
  print "快適です。<br>";
  // code...
}else if (70<=end($discom) && end($discom)<75) {
  print "暑くはないです<br>";
  // code...
}else if (75<=end($discom) && end($discom)<80) {
  print "やや暑いです。<br>";
  // code...
}else if (80<=end($discom) && end($discom)<85) {
  print "暑くて汗が出ます。<br>";
  // code...
}else if (85<=end($discom) ) {
  print "暑くてたまりません。<br>";
  // code...
}else{
  print "それ以外です。<br>";
}

if(end($discom)>=82 && $who=='pi'){
  print end($discom);
  shell_exec('php '.FILE_PATH);
}

} catch (Exception $e) {
    print $e;
}
