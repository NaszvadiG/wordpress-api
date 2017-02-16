<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function nice_date($delta){
	$blocks = array (
		array('tahun',  (3600 * 24 * 365)),
		array('bulan', (3600 * 24 * 30)),
		array('minggu',  (3600 * 24 * 7)),
		array('hari',   (3600 * 24)),
		array('jam',  (3600)),
		array('menit',   (60)),
		array('detik',   (1))
	);
	
	$harian = array(
		'Sunday' => 'Minggu',
		'Monday' => 'Senin',
		'Tuesday' => 'Selasa',
		'Wednesday' => 'Rabu',
		'Thursday' => 'Kamis',
		'Friday' => 'Jumat',
		'Saturday' => 'Sabtu'
	);

	
	#Get the time from the function arg and the time now
	$argtime = strtotime($delta);
	$nowtime = time();
	
	#Get the time diff in seconds
	$diff    = $nowtime - $argtime;
	
	#Store the results of the calculations
	$res = array ();
	
	#Calculate the largest unit of time
	for ($i = 0; $i < count($blocks); $i++) {      
		$title = $blocks[$i][0];      
		$calc  = $blocks[$i][1];      
		$units = floor($diff / $calc);      
		if ($units > 0) {
			$res[$title] = $units;
		}
	}
	
	if (isset($res['tahun']) && $res['tahun'] > 0) {
		if (isset($res['bulan']) && $res['bulan'] > 0 && $res['bulan'] < 12) {        
			$format      = "%s %s %s %s lalu";         
			$year_label  = $res['tahun'] > 1 ? 'tahun' : 'tahun';
			$month_label = $res['bulan'] > 1 ? 'bulan' : 'bulan';
			return sprintf($format, $res['tahun'], $year_label, $res['bulan'], $month_label);
		} else {
			$format     = "%s %s lalu";
			$year_label = $res['tahun'] > 1 ? 'tahun' : 'tahun';
			return sprintf($format, $res['tahun'], $year_label);
		}
	}
	
	if (isset($res['bulan']) && $res['bulan'] > 0) {
		if (isset($res['hari']) && $res['hari'] > 0 && $res['hari'] < 31) {        
			$format      = "%s %s %s %s lalu";         
			$month_label = $res['bulan'] > 1 ? 'bulan' : 'bulan';
			$day_label   = $res['hari'] > 1 ? 'hari' : 'hari';
			return sprintf($format, $res['bulan'], $month_label, $res['hari'], $day_label);
		} else {
			$format      = "%s %s lalu";
			$month_label = $res['bulan'] > 1 ? 'bulan' : 'bulan';
			return sprintf($format, $res['bulan'], $month_label);
		}
	}
	
	if (isset($res['hari']) && $res['hari'] > 0) {
		if ($res['hari'] == 1) {
			return sprintf("kemarin, %s", date('H:i', $argtime));
		}
		if ($res['hari'] <= 7) {
			//$tmp = date
			//return sprintf("%s, %s", $harian[date('l', $argtime)],date('H:i', $argtime)); //date("\h\a\\r\\i l, h:i a", $argtime);
		}
		if ($res['hari'] <= 31) {         
			//return sprintf('%s, %s', $harian[date('l', $argtime)],date('H:i', $argtime)); //date("l \j\a\\t H:i", $argtime);       
			return sprintf('%s hari yang lalu, %s', $res['hari'], date('H:i', $argtime));
		}     
	}         
	if (isset($res['jam']) && $res['jam'] > 0) {
		if ($res['jam'] > 1) {
			return sprintf("%s jam yang lalu", $res['jam']);
		} else {
			return "satu jam yang lalu";
		}
	}
	
	if (isset($res['menit']) && $res['menit']) {
		if ($res['menit'] == 1) {
			return "satu menit yang lalu";
		} else {
			return sprintf("%s menit yang lalu", $res['menit']);
		}
	}
	
	if (isset ($res['detik']) && $res['detik'] > 0) {
		if ($res['detik'] == 1) {
			return "baru saja";
		} else {
			return sprintf("%s detik yang lalu", $res['detik']);
		}
	}
	
	return 'baru saja';
}

function format_num($num, $precision = 2) 
{
	if ($num >= 1000 && $num < 1000000)
	{
		$n_format = round(number_format($num/1000,$precision)).'K';
	} 
	else if ($num >= 1000000 && $num < 1000000000) 
	{
		$n_format = number_format($num/1000000,$precision).'M';
	} 
	else if ($num >= 1000000000) 
	{
		$n_format = number_format($num/1000000000,$precision).'B';
	} 
	else
	{
		$n_format = $num;
	}
	
	return $n_format;
}
/* Selesai */