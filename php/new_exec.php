<?php

const FILE_PATH = '/var/www/html/aircon/php/';
if (file_exists(FILE_PATH . 'savedata')) {
    $bytes = unserialize(file_get_contents(FILE_PATH . 'savedata'));

    $bytes[17] = 0;
} else {
    $bytes = [
        '0' => 0x23, //固定値
        '1' => 0xcb, //固定値
        '2' => 0x26, //固定値
        '3' => 0x01, //固定値
        '4' => 0x00, //固定値
        '5' => 0x00, //電源  入：0x20 切：0
        '6' => 0x58, //モード 冷房(体感入)：0x58 冷房(体感切)：0x18 暖房(体感入)：0x48 暖房(体感切)：0x08 除湿(体感入)：0x50 除湿(体感切)：0x10 送風：0x38
        '7' => 12, //設定温度  設定温度-16
        '8' => 0x32, //上位4ビット [風左右] 最左：0x1 左：0x2 中央：0x3 右：0x4 最右：0x5 回転：0xc
        //下位4ビット [除湿強度] 強：0x0 標準：0x2 弱：0x4 冷房:0x6
        '9' => 0x40, //上位2ビット [動作音] 1回：1 2回：2
        //中央3ビット [風上下] 自動：0 最上：1 上：2 中：3 下：4 最上：5 回転：7
        //下位3ビット [風速] 弱：1 中：2 強、パワフル：3
        '10' => 0x00, //固定値
        '11' => 0x00, //固定値
        '12' => 0x00, //固定値
        '13' => 0x00, //風エリア 風左右：0x00 左半分：0x40 右半分：0xC0
        '14' => 0x0e, //内部クリーン 入:0x0e 切：0x10
        '15' => 0x00, //風速パワフル パワフル：10 通常：0x00
        '16' => 0x00, //固定値
        '17' => 0, //チェック
    ];
}
function aeha($T, $bytes, $repeats, $interval)
{
    // $bytes[5]=0;
    $bytes = updateCheck($bytes);

    $i = 0;
    $length = count($bytes);
    $fname = 'code';
    // $user=shell_exec('whoami');
    // if($user!="pi\n"){
    //   $fname='codes_webuser';
    // }
    $file = fopen(FILE_PATH . $fname, 'w');
    flock($file, LOCK_EX);
    fwrite($file, '{"aircon:on": [');
    while (1) {
        fwrite($file, ($T * 8) . "," . ($T * 4) . ",");
        for ($j = 0; $j < $length; $j++) {
            for ($k = 0; $k < 8; $k++) {
                if ($bytes[$j] & (1 << $k)) {
                    fwrite($file, $T . "," . ($T * 3) . ",");
                } else {
                    fwrite($file, $T . "," . $T . ",");
                }
            }
        }
        if (++$i >= $repeats) {
            fwrite($file, $T);
            break;
        } else {
            fwrite($file, $T . " " . $interval);
        }
    }
    fwrite($file, ']}');
    flock($file, LOCK_UN);
    fclose($file);
    chmod(FILE_PATH . $fname, 0777);
    shell_exec('sudo python3 ' . FILE_PATH . 'irrp.py -p -g25 -f ' . FILE_PATH . $fname . ' aircon:on');
    if ($bytes[5] == 0x20) {
        SaveData($bytes);
    }
}

function SaveData($bytes)
{
    $s = serialize($bytes);
    $res = file_put_contents(FILE_PATH . 'savedata', $s);
    chmod(FILE_PATH . 'savedata', 0777);
}

function GetOption()
{
    $bytes = unserialize(file_get_contents(FILE_PATH . 'savedata'));
    $mode = [
        0x58 => '冷房(体感入)',
        0x18 => '冷房(体感切)',
        0x48 => '暖房(体感入)',
        0x08 => '暖房(体感切)',
        0x50 => '除湿(体感入)',
        0x10 => '除湿(体感切)',
        0x38 => '送風'
    ][$bytes[6]];
    $speed = [
        '自動',
        '弱',
        '中',
        '強'
    ][$bytes[9] & 7];
    $area = [
        0 => '風左右',
        0x80 => '全体',
        0x40 => '左半分',
        0xC0 => '右半分'
    ][$bytes[13]];
    $dryintensity = [
        0 => '強',
        2 => '標準',
        4 => '弱',
        6 => '冷房'
    ][$bytes[8] & 15];
    $sound = [
        "なし",
        "あり"
    ][$bytes[9] & 63];
    print "モード:{$mode}\n";
    if ($bytes[6] == 80 || $bytes[6] == 16) {
        print "除湿強度:{$dryintensity}\n";
    } else {

        print "設定温度:" . ($bytes[7] + 16) . "℃\n";
    }
    print "風速:{$speed}\n";
    print "風エリア:{$area}\n";
    print "音:{$sound}\n";
}

