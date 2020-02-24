<?php

	Aseco::registerEvent('onStartup', 'cup_mlstartup');
	Aseco::registerEvent('onSync', 'cup_mlSync');
	Aseco::registerEvent('onPlayerConnect', 'cup_mlconnect');
	Aseco::registerEvent('onNewChallenge', 'cup_mlnewtrack');
	Aseco::registerEvent('onEndRound', 'cup_mlendRound');
	Aseco::registerEvent('onPlayerConnect', 'cup_mlPlayerConnect');
	Aseco::registerEvent('onPlayerFinish', 'cup_mlPlayerFinish');
	Aseco::registerEvent('onNewChallenge', 'cup_mlNewChallenge');
	Aseco::registerEvent('onRestartChallenge2', 'cup_mlRestartChallenge2');
	Aseco::registerEvent('onEndRace1', 'cup_mlEndRace1');
	Aseco::registerEvent('onPlayerManialinkPageAnswer',	'cup_onPlayerManialinkPageAnswer');

	Aseco::addChatCommand('cup', 'Cup subcommands, see /cup help');
	Aseco::addChatCommand('cupladder', 'Cup ladder subcommands, see /cupladder help');

	function cup_mlstartup ($aseco) {
		global $templates, $cup_config, $color1, $color2;

		// Parse XML
		if (!$xml = $aseco->xml_parser->parseXML('usc_config.xml', true, true)) {
			trigger_error('[USC] Could not read/parse config file "cup_config.xml"!', E_USER_ERROR);
		}
		$cup_config = $xml;

		$cup_config['go'] = false;
		$color1 = $cup_config['CUP']['COLOR'][0]['COLOR1'][0];
		$color2 = $cup_config['CUP']['COLOR'][0]['COLOR2'][0];

		//--------------------------------------------------------------//
		// BEGIN: Window						//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		// %window_title%

		$header  = '<manialink id="20001"></manialink>';		// Always close SubWindows
		$header .= '<manialink id="20000">';

		$header .= '<frame posn="-40.1 30.45 22">';	// BEGIN: Window Frame
		$header .= '<quad posn="0.8 -0.8 0.01" sizen="78.4 53.7" bgcolor="001B"/>';
		$header .= '<quad posn="-0.2 0.2 0.04" sizen="80.4 55.7" style="Bgs1InRace" substyle="BgCard3"/>';

		// Header Line
		$header .= '<quad posn="0.8 -1.3 0.02" sizen="78.4 3" bgcolor="09FC"/>';
		$header .= '<quad posn="0.8 -4.3 0.03" sizen="78.4 0.1" bgcolor="FFF9"/>';
		$header .= '<quad posn="1.8 -1 0.04" sizen="3.2 3.2" style="%icon_style%" substyle="%icon_substyle%"/>';

		// Title
		$header .= '<label posn="5.5 -1.9 0.04" sizen="74 0" textsize="2" scale="0.9" textcolor="FFFF" text="%window_title%"/>';
		$header .= '<quad posn="2.7 -54.1 0.04" sizen="11 1" action="200157" bgcolor="0000"/>';

		// Close Button
		$header .= '<frame posn="77.4 1.3 0.05">';
		$header .= '<quad posn="0 0 0.01" sizen="4 4" style="Icons64x64_1" substyle="ArrowDown"/>';
		$header .= '<quad posn="1.1 -1.35 0.02" sizen="1.8 1.75" bgcolor="EEEF"/>';
		$header .= '<quad posn="0.65 -0.7 0.03" sizen="2.6 2.6" action="20000" style="Icons64x64_1" substyle="Close"/>';
		$header .= '</frame>';

		$header .= '%prev_next_buttons%';

		// Footer
		$footer  = '</frame>';				// END: Window Frame
		$footer .= '</manialink>';

		$templates['WINDOW']['HEADER'] = $header;
		$templates['WINDOW']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Window													//
		//--------------------------------------------------------------//

		//--------------------------------------------------------------//
		// BEGIN: SubWindow												//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		// %window_title%

		$header  = '<manialink id="20001">';
		$header .= '<frame posn="-19.8 15 21.5">';	// BEGIN: Window Frame
		$header .= '<quad posn="0.8 -0.8 0.01" sizen="38 26" bgcolor="001B"/>';
		$header .= '<quad posn="-0.2 0.2 0.04" sizen="39.7 27.85" style="Bgs1InRace" substyle="BgCard3"/>';

		// Header Line
		$header .= '<quad posn="0.8 -1.3 0.02" sizen="38 3" bgcolor="09FC"/>';
		$header .= '<quad posn="0.8 -4.3 0.03" sizen="38 0.1" bgcolor="FFF9"/>';
		$header .= '<quad posn="1.8 -1.6 0.04" sizen="2.2 2.2" style="%icon_style%" substyle="%icon_substyle%"/>';

		// Title
		$header .= '<label posn="4.5 -1.9 0.04" sizen="37 0" textsize="2" scale="0.9" textcolor="FFFF" text="%window_title%"/>';

		// Close Button
		$header .= '<frame posn="36.8 1.3 0.05">';
		$header .= '<quad posn="0 0 0.01" sizen="4 4" style="Icons64x64_1" substyle="ArrowDown"/>';
		$header .= '<quad posn="1.1 -1.35 0.02" sizen="1.8 1.75" bgcolor="EEEF"/>';
		$header .= '<quad posn="0.65 -0.7 0.03" sizen="2.6 2.6" action="20001" style="Icons64x64_1" substyle="Close"/>';
		$header .= '</frame>';

		$header .= '%prev_next_buttons%';

		// Footer
		$footer  = '</frame>';				// END: Window Frame
		$footer .= '</manialink>';

		$templates['SUBWINDOW']['HEADER'] = $header;
		$templates['SUBWINDOW']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: SubWindow												//
		//--------------------------------------------------------------//



		$query = "CREATE TABLE IF NOT EXISTS `cup_players` (
	            `Id` mediumint(9) NOT NULL auto_increment,
	            `Id_player` int(9) NOT NULL,
				`nb_finish` int(9) NOT NULL,
				`total_score` int(64) NOT NULL,
	            PRIMARY KEY (`Id`)
	           ) ENGINE=MyISAM";
		mysql_query($query);
	}

	function cup_mlconnect ($aseco, $player) {

		global $cup_config, $color1, $color2;

		$login = $player->login;
		$id = $player->id;
        $aseco->console('[plugin.usc.php] ' . $login . ' connected');


		$sql = 'SELECT * FROM players AS p, cup_players AS cp
				WHERE p.Id = cp.Id_player
				AND p.Login = "'.$login.'"';
		$reponse = mysql_query($sql);
		if (!mysql_num_rows($reponse)) {
            $aseco->console('[plugin.usc.php] Trying insert ' . $login);
			if (!mysql_query('INSERT INTO cup_players (Id_player, nb_finish, total_score) VALUES (' . $id . ', 0, 0)')) {
                $msg = 'Unable to insert ' . $login . ' into cup_players: ' . mysql_error();
                $aseco->console('[plugin.usc.php]', $msg);
                trigger_error($msg);
            }
		}

		$result = mysql_query("SELECT COUNT(*) AS nb FROM cup_players");
		$nb = mysql_fetch_array($result);
		$cup_config['nb_participants'] = $nb['nb'];

		maj_donneesPlayer($login, $id);
	}

	function cup_mlSync ($aseco) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "RACE";
	}

	function cup_mlNewChallenge ($aseco, $challenge) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "RACE";
	}

	function cup_mlRestartChallenge2 ($aseco, $challenge) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "RACE";
	}

	function cup_mlEndRace1 ($aseco, $race) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "SCORE";
	}

	function cup_mlnewtrack ($aseco, $challenge) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "RACE";
		show_ml_score($aseco);
		show_ml_finish($aseco);
	}

	function cup_mlendRound ($aseco) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "SCORE";
		show_ml_score($aseco);
		show_ml_finish($aseco);
	}

	function cup_mlPlayerFinish($aseco, $finish) {
		global $cup_config, $color1, $color2;
		$cup_config['gamestate'] = "RACE";
		//show_all_ml($aseco);

		$login = $finish->player->login;
		$id = $finish->player->id;

		maj_donneesPlayer($login, $id);
	}

	function cup_mlPlayerConnect($aseco, $player) {
		show_ml_score($aseco);
		show_ml_finish($aseco);
	}


	function chat_cupladder($aseco, $command) {
		global $cup_config, $color1, $color2;

		$player = $command['author'];
		$login = $player->login;
		$arglist = explode(' ', $command['params'], 2);
		if (!isset($arglist[1]))
			$arglist[1] = '';
		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $command['params']));
		if (!isset($command['params'][1]))
			$command['params'][1] = '';

		if ($command['params'][0] == 'help') {
			$header = 'Help for '.$cup_config['CUP']['INFOS'][0]['NOM'][0];
			$help = array();
			$help[] = array($color2.'/cupladder all');
			$help[] = array($color1.'See ranking GENERAL');
			$help[] = array($color2.'/cupladder coast');
			$help[] = array($color1.'See ranking COAST');
			$help[] = array($color2.'/cupladder bay');
			$help[] = array($color1.'See ranking BAY');
			$help[] = array($color2.'/cupladder island');
			$help[] = array($color1.'See ranking ISLAND');
			$help[] = array($color2.'/cupladder desert');
			$help[] = array($color1.'See ranking DESERT');
			$help[] = array($color2.'/cupladder rally');
			$help[] = array($color1.'See ranking RALLY');
			$help[] = array($color2.'/cupladder snow');
			$help[] = array($color1.'See ranking SNOW');
			$help[] = array($color2.'/cupladder stadium');
			$help[] = array($color1.'See ranking STADIUM');

			// display ManiaLink message
			display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.0, 1.0), 'OK');
		}

		if ($command['params'][0] == "all") {
			show_ml_windowsScore($aseco, $login);
		}

		if ($command['params'][0] == "coast") {
			show_ml_windowsEnviros($aseco, $login, "Coast");
		}

		if ($command['params'][0] == "bay") {
			show_ml_windowsEnviros($aseco, $login, "Bay");
		}

		if ($command['params'][0] == "island") {
			show_ml_windowsEnviros($aseco, $login, "Island");
		}

		if ($command['params'][0] == "desert") {
			show_ml_windowsEnviros($aseco, $login, "Speed");
		}

		if ($command['params'][0] == "rally") {
			show_ml_windowsEnviros($aseco, $login, "Rally");
		}

		if ($command['params'][0] == "snow") {
			show_ml_windowsEnviros($aseco, $login, "Alpine");
		}

		if ($command['params'][0] == "stadium") {
			show_ml_windowsEnviros($aseco, $login, "Stadium");
		}
	}

	function chat_cup($aseco, $command) {

		global $cup_config, $color1, $color2;

		$player = $command['author'];
		$login = $player->login;
		$arglist = explode(' ', $command['params'], 2);
		if (!isset($arglist[1]))
			$arglist[1] = '';
		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $command['params']));
		if (!isset($command['params'][1]))
			$command['params'][1] = '';

		if ($command['params'][0] == 'help') {
			if ($aseco->isMasterAdmin($player) || $aseco->isAdmin($player)) {
				$header = 'Help for '.$cup_config['CUP']['INFOS'][0]['NOM'][0];
				$help = array();
				$help[] = array($color2.'/cup delplayer {#black}$ilogin');
				$help[] = array($color1.'Remove a player from the cup');
				$help[] = array($color2.'/cup idealtime');
				$help[] = array($color1.'See the ideal time');
				$help[] = array($color2.'/cup start');
				$help[] = array($color1.'Start the CUP');
				$help[] = array($color2.'/cup stop');
				$help[] = array($color1.'Stop the CUP');

				// display ManiaLink message
				display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.0, 1.0), 'OK');
			}
		}

		if ($command['params'][0] == 'idealtime') {
			$tabPlayers = array();
			$score = 0;
			$sql = "SELECT * FROM `records` AS r ORDER BY Challengeid ASC, Score ASC";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_array($result)) {
				if (!isset($tabPlayers[$row['ChallengeId']])) {
					$tabPlayers[$row['ChallengeId']] = $row['Score'];
					$score += $tabPlayers[$row['ChallengeId']];
				}
			}
			$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s'.$color1.'The ideal time is '.$color2.formatTime($score);
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}

		if ($command['params'][0] == 'delplayer') {

			if ($aseco->isMasterAdmin($player) || $aseco->isAdmin($player)) {
				if ($command['params'][1] != '') {
					$del_login = $command['params'][1];

					$result = mysql_query("SELECT Id, Login, NickName FROM players WHERE Login = '".$del_login."'");
					$row = mysql_fetch_array($result);
					$id = $row['Id'];
					$target = $row['Login'];
					$nickname = $row['NickName'];

					mysql_query("DELETE FROM cup_players WHERE Id_player = ".$id."");
					mysql_query("DELETE FROM records WHERE PlayerId = ".$id."");

					$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s'.$color1.'The player '.$nickname.' $z$s'.$color1.'was removed from the cup';
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

					show_ml_score($aseco);
					show_ml_finish($aseco);

					$command = array();
					$command['author'] = $player;
					$command['params'] = 'ban ' . $target;
					chat_admin($aseco, $command);
				}
			} else {
				$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s'.$color2.'This commands is only for admins !';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}

		if ($command['params'][0] == 'start') {
			if ($aseco->isMasterAdmin($player) || $aseco->isAdmin($player)) {
				$cup_config['cup']['states'] = 'start';
				foreach ($aseco->server->players->player_list as $pl) {
					$aseco->client->query('ForceSpectator', $pl->login, 2);
					$aseco->client->query('ForceSpectator', $pl->login, 0);
				}
				$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s$o'.$color2.'Start of the CUP !';
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			} else {
				$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s'.$color2.'This commands is only for admins !';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}

		if ($command['params'][0] == 'stop') {
			if ($aseco->isMasterAdmin($player) || $aseco->isAdmin($player)) {
				$cup_config['cup']['states'] = 'stop';
				foreach ($aseco->server->players->player_list as $pl) {
					$aseco->client->query('ForceSpectator', $pl->login, 1);
				}
				$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s$o'.$color2.'End of the CUP !';
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			} else {
				$message = '$fff['. $cup_config['CUP']['INFOS'][0]['NOM'][0] .'$z$fff] $s'.$color2.'This commands is only for admins !';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
	}

	function cup_onPlayerManialinkPageAnswer($aseco, $answer) {

	// If id = 0, bail out immediately
		if ($answer[2] == 0) {
			return;
		}

		$player = $aseco->server->players->player_list[$answer[1]];

		if ($answer[2] == '20015') {

			// Show the Finish Window
			show_ml_windowsFinish($aseco, $player->login, 0);
		}
		else if (($answer[2] <= -(int)'200100') && ($answer[2] >= -(int)'200149')) {

			// Get the wished Page
			$page = intval(str_replace(200, '', abs($answer[2])) - 100);
			show_ml_windowsFinish($aseco, $player->login, $page);

		}
		else if (($answer[2] >= (int)'200100') && ($answer[2] <= (int)'200149')) {

			// Get the wished Page
			$page = intval(str_replace(200, '', $answer[2]) - 100);
			show_ml_windowsFinish($aseco, $player->login, $page);
		}
		else if ($answer[2] == '30015') {

			// Show the Finish Window
			show_ml_windowsScore($aseco, $player->login, 0);
		}
		else if (($answer[2] <= -(int)'300100') && ($answer[2] >= -(int)'300149')) {

			// Get the wished Page
			$page = intval(str_replace(300, '', abs($answer[2])) - 100);
			show_ml_windowsScore($aseco, $player->login, $page);

		}
		else if (($answer[2] >= (int)'300100') && ($answer[2] <= (int)'300149')) {

			// Get the wished Page
			$page = intval(str_replace(300, '', $answer[2]) - 100);
			show_ml_windowsScore($aseco, $player->login, $page);
		}
		else if ($answer[2] == '20000') {

			hide_ml_windows($aseco, $player->login);
		}
	}

	function show_ml_windowsFinish($aseco, $login, $page) {

		global $cup_config, $templates, $color1, $color2;

		// Get the total of records
		$result = mysql_query('SELECT COUNT(*) AS nb FROM cup_players');
		$row = mysql_fetch_array($result);
		$totalrecs = $row['nb'];

		// Determind the maxpages
		$maxpages = ceil($totalrecs / 100);
		if (!$maxpages) $maxpages = 1;

		// Frame for Previous-/Next-Buttons
		$buttons = '<frame posn="52.2 -53.2 0.04">';

		// Previous button
		if ($page > 0) {

			// First
			$buttons .= '<quad posn="6.6 0 0.01" sizen="3.2 3.2" action="-200100" style="Icons64x64_1" substyle="ArrowFirst"/>';

			// Previous (-5)
			$buttons .= '<quad posn="9.9 0 0.01" sizen="3.2 3.2" action="-200'.((($page + 94) < 100) ? 100 : ($page + 94)) .'" style="Icons64x64_1" substyle="ArrowFastPrev"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.2 0 0.01" sizen="3.2 3.2" action="-200'.($page + 99) .'" style="Icons64x64_1" substyle="ArrowPrev"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="6.6 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="6.6 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Previous (-5)
			$buttons .= '<quad posn="9.9 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="9.9 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.2 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="13.2 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
		}

		// Next button (display only if more pages to display)
		if (($page < 50) && ($totalrecs > 100) && (($page + 1) < $maxpages)) {
			// Next (+1)
			$buttons .= '<quad posn="16.5 0 0.01" sizen="3.2 3.2" action="200'.($page + 101) .'" style="Icons64x64_1" substyle="ArrowNext"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.8 0 0.01" sizen="3.2 3.2" action="200'.((($page + 106) > ($maxpages + 99)) ? ($maxpages + 99) : ($page + 106)) .'" style="Icons64x64_1" substyle="ArrowFastNext"/>';

			// Last
			$buttons .= '<quad posn="23.1 0 0.01" sizen="3.2 3.2" action="200'.($maxpages + 99) .'" style="Icons64x64_1" substyle="ArrowLast"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.5 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="16.5 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.8 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="19.8 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Last
			$buttons .= '<quad posn="23.1 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="23.1 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
		}
		$buttons .= '</frame>';

		$title = '$s'.$cup_config['CUP']['INFOS'][0]['NOM'][0].' '.$color2.'- '.$color1.'Finish   |   Page '.$color2. ($page+1) .'/'. $maxpages .'   '.$color1.'|   '.$color2. $totalrecs . (($totalrecs == 1) ? ' '.$color1.'Participant' : ' '.$color1.'Participants');

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons64x64_1',
				'Finish',
				$title,
				$buttons
			),
			$templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="2.5 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

		$rank = 1;
		$line = 0;
		$offset = 0;

		$limitmin = ($page > 0) ? ($page * 100) : ($page * 100);
		$limitmax = 100;

		$sql = 'SELECT * FROM cup_players AS c, players AS p WHERE c.Id_player = p.Id ORDER BY c.nb_finish DESC, c.total_score ASC LIMIT '.$limitmin.', '.$limitmax.'';
		$result = mysql_query($sql);

		while ($row = mysql_fetch_array($result)) {
			if ($row['Login'] == $login) {
				$textcolor = str_replace('$', '', $color2);
				$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="BgsPlayerCard" substyle="BgCardSystem"/>';
			} else {
				$textcolor = str_replace('$', '', $color1);
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="$s'. ($rank + 100*$page) .'."/>';
			$xml .= '<label posn="'. (4.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="center" scale="0.9" textcolor="'.$textcolor.'" text="$s'. $row['nb_finish'] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="$s'. $row['NickName'] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		$xml .= '</frame>';
		$xml .= $templates['WINDOW']['FOOTER'];

		$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
	}

	function show_ml_windowsScore($aseco, $login) {

		global $cup_config, $templates, $color1, $color2;

		// Get the total of records
		$result = mysql_query('SELECT COUNT(*) AS nb FROM cup_players WHERE nb_finish = '.$cup_config['CUP']['INFOS'][0]['TOTAL_MAP'][0]);
		$row = mysql_fetch_array($result);
		$totalrecs = $row['nb'];

		// Determind the maxpages
		$maxpages = ceil($totalrecs / 100);
		if (!$maxpages) $maxpages = 1;

		// Frame for Previous-/Next-Buttons
		$buttons = '<frame posn="52.2 -53.2 0.04">';

		// Previous button
		if ($page > 0) {

			// First
			$buttons .= '<quad posn="6.6 0 0.01" sizen="3.2 3.2" action="-300100" style="Icons64x64_1" substyle="ArrowFirst"/>';

			// Previous (-5)
			$buttons .= '<quad posn="9.9 0 0.01" sizen="3.2 3.2" action="-300'.((($page + 94) < 100) ? 100 : ($page + 94)) .'" style="Icons64x64_1" substyle="ArrowFastPrev"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.2 0 0.01" sizen="3.2 3.2" action="-300'.($page + 99) .'" style="Icons64x64_1" substyle="ArrowPrev"/>';

		}
		else {
			// First
			$buttons .= '<quad posn="6.6 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="6.6 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Previous (-5)
			$buttons .= '<quad posn="9.9 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="9.9 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.2 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="13.2 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
		}

		// Next button (display only if more pages to display)
		if (($page < 50) && ($totalrecs > 100) && (($page + 1) < $maxpages)) {
			// Next (+1)
			$buttons .= '<quad posn="16.5 0 0.01" sizen="3.2 3.2" action="300'.($page + 101) .'" style="Icons64x64_1" substyle="ArrowNext"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.8 0 0.01" sizen="3.2 3.2" action="300'.((($page + 106) > ($maxpages + 99)) ? ($maxpages + 99) : ($page + 106)) .'" style="Icons64x64_1" substyle="ArrowFastNext"/>';

			// Last
			$buttons .= '<quad posn="23.1 0 0.01" sizen="3.2 3.2" action="300'.($maxpages + 99) .'" style="Icons64x64_1" substyle="ArrowLast"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.5 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="16.5 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.8 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="19.8 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';

			// Last
			$buttons .= '<quad posn="23.1 0 0.01" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
			$buttons .= '<quad posn="23.1 0 0.02" sizen="3.2 3.2" style="Icons64x64_1" substyle="StarGold"/>';
		}
		$buttons .= '</frame>';

		$title = '$s'.$cup_config['CUP']['INFOS'][0]['NOM'][0].' '.$color2.'- '.$color1.'Total time   |   Page '.$color2. ($page+1) .'/'. $maxpages .'   '.$color1.'|   '.$color2. $totalrecs . (($totalrecs == 1) ? ' '.$color1.'Score' : ' '.$color1.'Scores');

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons64x64_1',
				'Finish',
				$title,
				$buttons
			),
			$templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="2.5 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';


		$rank = 1;
		$line = 0;
		$offset = 0;

		$limitmin = ($page > 0) ? ($page * 100) + 1 : ($page * 100);
		$limitmax = 100;

		$sql = 'SELECT * FROM cup_players AS c, players AS p WHERE p.Id = c.Id_player AND c.nb_finish = '.$cup_config['CUP']['INFOS'][0]['TOTAL_MAP'][0].' ORDER BY c.total_score ASC LIMIT '.$limitmin.', '.$limitmax.'';
		$result = mysql_query($sql);

		while ($row = mysql_fetch_array($result)) {
			if ($row['Login'] == $login) {
				$textcolor = str_replace('$', '', $color2);
				$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="BgsPlayerCard" substyle="BgCardSystem"/>';
			} else {
				$textcolor = str_replace('$', '', $color1);
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="$s'. ($rank + 100*$page) .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="right" scale="0.9" textcolor="'.$textcolor.'" text="$s'. formatTime($row['total_score']) .'"/>';
			//$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="center" scale="0.9" textcolor="'.$textcolor.'" text="'. $row['nb_finish'] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="$s'. $row['NickName'] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		unset($item);
		$xml .= '</frame>';
		$xml .= $templates['WINDOW']['FOOTER'];

		$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
	}

	function show_ml_windowsEnviros($aseco, $login, $environment) {

		global $cup_config, $templates, $color1, $color2;

		$sql = "SELECT * FROM cup_players WHERE nb_finish = ".$cup_config['CUP']['INFOS'][0]['TOTAL_MAP'][0]."";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$score = 0;
			$sql = "SELECT p.Login, r.Score FROM cup_players AS c, players AS p, challenges AS ch, records AS r WHERE c.Id_player = p.Id AND r.PlayerId = p.Id AND ch.Id = r.ChallengeId AND ch.Environment = '".$environment."' AND p.Id = '".$row['Id_player']."'";

			$result2 = mysql_query($sql);
			while ($row2 = mysql_fetch_array($result2)) {
				$ladder[$row2['Login']] += $row2['Score'];
			}
		}

		array_multisort($ladder, SORT_NUMERIC);

		// Get the total of records
		$totalrecs = count($ladder);

		// Determind the maxpages
		$maxpages = ceil($totalrecs / 100);
		if (!$maxpages) $maxpages = 1;

		$title = '$s'.$cup_config['CUP']['INFOS'][0]['NOM'][0].' '.$color2.'- '.$color1.'Total time '.$color2.$environment.'   '.$color1.'|   Page '.$color2. ($page+1) .'/'. $maxpages .'   '.$color1.'|   '.$color2. $totalrecs . (($totalrecs == 1) ? ' '.$color1.'Score' : ' '.$color1.'Scores');

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons64x64_1',
				'Finish',
				$title,
				''
			),
			$templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="2.5 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

		$rank = 1;
		$line = 0;
		$offset = 0;

		$limitmin = ($page > 0) ? ($page * 100) : ($page * 100);
		$limitmax = 100;

		$sql = 'SELECT * FROM cup_players AS c, players AS p WHERE c.Id_player = p.Id ORDER BY c.nb_finish DESC, c.total_score ASC LIMIT '.$limitmin.', '.$limitmax.'';
		$result = mysql_query($sql);

		foreach ($ladder as $plogin => $pscore) {
			$sql = "SELECT NickName FROM players WHERE Login = '".$plogin."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			if ($plogin == $login) {
				$textcolor = str_replace('$', '', $color2);
				$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="BgsPlayerCard" substyle="BgCardSystem"/>';
			} else {
				$textcolor = str_replace('$', '', $color1);
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="$s'. $rank .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="right" scale="0.9" textcolor="'.$textcolor.'" text="$s'. formatTime($pscore) .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="$s'. $row['NickName'] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		unset($item);
		$xml .= '</frame>';
		$xml .= $templates['WINDOW']['FOOTER'];

		$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
	}

	function hide_ml_windows($aseco, $login) {
		$xml = '
			<manialinks>
				<manialink id="20000">
				</manialink>
			</manialinks>';
		$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
	}

	function show_ml_score($aseco) {
		global $cup_config, $color1, $color2;

		$text = str_replace(array('{rose}', '{bleu}', '{gras}'), array($color2, $color1, '$o'), $cup_config['CUP']['MANIALINK'][0]['SCORE'][0]['TEXT_SCORE'][0]);
		$header = '
		<manialinks>
			<manialink id="10001">
				<frame posn="'.$cup_config['CUP']['MANIALINK'][0]['SCORE'][0]['POSX'][0].' '.$cup_config['CUP']['MANIALINK'][0]['SCORE'][0]['POSY'][0].' 0">
					<quad posn="0 0 0.001" sizen="15.5 21.7" action="30015" style="BgsPlayerCard" substyle="BgPlayerCardBig" />
					<quad posn="0.4 -0.40 10" sizen="14.7 2" style="BgsPlayerCard" substyle="BgRacePlayerName" />
					<quad posn="0.4 -2.40 11" sizen="14.7 9.5" style="BgsPlayerCard" substyle="BgCard" />
					<quad posn="0.4 -0.36 0.002" sizen="14.7 2" style="BgsPlayerCard" substyle="ProgressBar" />
					<quad posn="0.7 -0.15 20" sizen="2.5 2.5" style="Icons64x64_1" substyle="OfficialRace" />
					<label posn="3.2 -0.55 20" sizen="10.2 0" textsize="1" text="$s'. $text .'"/>
					<format textsize="1" textcolor="FFFF" />';

		$footer .='</frame>
			</manialink>
		</manialinks>';

		$offset = 3;
		$offset2 = 2.8;
		$LineHeight = 1.8;
		$trouve = false;

		maj_donneesAllPlayer();

		foreach ($aseco->server->players->player_list as $pl) {

			$login = $pl->login;
			$id = $pl->id;
			$nickname = $pl->nickname;

			maj_donneesPlayer($login, $id);

			$trouve = false;
			$line = 0;
			$index = 0;
			$index2 = 0;

			$xml = $header;

			$sql = 'SELECT * FROM cup_players WHERE nb_finish = '.$cup_config['CUP']['INFOS'][0]['TOTAL_MAP'][0].' ORDER BY total_score ASC';
			$result = mysql_query($sql);

			while ($row = mysql_fetch_array($result)) {
				$color = $color2;
				$sql = "SELECT NickName, Login FROM players WHERE Id = ". $row['Id_player'] ."";
				$resultat = mysql_query($sql);
				$res = mysql_fetch_array($resultat);
				$nickname2 = $res['NickName'];
				$login2 = $res['Login'];

				if ($login == $login2) {
					$color = $color1;
					$trouve = true;
				}

				if (($index < 9) || ($index == 8 && $trouve)) {
					if (($line + 1) == 1)
						$xml .= '<quad posn="-1.3 -'. ($LineHeight * $line + $offset2) .' 14" sizen="1.7 1.7" style="Icons64x64_1" substyle="First"/>';
					if (($line + 1) == 2)
						$xml .= '<quad posn="-1.3 -'. ($LineHeight * $line + $offset2) .' 14" sizen="1.7 1.7" style="Icons64x64_1" substyle="Second"/>';
					if (($line + 1) == 3)
						$xml .= '<quad posn="-1.3 -'. ($LineHeight * $line + $offset2) .' 14" sizen="1.7 1.7" style="Icons64x64_1" substyle="Third"/>';

					$xml .= '<label posn="2.1 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" text="$s'. ($line + 1) .'."/>';
					$xml .= '<label posn="5.7 -'. ($LineHeight * $line + $offset) .' 13" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="DDDF" text="$s'.$color . formatTime($row['total_score']).'"/>';
					$xml .= '<label posn="5.9 -'. ($LineHeight * $line + $offset) .' 13" sizen="10.2 1.3" scale="0.9" text="$s'.$color1. $aseco->formatColors($nickname2) .'"/>';
					$line ++;
					$index = $line;
					$index2++;
				} else {
					if (!$trouve) {
						$index++;
					} else {
						$xml .= '<label posn="2.1 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" text="$s'. ($index + 1) .'."/>';
						$xml .= '<label posn="5.7 -'. ($LineHeight * $line + $offset) .' 13" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="DDDF" text="$s'.$color . formatTime($row['total_score']).'"/>';
						$xml .= '<label posn="5.9 -'. ($LineHeight * $line + $offset) .' 13" sizen="10.2 1.3" scale="0.9" text="$s'.$color1. $aseco->formatColors($nickname2) .'"/>';
						break;
					}
				}
			}

			if (!$trouve && $index2 == 9) {
				$xml .= '<label posn="2.1 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" text="$s'. ($index + 1) .'."/>';
				$xml .= '<label posn="5.7 -'. ($LineHeight * $line + $offset) .' 13" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="DDDF" text="$s'.$color1.'--:--.--"/>';
				$xml .= '<label posn="5.9 -'. ($LineHeight * $line + $offset) .' 13" sizen="10.2 1.3" scale="0.9" text="$s'.$color1. $aseco->formatColors($nickname) .'"/>';
			}

			$xml .= $footer;
			$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
		}
	}

	function show_ml_finish($aseco) {
		global $cup_config, $color1, $color2;

		$text = str_replace(array('{rose}', '{bleu}', '{gras}'), array($color2, $color1, '$o'), $cup_config['CUP']['MANIALINK'][0]['FINISH'][0]['TEXT_FINISH'][0]);
		$header = '
		<manialinks>
			<manialink id="10002">
				<frame posn="'.$cup_config['CUP']['MANIALINK'][0]['FINISH'][0]['POSX'][0].' '.$cup_config['CUP']['MANIALINK'][0]['FINISH'][0]['POSY'][0].' 0">
					<quad posn="0 0 0.001" sizen="15.5 21.7" action="20015" style="BgsPlayerCard" substyle="BgPlayerCardBig" />
					<quad posn="0.4 -0.40 10" sizen="14.7 2" style="BgsPlayerCard" substyle="BgRacePlayerName" />
					<quad posn="0.4 -0.36 0.002" sizen="14.7 2" style="BgsPlayerCard" substyle="ProgressBar" />
					<quad posn="0.7 -0.15 20" sizen="2.5 2.5" style="Icons64x64_1" substyle="Finish" />
					<label posn="3.2 -0.55 20" sizen="10.2 0" textsize="1" text="$s'. $text .'"/>
					<format textsize="1" textcolor="FFFF" />';

		$footer .='</frame>
			</manialink>
		</manialinks>';

		$offset = 3;
		$LineHeight = 1.8;

		maj_donneesAllPlayer();

		foreach ($aseco->server->players->player_list as $pl) {

			$login = $pl->login;
			$id = $pl->id;

			maj_donneesPlayer($login, $id);

			$trouve = false;
			$line = 0;
			$index = 0;

			$xml = $header;

			$sql = 'SELECT * FROM cup_players ORDER BY nb_finish DESC, total_score ASC';
			$result = mysql_query($sql);

			while ($row = mysql_fetch_array($result)) {
				$color = $color2;
				$sql = "SELECT NickName, Login FROM players WHERE Id = ". $row['Id_player'] ."";
				$resultat = mysql_query($sql);
				$res = mysql_fetch_array($resultat);
				$nickname2 = $res['NickName'];
				$login2 = $res['Login'];

				if ($login == $login2) {
					$color = $color1;
					$trouve = true;
				}

				if (($index < 9) || ($index == 8 && $trouve)) {
				$xml .= '<label posn="2.1 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" text="$s'. ($line + 1) .'."/>';
				$xml .= '<label posn="4.2 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" textcolor="DDDF" text="$s'.$color . $row['nb_finish'].'"/>';
				$xml .= '<label posn="5.9 -'. ($LineHeight * $line + $offset) .' 13" sizen="10.2 1.3" scale="0.9" text="$s'.$color1. $aseco->formatColors($nickname2) .'"/>';
				$line ++;
				$index = $line;
				} else {
					if (!$trouve) {
						$index++;
					} else {
						$xml .= '<label posn="2.1 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" text="$s'. ($index + 1) .'."/>';
						$xml .= '<label posn="4.2 -'. ($LineHeight * $line + $offset) .' 13" sizen="1.7 1.7" halign="right" scale="0.9" textcolor="DDDF" text="$s'.$color . $row['nb_finish'].'"/>';
						$xml .= '<label posn="5.9 -'. ($LineHeight * $line + $offset) .' 13" sizen="10.2 1.3" scale="0.9" text="$s'.$color1. $aseco->formatColors($nickname2) .'"/>';
						break;
					}
				}
			}
			$xml .= $footer;
			$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
		}
	}

	function hide_ml($aseco, $id) {
        $xml = '
		<manialinks>
			<manialink id="'.$id.'">
			</manialink>
		</manialinks>';
        $aseco->addCall('SendDisplayManialinkPage', array($xml, 0, false));
    }

	function maj_donneesPlayer($login, $id) {

		$sql = 'SELECT p.Login, p.Nickname, count(p.Id) AS Count
				FROM players AS p, records AS r, cup_players AS cp
				WHERE r.PlayerId = p.Id
				AND cp.Id_player = p.Id
				AND p.Login = "'.$login.'"
				GROUP BY p.Id
				ORDER BY count DESC';

		if ($result = mysql_query($sql)) {
			$row = mysql_fetch_array($result);
			if (isset($row['Count'])) {
				$sql = 'UPDATE cup_players SET nb_finish = '.$row['Count'].' WHERE Id_player = '.$id.'';
			}
			else {
				$sql = 'UPDATE cup_players SET nb_finish = 0 WHERE Id_player = '.$id.'';
			}
			mysql_query($sql);
		}
		$sql = 'SELECT r.Score
				FROM players AS p, records AS r, cup_players AS cp
				WHERE r.PlayerId = p.Id
				AND cp.Id_player = p.Id
				AND p.Login = "'.$login.'"';

		$result = mysql_query($sql);
		$total_score = 0;
		while ($tab = mysql_fetch_array($result)) {
			$total_score += $tab['Score'];
		}
		$sql = 'UPDATE cup_players SET total_score = '.$total_score.' WHERE Id_player = '.$id.'';
		mysql_query($sql);
	}

	function maj_donneesAllPlayer() {

		$sql = 'SELECT cp.Id_player, p.Login FROM cup_players AS cp, players AS p WHERE cp.Id_player = p.Id ORDER BY Id_player ASC';
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			$id = $row['Id_player'];
			$login = $row['Login'];
			maj_donneesPlayer($login, $id);
		}
	}

 ?>
