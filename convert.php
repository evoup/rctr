<?php
/*
  +----------------------------------------------------------------------+
  | Name: 转换imp日志为可供R语言加载的格式
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
#column_names <-c("0 BidID", "1 Timestamp", "2 LogType", "3 iPinYouID", "4 UserAgent", "5 IP", "6 Region",
# "7 City", "8 AdExchange", "9 Domain", "10 URL", 
#"11 AnonymousURLID", "12 AdSlotID", "13 AdSlotWidth", "14 AdSlotHeight", "15 AdSlotVisibility", "16 AdSlotFormat",
# "17 AdSlotFloorPrice", "18 CreativeID",
#"19 BiddingPrice", "20 PayingPrice", "21 KeyPageURL", "22 AdvertiserID", "23 UserTags")>

#19 是biddingPrice,20是PayingPrice，6 是region,7是city, 8是adexchange，22是advertiserID
//$fileName = dirname(__FILE__)."/imp.20130609.txt";
$fileName = "/home/evoup/project/dataset/full/ipinyou.contest.dataset/testing2nd/leaderboard.test.data.20130613_15.txt";
$fileName = "/home/evoup/project/dataset/full/ipinyou.contest.dataset/training2nd/imp.20130607.txt";
$baseDir = '/home/evoup/project/dataset/full/ipinyou.contest.dataset/training2nd/';
$fileNames[]=array(
    'imp' => $baseDir."imp.20130606.txt",
    'clk' => $baseDir."clk.20130606.txt"
);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130607.txt",
    //'clk' => $baseDir."clk.20130607.txt"
//);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130608.txt",
    //'clk' => $baseDir."clk.20130608.txt"
//);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130609.txt",
    //'clk' => $baseDir."clk.20130609.txt"
//);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130610.txt",
    //'clk' => $baseDir."clk.20130610.txt"
//);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130611.txt",
    //'clk' => $baseDir."clk.20130611.txt"
//);
//$fileNames[]=array(
    //'imp' => $baseDir."imp.20130612.txt",
    //'clk' => $baseDir."clk.20130612.txt"
//);
foreach ($fileNames as $fileNameArr) {
    $impLog=$fileNameArr['imp'];
    $clkLog=$fileNameArr['clk'];
    $impLogNew=$impLog.".new";
    $fdclk = fopen($clkLog, 'r');
    $clkBidIDS = array();
    while (!feof($fdclk)) {
        $buffer = fgets($fdclk, 4096); 
        $buffer = (explode("\t", $buffer));
        $buf = retColumnedLine($buffer);
        if (!empty($buf['BidID'])) $clkBidIDS[]=$buf['BidID'];
    }
    fclose($fdclk);
    print_r($clkBidIDS);
    $fdimp = fopen($impLog, 'r');
    $fdImpNew = fopen($impLogNew, 'w');
    while (!feof($fdimp)) {
        $buffer = fgets($fdimp, 4096); 
        $buffer = (explode("\t", $buffer));
        if (sizeof($buffer) < 5) continue; // garbage data 
        $buf = retColumnedLine($buffer);
        $col_is_click='0';
        if (in_array($buf['BidID'], $clkBidIDS)) {
            $col_is_click='1';
            $buffer[]=$col_is_click;
        } else {
            $buffer[]=$col_is_click;
        }
        $buffer[1]=substr($buffer[1],8,2); // 时间戳只要小时作为特征
        // ua
        $ua = strtolower($buffer[4]);
        if (!empty(strstr($ua, 'android'))) {
            $os=0;
        } else if (!empty(strstr($ua, 'iphone'))) {
            $os=1;
        } else if (!empty(strstr($ua, 'ipad'))) {
            $os=1;
        } else if (!empty(strstr($ua, 'mac os'))) {
            $os=2;
        } else {
            $os=3;
        }
        $buffer[4]=$os;
        // 用户画像
        if ($buffer[23]!='null') {
            $userTags = explode(',', $buffer[23]);
            sort($userTags);
            $userTags=join('-',$userTags);
            $buffer[23]=crc32($userTags);
        }
        $buffer[23]=str_replace(array("\r", "\n", "\r\n"), "", $buffer[23]);
        $newLine = join("\t", $buffer)."\n";
        fwrite($fdImpNew, $newLine);
    }
    fclose($fdimp);
    fclose($fdImpNew);
}
die;

if (!file_exists($fileName)) {
    echo "file not exists!";
}
$i=0;
$fd = fopen ($fileName, "r"); 
while (!feof ($fd)) 
{ 
   $buffer = fgets($fd, 4096); 
   $buffer = (explode("\t", $buffer));
   $buf = array(
       'BidID'=> $buffer[0],
       'Timestamp'=> $buffer[1],
       'LogType'=> $buffer[2],
       'iPinYouID'=> $buffer[3],
       'UserAgent' => $buffer[4],
       'IP' => $buffer[5],
       'Region' => $buffer[6],
       'City' => $buffer[7],
       'AdExchange' => $buffer[8],
       'Domain' => $buffer[9],
       'URL' => $buffer[10],
       'AnonymousURLID' => $buffer[11],
       'AdSlotID' => $buffer[12],
       'AdSlotWidth' => $buffer[13],
       'AdSlotHeight' => $buffer[14],
       'AdSlotVisibility' => $buffer[15],
       'AdSlotFormat' => $buffer[16],
       'AdSlotFloorPrice' => $buffer[17],
       'CreativeID' => $buffer[18],
       'BiddingPrice' => $buffer[19],
       'PayingPrice' => $buffer[20],
       'KeyPageURL' => $buffer[21],
       'AdvertiserID' => $buffer[22],
       'UserTags' => $buffer[23]
   );
   print_r($buf);
   $i++;
   if ($i>10) break;
} 
fclose ($fd); 

function retColumnedLine($buffer){
   $buf = array(
       'BidID'=> $buffer[0],
       'Timestamp'=> $buffer[1],
       'LogType'=> $buffer[2],
       'iPinYouID'=> $buffer[3],
       'UserAgent' => $buffer[4],
       'IP' => $buffer[5],
       'Region' => $buffer[6],
       'City' => $buffer[7],
       'AdExchange' => $buffer[8],
       'Domain' => $buffer[9],
       'URL' => $buffer[10],
       'AnonymousURLID' => $buffer[11],
       'AdSlotID' => $buffer[12],
       'AdSlotWidth' => $buffer[13],
       'AdSlotHeight' => $buffer[14],
       'AdSlotVisibility' => $buffer[15],
       'AdSlotFormat' => $buffer[16],
       'AdSlotFloorPrice' => $buffer[17],
       'CreativeID' => $buffer[18],
       'BiddingPrice' => $buffer[19],
       'PayingPrice' => $buffer[20],
       'KeyPageURL' => $buffer[21],
       'AdvertiserID' => $buffer[22],
       'UserTags' => $buffer[23]
   );
   return $buf;
}
?> 