function GetOption2()
{
    global $bytes;
    $mode = [
        0x58 => '冷房(体感)',
        0x18 => '冷房',
        0x48 => '暖房(体感)',
        0x08 => '暖房',
        0x50 => '除湿(体感)',
        0x10 => '除湿',
        0x38 => '送風'
    ][$bytes[6]];
    $speed = [
        '自動',
        '弱',
        '中',
        '強'
    ][$bytes[9] & 7];
    $area = [
        0 => '風左右',
        0x80 => '全体',
        0x40 => '左半分',
        0xC0 => '右半分'
    ][$bytes[13]];
    $dryintensity = [
        0 => '強',
        2 => '標準',
        4 => '弱',
        6 => '冷房'
    ][$bytes[8] & 15];
    $sound = [
        "なし",
        "あり"
    ][$bytes[9] & 63];
    print "モードは{$mode}。\n";
    if ($bytes[6] == 80 || $bytes[6] == 16) {
        print "除湿強度は{$dryintensity}。\n";
    } else {

        print "設定温度は" . ($bytes[7] + 16) . "℃。\n";
    }
    print "風速は{$speed}。\n";
    print "風エリアは{$area}。\n";
    print "音は{$sound}。\n";
}

function GetHelp()
{
    $str = <<<EOL
-p
電源オフ:0  

-m 【モード】
cool-b:冷房(体感入)
cool:冷房
heat-b:暖房(体感入)
heat:暖房
dry-b:除湿(体感入)
dry:除湿
wind:送風

-t 【温度設定】

-d 【除湿強度】
h:強
n:標準
l:弱

-v 【風向上下】
0:自動
1〜5：最上
6:回転

-s 【風速】
0:自動
1:弱
2:中
3:強
4:パワフル


-a 【風エリア】
none:風左右の値を利用
whiole:全体
left:左半分
right:右半分

-g 
-vo 【動作音】
0:なし
1:あり

EOL;
    print $str;
}

function SetPower($power)
{
    global $bytes;
    if (!$power || $power === "off" || $power === "false") {
        $bytes[5] = 0;
    } else {
        $bytes[5] = 0x20;
    }
}

function SetMode($mode)
{ // モードをセット（cool: 冷房, heat: 暖房, dry: 除湿, wind: 送風）
    global $bytes;
    $b = ["cool-b" => 0x58, "cool" => 0x18, "heat-b" => 0x48, "heat" => 8, "dry-b" => 0x50, "dry" => 0x10, "wind" => 0x38][$mode];
    if (!$b) {
        print "Mode {$mode} is not defined!\n";
        return false;
    }
    SetPower(true);
    $bytes[6] = $b;
    return true;
}

function SetDryIntensity($intensity)
{ // 除湿強度（high: 強, normal: 標準, low: 弱）
    // var_dump($intensity);
    global $bytes;

    $b = ["h" => 0, "n" => 0x02, "l" => 0x04, "c" => 0x06];
    if (!$b) {
        print "Dry intensity {$intensity} is not defined!";
        return false;
    }
    $bytes[8] = ($bytes[8] & 240) | $b[$intensity];
    return true;
}

function SetTemperature($temperature)
{ // 設定温度をセット（16~31）
    global $bytes;
    if ($temperature < 16 || $temperature > 31) {
        print "Temperature {$temperature} is out of range (16 ~ 31).";
        return false;
    }
    $bytes[7] = $temperature - 16;
    return true;
}

function SetWindSpeed($speed)
{ // 風速（0: 自動, 1: 弱, 2: 中, 3: 強, 4: パワフル）
    global $bytes;
    if ($speed < 0 || $speed > 4) {
        print "Wind speed {$speed} is out of range (0 ~ 4).";
        return false;
    }
    $powerful = 0;
    if ($speed == 4) {
        $speed = 3;
        $powerful = 16;
    }
    $bytes[9] = ($bytes[9] & 248) | $speed;
    $bytes[15] = $powerful;
    return true;
}

function SetWindVertical($vertical)
{ // 風向上下（0: 自動, 1: 最上 ~ 5: 最下, 6: 回転）
    global $bytes;
    if ($vertical < 0 || $vertical > 6) {
        print "Vertical wind direction {$vertical} is out of range (0 ~ 6).";
        return false;
    }
    if ($vertical == 6) {
        $vertical = 7;
    }
    $bytes[9] = ($bytes[8] & 199) | ($vertical << 3);
    return true;
}

function SetWindArea($area)
{ // 風エリア（none: 風左右の値を利用, whole: 全体, left: 左半分, right: 右半分）
    global $bytes;
    $b = ["none" => 0, "whole" => 0x80, "left" => 0x40, "right" => 0xC0][$area];
    if (!$b) {
        print "Wind area {$area} is not defined!";
        return false;
    }
    $bytes[13] = $b;
    return true;
}

