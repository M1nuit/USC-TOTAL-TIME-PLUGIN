<?php
require_once('functions.php');
require_once('./classes/tmnick.class.php');

mysql_connect("localhost", "tmf", "#Minuit971");
mysql_select_db("xaseco");
?>

<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<div align="center" style="width:900px;margin-left: auto;margin-right: auto;padding-left: 15px;padding-right: 15px;">
		<div class="header2"></div>
		<h2>Choose a map</h2>
		<div>
			<form method="POST">
				<p>
					<label>
					<select name="mapid" id="mapid">
						<option value="0" selected>--------------------</option>
						<?php
							$sql = "SELECT * FROM challenges ORDER BY Name ASC";
							$result = mysql_query($sql);
							while ($row = mysql_fetch_array($result)) {
								echo '<option value="'.$row['Id'].'">'.TmNick::toHtml(htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999').' ('.$row['Environment'].')</option>';
							}
						?>
					</select>
					</label>
				</p>
				<p>
					<input type="submit" class="btn" name="submit" value="Submit"/>
				</p>
			</form>
		</div>
		
		<br />
		
		<?php 
			if (isset($_POST['submit']) && $_POST['mapid'] != 0) {
				
				$sql = "SELECT Name FROM challenges WHERE Id = ".(int)$_POST['mapid'];
				$result = mysql_query($sql);
				$row = mysql_fetch_array($result);
		?>		
			<h2><?php echo TmNick::toHtml(htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'), 14, true, true, '#000'); ?></h2>
			<div class="table">
				<table>
					<tr>
						<td>Rank</td>
						<td>Player</td>
						<td>Time</td>
						<td>Date</td>
					</tr>
					<?php

					$html = '';
					$sql = "SELECT r.*, p.NickName, p.Login FROM records AS r, players AS p WHERE r.PlayerId = p.Id AND r.ChallengeId = ".(int)$_POST['mapid']." ORDER BY r.Score ASC";
					$result = mysql_query($sql);
					$i = 1;
					while ($row = mysql_fetch_array($result)) {
						
						$html .= "<tr><td width='20'>".$i."</td><td width='300'>".TmNick::toHtml(htmlspecialchars($row['NickName'], ENT_QUOTES, 'UTF-8'), 14, true, false, '#999')." (".$row['Login'].")</td><td width='150'><b>".formatTime($row['Score'])."</b></td><td width='150'>".$row['Date']."</td></tr>" ;
						$i++;
					}
					echo $html;	
					
					?>
				</table>
			</div>
		<?php
			}
		?>
	</div>
</body>
</html>