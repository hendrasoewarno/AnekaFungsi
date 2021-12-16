<?php
 
//duration in minutes
//pullToStart (jika lewat waktu 17:30 dianggap 17:00)
function nextSLADue($unixtimestamp, $duration, $pullToEnd=1) {
	//function ini tidak bekerja untuk $duration 0 atau negatif
	if ($duration <= 0)
		return $unixtimestamp;
	
	$holiday = array("2021-12-13","2021-12-14","2021-12-15","2021-12-16");
	
    $worktime = array(
        0 => array("from"=>8*60*60, "ifrom"=> 8*60*60, "ito"=> 8*60*60, "to"=>8*60*60),
        1 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13*60*60,  "to"=>17*60*60),
        2 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13*60*60,  "to"=>17*60*60),
        3 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13*60*60,  "to"=>17*60*60),
        4 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13*60*60,  "to"=>17*60*60),
        5 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13.5*60*60,  "to"=>17*60*60),
        6 => array("from"=>8*60*60, "ifrom"=> 12*60*60, "ito"=> 13*60*60,  "to"=>17*60*60),
    );
   
	$noOfDaySince1Jan1970 = floor($unixtimestamp/86400);
	
	$dow=(($noOfDaySince1Jan1970)+4)%7; //1 jan 1970 adalah hari jumat   
    echo "Start DOW:" . $dow . "</br>";
	
	//Jika sekarang 17:30 (jam kerja s/d 17:00), dan duration 30 menit, maka due besok 9:00
	if ($pullToEnd<1)
		$remainDuration = $unixtimestamp - $noOfDaySince1Jan1970*86400 - $worktime[$dow]["from"];
	//Jika sekarang 17:30 (jam kerja s/d 17:00), dan duration 30 menit, maka due besok 8:30
	else
		$remainDuration = min($unixtimestamp - $noOfDaySince1Jan1970*86400, $worktime[$dow]["to"]) - $worktime[$dow]["from"];
	
	//loncati semua hari minggu dan hari libur, dan buang remainDuration kalau hari libur
	$noOfDay=0;
	while(($worktime[$dow]["to"]-$worktime[$dow]["from"])==0 || in_array(date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400), $holiday)) {
		if (($worktime[$dow]["to"]-$worktime[$dow]["from"])==0)
			echo "Start Sunday :" . (date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400)) . "</br>";
		else
			echo "Start Holiday :" . (date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400)) . "</br>";
		$noOfDay++;
		$dow++; //maju satu hari
		if ($dow>6) $dow=0; //kembali ke minggu	
		if ($remainDuration>0)
			$remainDuration = 0;
	}
	
	//jika jatuh dijam istirahat
	$subAllowance = 0;
	if ($remainDuration > $worktime[$dow]["ito"]-$worktime[$dow]["from"]) //jika lewat jam istirahat perlu dikurangkan
		$subAllowance += $worktime[$dow]["ito"]-$worktime[$dow]["ifrom"];
	else if ($remainDuration > $worktime[$dow]["ifrom"]-$worktime[$dow]["from"]) //jika di jam istiraht perlu dikurangi sejumlah xx:xx:xx - 12:00:00
		$subAllowance += $remainDuration-($worktime[$dow]["ifrom"]-$worktime[$dow]["from"]);
		
	echo "Sub Allowance:" . $subAllowance . "</br>";
	$totalRemainDuration = $remainDuration - $subAllowance + $duration*60; //kurangi waktu istirahat dan tambah durasi
	
    //hitung jumlah hari durasi
    while(1==1) {
		$tempRemainDuration = $totalRemainDuration;
		if (($worktime[$dow]["to"]-$worktime[$dow]["from"])==0)
			echo "Skip Sunday :" . (date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400)) . "</br>";
		else if (in_array(date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400), $holiday))
			echo "Skip Holiday :" . (date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400)) . "</br>";
		else {
			$tempRemainDuration -= (($worktime[$dow]["ifrom"]-$worktime[$dow]["from"])+($worktime[$dow]["to"]-$worktime[$dow]["ito"]));
			if ($tempRemainDuration<=0) break;
		}
		
		$totalRemainDuration = $tempRemainDuration;	
		$noOfDay++; //maju satu hari		
		$dow++; //maju satu hari
		if ($dow>6) $dow=0; //kembali ke minggu		
    }

    echo "End DOW:" . $dow . "</br>";
    echo "NoOfDay:" . $noOfDay . "</br>";
    echo "RemainDuration:" . $totalRemainDuration . "</br>";
	
	//Skip jam istirahat
	$addAllowance = 0;
	if ($totalRemainDuration > $worktime[$dow]["ifrom"]-$worktime[$dow]["from"])  //jika lewat jam istirahat perlu ditambahkan
		$addAllowance += $worktime[$dow]["ito"]-$worktime[$dow]["ifrom"];

	echo "Add Allowance:" . $addAllowance . "</br>";
   
    $result = ($noOfDaySince1Jan1970+$noOfDay)*86400 + $worktime[$dow]["from"] + $totalRemainDuration + $addAllowance;
    return $result;
}

//unit test
$sekarang = strtotime("2021-12-11 16:50:00");
echo(date("Y-m-d H:i:s",$sekarang)) . "</br>";
echo "--- Uji Pull to End=1</br>";
echo "</br>";
echo "</br>";
echo "*Uji 10 menit</br>";
$berikutnya = nextSLADue($sekarang, 10, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "*Uji 30 menit</br>";
$berikutnya = nextSLADue($sekarang, 30, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "*Uji 60 menit</br>";
$berikutnya = nextSLADue($sekarang, 60, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "*Uji 9 jam</br>";
$berikutnya = nextSLADue($sekarang, 9*60, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "--- Uji Pull to End=0</br>";
echo "*Uji 10 menit</br>";
$berikutnya = nextSLADue($sekarang, 10, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "*Uji 30 menit</br>";
$berikutnya = nextSLADue($sekarang, 30, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "</br>";
echo "*Uji 60 menit</br>";
$berikutnya = nextSLADue($sekarang, 60, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "</br>";
echo "*Uji 9 jam</br>";
$berikutnya = nextSLADue($sekarang, 9*60, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
?>