function SetSysVolume($vol)
{ //音(なし：0, あり：1)

    global $bytes;
    if ($vol == '0' || $vol == '1') {
        $bytes[9] = ($bytes[9] & 63) | ($vol << 6);
        return true;
    } else {
        print "0 or 1 (0:mute , 1:sound)";
        return false;
    }
}

function updateCheck($bytes)
{
    $sum = 0;
    for ($i = 0; $i < count($bytes) - 1; $i++) {
        $sum += $bytes[$i];
    }
    $bytes[17] = $sum & 255;
    return $bytes;
}

if (@$_SERVER["REQUEST_METHOD"] == "GET") {
    $count = 0;
    $query = [
        'm' => ''
    ];
    $w = $_GET['key'];
    if (strpos($w, '消') !== false) {
        SetPower(0);
    } else {

        if (strpos($w, '状態') !== false) {
            GetOption2();
            exit;
        }
        if (strpos($w, '除湿') !== false) {
            SetMode('dry');
            print "除湿運転を開始します。\n";
            $query['m'] = 'dry';
        } else if (strpos($w, '暖房') !== false) {
            print "暖房運転を開始します。\n";
            SetMode('heat');
            $query['m'] = 'heat';
        } else if (strpos($w, '冷房') !== false) {
            print "冷房運転を開始します。\n";
            SetMode('cool');
            $query['m'] = 'cool';
        }
        if (strpos($w, '下げ') !== false || strpos($w, '暑い') !== false) {

            $bytes[7]--;
            print "温度が" . ($bytes[7] + 16) . "℃に変更されました。\n";
        } else if (strpos($w, '上げ') !== false || strpos($w, '寒い') !== false) {
            $bytes[7]++;
            print "温度が" . ($bytes[7] + 16) . "℃に変更されました。\n";
        }
        if (strpos($w, '強') !== false) {
            SetDryIntensity('h');
            print "除湿強度が強めに変更されました。\n";
        } else if (strpos($w, '普通') !== false) {
            SetDryIntensity('n');
            print "除湿強度が普通に変更されました。\n";
        } else if (strpos($w, '弱') !== false) {
            SetDryIntensity('l');
            print "除湿強度が弱めに変更されました。\n";
        }
        // if (strpos($w, '後') !== false) {
        //     preg_match('/(\d+)/',$w,$match);
        //     // print_r($match[0]);
        //     if (strpos($w, '秒') !== false) {
        //         exec("php timer.php ".$match[0]);

        //     }else if (strpos($w, '分') !== false) {
        //         sleep($match[0]*60);
        //     }else if (strpos($w, '時間') !== false) {
        //         sleep($match[0]*60*60);
        //     }
            
            
        // }

        // var_dump($bytes);
        GetOption2();
    }
} elseif (count($argv) == 1) {
    $bytes = unserialize(file_get_contents(FILE_PATH . 'savedata'));
} else {
    for ($i = 1; $i < count($argv);) {
        if ($argv[$i] == "-p" || $argv[$i] == "--power") {
            SetPower($argv[$i + 1]);
            $i += 2;
        } elseif ($argv[$i] == "-m" || $argv[$i] == "--mode") {
            if (!SetMode($argv[$i + 1])) {
                print "Invalid value of mode option {$argv[i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-t" || $argv[$i] == "--temperature") {
            if (!SetTemperature($argv[$i + 1])) {
                print "Invalid value of temperature option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-d" || $argv[$i] == "--dry_intensity") {
            if (!SetDryIntensity($argv[$i + 1])) {
                print "Invalid value of dry intensity option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-h" || $argv[$i] == "--horizontal") {
            if (!SetWindHorizontal($argv[$i + 1])) {
                print "Invalid value of wind horizontal option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-v" || $argv[$i] == "--vertical") {
            if (!SetWindVertical($argv[$i + 1])) {
                print "Invalid value of wind vertical option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-s" || $argv[$i] == "--speed") {
            if (!SetWindSpeed($argv[$i + 1])) {
                print "Invalid value of wind speed option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-a" || $argv[$i] == "--area") {
            if (!SetWindArea($argv[$i + 1])) {
                print "Invalid value of wind area option {$argv[$i + 1]}";
            }
            $i += 2;
        } elseif ($argv[$i] == "-g" || $argv[$i] == "--get") {
            GetOption();
            exit;
            $i++;
        } elseif ($argv[$i] == "-vo" || $argv[$i] == "--volume") {
            if (!SetSysVolume($argv[$i + 1])) {
                print "IInvalid value of system volume option {$argv[$i + 1]}";
                var_dump(SetSysVolume($argv[$i + 1]));
            }
            $i += 2;
        } elseif ($argv[$i] == "--help") {
            GetHelp();
            $i++;
        } else {
            print "Invalid option key \"{$argv[$i]}\"";
            $i += 2;
            exit;
        }
    }
}

aeha(430, $bytes, 1, 1330);
print "\n";


// print_r($bytes);
