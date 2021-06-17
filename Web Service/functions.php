<?php

function formatTime($MwTime, $hsec = true) {

	if ($MwTime == -1) {
		return '???';
	} else {
		$minutes = floor($MwTime/(1000*60));
		$seconds = floor(($MwTime - $minutes*60*1000)/1000);
		$hseconds = substr($MwTime, strlen($MwTime)-3, 2);
		if ($hsec) {
			$tm = sprintf('%02d:%02d.%02d', $minutes, $seconds, $hseconds);
		} else {
			$tm = sprintf('%02d:%02d', $minutes, $seconds);
		}
	}
	if ($tm[0] == '0') {
		$tm = substr($tm, 1);
	}
	return $tm;
}  // formatTime

?>