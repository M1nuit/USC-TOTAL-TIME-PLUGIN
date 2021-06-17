<?php 

require_once('functions.php');
require_once('./classes/tmnick.class.php');

$nb_maps = 28;

if (isset($_GET['c'])) {
	mysql_connect("localhost", "tmf", "#Minuit971");
	mysql_select_db("xaseco");

	$tabClassement = array();
	
	$sql = "SELECT p.Login FROM cup_players AS c, players AS p WHERE p.Id = c.Id_player AND c.nb_finish = 28 ORDER BY c.total_score ASC";
	$result = mysql_query($sql);
	$i = 1;

	while ($row = mysql_fetch_array($result)) {
		$tabClassement[$row['Login']] = $i;
		$i++;
	}
	
	if ($_GET['c'] == 'all') {
		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		$sql = "SELECT * FROM cup_players AS c, players AS p WHERE p.Id = c.Id_player AND c.nb_finish = ".$nb_maps." ORDER BY c.total_score ASC";
		$result = mysql_query($sql);
		$i = 1;
		$precScore = 0;
		while ($row = mysql_fetch_array($result)) {
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $row['total_score'] - $topScore;
				$precEcart = $row['total_score'] - $precScore;
				$precScore = $row['total_score'];
			}
			else if ($i == 1) {
				$topScore = $row['total_score'];
				$precScore = $row['total_score'];
				$ecart = '';
				$precEcart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($row['total_score'])."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'nation') {
		$tabInfos = array();
		
		$sql = "SELECT * FROM cup_players AS c, players AS p WHERE p.Id = c.Id_player AND c.nb_finish = ".$nb_maps." ORDER BY `p`.`Nation` ASC, c.total_score ASC";
		$result = mysql_query($sql);
		$i = 1;
		$precScore = 0;
		while ($row = mysql_fetch_array($result)) {
			if ($i > 1) {
				$ecart = $row['total_score'] - $topScore;
				$precEcart = $row['total_score'] - $precScore;
				$precScore = $row['total_score'];
				if ($row['Nation'] != $precNation) {
					$precNation = $row['Nation'];
					$i = 1;
				}
			}
			if ($i == 1) {
				$topScore = $row['total_score'];
				$precScore = $row['total_score'];
				$precNation = $row['Nation'];
				$ecart = '';
				$precEcart = '';
			}
			
			foreach ($tabClassement as $login => $num) {
				if ($login == $row['Login']) {
					$clsmt = $num;
				}
			}
			
			$tabInfos[$row['Nation']][$i]['nickname'] = $row['NickName'];
			$tabInfos[$row['Nation']][$i]['login'] = $row['Login'];
			$tabInfos[$row['Nation']][$i]['score'] = $row['total_score'];
			$tabInfos[$row['Nation']][$i]['classement'] = $clsmt;
			$tabInfos[$row['Nation']][$i]['ecart'] = $ecart;
			$tabInfos[$row['Nation']][$i]['precEcart'] = $precEcart;
			$i++;
		}
		
		
		$html = '';
		foreach ($tabInfos as $cle => $val) {
			$html .= '<h2 align="center">Top '.$cle.' <img src="images/'.$cle.'.png"></h2>';

			foreach ($val as $i => $infos) {
				if (($i % 2) == 0) {
					$bgcolor = '#4baae4';
				}
				else {
					$bgcolor = '#afeaee';
				}
				
				$html .= '<table width="565" align="center" style="border-collapse:collapse;">';
				
				$html .= "<tr style='background-color:".$bgcolor.";'><td width='60'>".$i.". (".$infos['classement'].")</td><td width='50'><img src='images/".$cle.".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($infos['nickname'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$infos['login']."</td><td width='75'><span style='color:blue;'>".formatTime($infos['score'])."</span></td><td width='75'><span style='color:#ec449b ;'>+".formatTime($infos['ecart'])."</span></td></tr>" ;

				$html .= '</table>';
			}
		}

		echo $html;
	}
	
	else if ($_GET['c'] == 'coast') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'coast' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'desert') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'speed' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'bay') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'bay' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'rally') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'rally' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'snow') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'alpine' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'island') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'island' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'stadium') {
	
		$ladder = array();
		
		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 1;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'stadium' AND p.Id = '".$row['Id_player']."'";
	
			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				if ($score == 1) {
					$ladder[$row2['Login']] = $row2['Score'];
					$score++;
				}
				else
					$ladder[$row2['Login']] += $row2['Score'];
			}
		}
		array_multisort($ladder, SORT_NUMERIC);

		$html = '<table width="565" align="center" style="border-collapse:collapse;">';
		
		$i = 1;
		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT * FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		
			if (($i % 2) == 0) $bgcolor = '#4baae4';
			else $bgcolor = '#afeaee';
			
			if ($i > 1) {
				$ecart = $pscore - $topScore;
			}
			else if ($i == 1) {
				$topScore = $pscore;
				$ecart = '';
			}
			$html .= "<tr style='background-color:".$bgcolor.";'><td width='25'>".$i.")</td><td width='50'><img src='images/".$row['Nation'].".png' width='24' height='24'></td><td width='225'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')."</td><td width='125'>".$row['Login']."</td><td width='75'><span style='color:blue;'>".formatTime($pscore)."</span></td><td width='75'><span style='color:#ec449b;'>+".formatTime($ecart)."</span></td></tr>" ;
			$i++;
		}	
		$html .= '</table>';
		echo $html;
	}
	
	else if ($_GET['c'] == 'player') {
	
		if (empty($_GET['n'])) {
			$html = '<div align="center"><span style="color: #821253;">Le login ne peut pas être vide !</span></div>';
		}
		else {
			$login = $_GET['n'];
			$sql = "SELECT * FROM players AS p, cup_players AS c WHERE p.Id = c.Id_player AND p.Login = '".$login."'";
			$result = mysql_query($sql);
			$nb = mysql_num_rows($result);
			if ($nb == 0) {
				$html = '<div align="center"><span style="color: #821253;">Ce joueur n\'a pas participé à la Penetown Cup !</span></div>';
			}
			else {
				$row = mysql_fetch_array($result);
				
				// Début du tableau joueur
				$html = '<table width="565" align="center">';
				
				// Login + Nickname
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Login :</span></td><td width="300" style="padding-left:40px;">'.$login.'</td></tr>';
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Nickname :</span></td><td width="300" style="padding-left:40px;">'.TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999').'</td></tr>';
				$html .= '<tr><td>&nbsp;</td></tr>';
				
				// Classement général
				$i = 1;
				$clsmt = 1;
				$ladder = array();
				$sql = "SELECT * FROM players AS p, cup_players AS c WHERE p.Id = c.Id_player AND c.nb_finish = ".$nb_maps." ORDER BY c.total_score ASC";
				$result2 = mysql_query($sql);
				$total = mysql_num_rows($result2);
				while($row2 = mysql_fetch_array($result2)) {
					if ($i == 1) {
						$topScore = $row2['total_score'];
						$ecart = 0;
					}
				
					if ($row2['Login'] == $login) {
						if ($i > 1) $ecart = $row2['total_score'] - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement général :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Classement nations
				$sql = "SELECT * FROM cup_players AS c, players AS p WHERE p.Id = c.Id_player AND c.nb_finish = ".$nb_maps." AND p.Nation = '".$row['Nation']."' ORDER BY c.total_score ASC";
				$result2 = mysql_query($sql);
				$i = 1;
				while ($row2 = mysql_fetch_array($result2)) {
					if ($i == 1) {
						$topScore = $row2['total_score'];
						$ecart = 0;
					}
				
					if ($row2['Login'] == $login) {
						if ($i > 1) $ecart = $row2['total_score'] - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement '.$row['Nation'].' :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				$html .= '<tr><td>&nbsp;</td></tr>';
				
				// Classement par environnement
				// Coast
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'coast' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Coast :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Desert
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'speed' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Desert :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Bay
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'bay' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Bay :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Rally
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'rally' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Rally :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Island
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'island' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Island :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Snow
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'alpine' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Snow :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Stadium
				$ladder = array();
		
				$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$nb_maps;
				$result2 = mysql_query($sql);
				while ($row2 = mysql_fetch_array($result2)) {
					$score = 1;
					$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = 'stadium' AND p.Id = '".$row2['Id_player']."'";
			
					$result3 = mysql_query($sql);
					while ($row3 = mysql_fetch_array($result3)) {
						if ($score == 1) {
							$ladder[$row3['Login']] = $row3['Score'];
							$score++;
						}
						else
							$ladder[$row3['Login']] += $row3['Score'];
					}
				}
				array_multisort($ladder, SORT_NUMERIC);
				
				$i = 1;
				foreach ($ladder as $plogin => $pscore) {
					if ($i == 1) {
						$topScore = $pscore;
						$ecart = 0;
					}
					
					if ($plogin == $row['Login']) {
						if ($i > 1) $ecart = $pscore - $topScore;
						$clsmt = $i;
					}
					else {
						$i++;
					}
				}
				if ($clsmt != 1) $prefix = 'ème';
				else if ($clsmt == 1) $prefix = 'er';
				
				$html .= '<tr><td width="300" align="right"><span style="color: #821253;">Classement Stadium :</span></td><td width="300" style="padding-left:40px;">'.$clsmt . $prefix.' (+'.formatTime($ecart).')</td></tr>';
				
				// Fin du tableau joueur
				$html .= '</table>';
			}
		}
		echo $html;
	}
	
	mysql_close();
}

?>
