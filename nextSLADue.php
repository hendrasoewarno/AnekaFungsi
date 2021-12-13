<?php
 
//duration in minutes
//pullToStart (jika lewat waktu 17:30 dianggap 17:00)
function nextSLADue($unixtimestamp, $duration, $pullToEnd=1) {
	//function ini tidak bekerja untuk $duration 0 atau negatif
	if ($duration <= 0)
		return $unixtimestamp;
	
	$holiday = array("2021-12-13","2021-12-14","2021-12-15");
	
    $worktime = array(
        0 => array("from"=>8*60*60, "to"=>8*60*60),
        1 => array("from"=>8*60*60, "to"=>17*60*60),
        2 => array("from"=>8*60*60, "to"=>17*60*60),
        3 => array("from"=>8*60*60, "to"=>17*60*60),
        4 => array("from"=>8*60*60, "to"=>17*60*60),
        5 => array("from"=>8*60*60, "to"=>17*60*60),
        6 => array("from"=>8*60*60, "to"=>17*60*60),
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
	
    $noOfDay=0;
	$totalRemainDuration = $remainDuration + $duration*60;
	
    //hitung jumlah hari durasi
    while($totalRemainDuration > ($worktime[$dow]["to"]-$worktime[$dow]["from"])) {		
        $noOfDay++; //maju satu hari
		$dow++; //maju satu hari
		if ($dow>6) $dow=0; //kembali ke minggu
		
		if (!in_array(date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400), $holiday))
			$totalRemainDuration-=($worktime[$dow]["to"]-$worktime[$dow]["from"]);
		else
			echo "Holiday :" . (date("Y-m-d", ($noOfDaySince1Jan1970+$noOfDay)*86400)) . "</br>";
    }

    echo "End DOW:" . $dow . "</br>";
    echo "NoOfDay:" . $noOfDay . "</br>";
    echo "RemainDuration:" . $totalRemainDuration . "</br>";
   
    $result = ($noOfDaySince1Jan1970+$noOfDay)*86400 + $worktime[$dow]["from"] + $totalRemainDuration;
    return $result;
}

//unit test
$sekarang = strtotime("2021-12-11 17:30:00");
echo(date("Y-m-d H:i:s",$sekarang)) . "</br>";
echo "--- Uji Pull to End=0</br>";
echo "*Uji -10 menit</br>";
$berikutnya = nextSLADue($sekarang, -10, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 0 menit</br>";
$berikutnya = nextSLADue($sekarang, 0, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 10 menit</br>";
$berikutnya = nextSLADue($sekarang, 10, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 20 menit</br>";
$berikutnya = nextSLADue($sekarang, 20, 0);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "--- Uji Pull to End=1</br>";
echo "*Uji -10 menit</br>";
$berikutnya = nextSLADue($sekarang, -10, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 0 menit</br>";
$berikutnya = nextSLADue($sekarang, 0, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 10 menit</br>";
$berikutnya = nextSLADue($sekarang, 10, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
echo "*Uji 20 menit</br>";
$berikutnya = nextSLADue($sekarang, 20, 1);
echo(date("Y-m-d H:i:s",$berikutnya));
echo "</br>";
?>