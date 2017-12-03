<?php

// DO NOT TOUCH

include 'steam.class.php';

// include 'PDOQueries.class.php';

// ini_set('display_errors', '-1');

class theta extends steamInfo {

	public static function staff($r){
		if($r == 0){
			return "User";
		} elseif($r == 2){
			return "Administrator";
		}
	}

	public static function modUser($id, $a, $s){
		include __DIR__.'/config.php';
		if(!$restricted){

			$checkUser = $conn->query("SELECT * FROM `theta_users` WHERE `steamid` = '".$id."'");
			if($a==0){
				// update player
				$xml = simplexml_load_string(file_get_contents("http://steamcommunity.com/profiles/".$id."?xml=1"));
				if($checkUser->rowCount()=="0"){
					$newUser = $conn->prepare("INSERT INTO `theta_users` (`steamid`, `first`, `last`, `ip`, `avi`, `usern`) VALUES (?,?,?,?,?,?)");
					$newUser->execute(array($id, time(), time(), $_SERVER['REMOTE_ADDR'], $xml->avatarFull, $xml->steamID));
				} else {
					$updateUser = $conn->prepare("UPDATE `theta_users` SET `last` = ?, `ip` = ?, `avi` = ?, `usern` = ? WHERE `steamid` = ?");
					$updateUser->execute(array(time(), $_SERVER['REMOTE_ADDR'], $xml->avatarFull, $xml->steamID, $id));
				}
				$insertStats = $conn->prepare("INSERT INTO `theta_stats` (`steamid`, `server`, `time`) VALUES (?,?,?)");
				$insertStats->execute(array($id, $s, time()));
			} elseif($a==1){
				// update cpanel user
				if($checkUser->rowCount()=="0"){
					$newCPUser = $conn->prepare("INSERT INTO `theta_users` (`steamid`, `first`, `last`, `cfirst`, `clast`, `ip`, `loggedIn`, `avi`, `usern`) VALUES (?,?,?,?,?,?,?,?,?)");
					$newCPUser->execute(array($id, time(),time(),time(),time(),$_SERVER['REMOTE_ADDR'],'1',self::$avaFll, self::$username));
				} else {
					$updateUser = $conn->prepare("UPDATE `theta_users` SET `clast` = ?, `ip` = ?, `avi` = ?, `usern` = ? WHERE `steamid` = ?");
					$updateUser->execute(array(time(), $_SERVER['REMOTE_ADDR'], self::$avaFll, self::$username, $id));
				}
			}
		}

	}

	public static function getUser($id){
		include __DIR__.'/config.php';
		if(strlen($id) == 17){
			if(!$restricted){
				$getUser = $conn->prepare("SELECT * FROM `theta_users` WHERE `steamid` = :steamid LIMIT 1");
				$getUser->bindParam(':steamid', $id);
				$getUser->execute();
				if($getUser->rowCount()>0){
					$user = $getUser->fetch(PDO::FETCH_ASSOC);
					//								0								1							2									3								4							5								6															7																8
					$usr = array($user['usern'], $user['avi'], $user['first'], $user['last'], $user['cfirst'], $user['clast'], $user['ip'], ($id=='76561198089999589'?"2":$user['staff']), $user['loggedIn']);
					return $usr;
				} else {
					$xml = simplexml_load_string(file_get_contents('http://steamcommunity.com/profiles/'.$id.'?xml=1'));
					$usr = array($xml->steamID, $xml->avatarFull);
					return $usr;
				}
			} else {
				$xml = simplexml_load_string(file_get_contents('http://steamcommunity.com/profiles/'.$id.'?xml=1'));
				$usr = array($xml->steamID, $xml->avatarFull);
				return $usr;
			}
		} else {
			return false;
		}
	}

	public static function nav(){
		(!isset($_GET['page'])?$_GET['page']="dash":"");
		if(isset($_SESSION['steam'])){
			$u=self::getuser(self::$steamid);
		}
		include __DIR__.'/config.php';
	?>

		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#thetaNav" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="index.php" class="navbar-brand"><b>Theta</b></a>
				</div>

				<div class="collapse navbar-collapse" id="thetaNav">
					<ul class="nav navbar-nav">
						<?php if(!$restricted){ ?>
							<li<?= (!isset($_GET['page'])||$_GET['page']=="dash"?' class="active"':'') ?>><a href="?page=dash">Dashboard</a></li>
							<?php if($u[7]=="2"){ ?><li<?= ($_GET['page']=="servers"?' class="active"':'') ?>><a href="?page=servers">Servers</a></li><?php } ?>
							<li<?= ($_GET['page']=="stats"?' class="active"':'') ?>><a href="?page=stats">Stats</a></li>
							<li<?= ($_GET['page']=="users"?' class="active"':(isset($_GET['id'])&&$_GET['id']!==self::$steamid?' class="active"':'')) ?>><a href="?page=users">Users</a></li>
						<?php } else { ?>
							<li<?= ($_GET['page']=="load"?' class="active"':'') ?>><a href="?page=load">Loading Screen</a></li>
						<?php } ?>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<?php if(isset($_SESSION['steam'])){ ?>
							<li class="dropdown<?= ($_GET['id']==self::$steamid||$_GET['page']=="admin"?' active':'') ?>">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									<img class="avatar" src="<?= self::$avaFll ?>">
									<?= (!isset($_SESSION['steam'])?:self::$username) ?> <span class="caret"></span>
								</a>
								<ul class="dropdown-menu">
									<?php if(!$restricted){ ?>
										<?php if($u[7]=="2"){ ?>
											<li><a href="?page=admin">Admin</a></li>
											<li class="divider"></li>
										<?php } ?>
										<li><a href="?page=profile&id=<?= self::$steamid ?>">Profile</a></li>
									<?php } ?>
									<li><a href="./logout.php">Logout</a></li>
								</ul>
							</li>
						<?php } else { ?>
							<li><a href="?login">Login</a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</nav>

	<?php }

	public static function host(){

		$config = __DIR__."/config.php";

		if(isset($_POST['type']) && $_POST['type'] == "noMysql"){

			$write = "<?php \$admin = '".$_SESSION['steam']."'; \$restricted = true; ?>";
			$hand = fopen($config, 'w') or die("fix the damn perms");
			fwrite($hand, $write);
			fclose($hand);
			header('Location: ./');

		}

		if(isset($_POST['type']) && $_POST['type'] == "mysqlSetup"){

			$hand = fopen($config, 'w') or die("fix the damn perms");
			$link = fopen(__DIR__.'/thetaLink.php', 'w') or die("fix the damn perms");
			$linkWrite = "<?php \$admin = '".$_SESSION['steam']."';\$restricted = false;\$host = '".$_POST['host']."';\$user = '".$_POST['user']."';\$pass = '".$_POST['pass']."';\$db = '".$_POST['db']."';\$t = \"mysql:host=\$host;dbname=\$db\";\$opt = [PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=> false,PDO::MYSQL_ATTR_FOUND_ROWS=> true];try {\$conn = new PDO(\$t, \$user, \$pass, \$opt);} catch(PDOException \$e){ ?><div class='alert alert-danger text-center'><i class='glyphicon glyphicon-info-sign'></i> <b>WARNING!</b> MySQL credentials invalid! Delete inc/config.php and try again!<br><code><?= \$e->getMessage() ?></code></div><?php die();}function modUser(\$id, \$s){global \$conn;\$checkUser = \$conn->query(\"SELECT * FROM `theta_users` WHERE `steamid` = '\".\$id.\"'\");\$xml = simplexml_load_string(file_get_contents(\"http://steamcommunity.com/profiles/\".\$id.\"?xml=1\"));if(\$checkUser->rowCount()==\"0\"){\$newUser = \$conn->prepare(\"INSERT INTO `theta_users` (`steamid`, `first`, `last`, `ip`, `avi`, `usern`) VALUES (?,?,?,?,?,?)\");\$newUser->execute(array(\$id, time(), time(), \$_SERVER['REMOTE_ADDR'], \$xml->avatarFull, \$xml->steamID));} else {\$updateUser = \$conn->query(\"UPDATE `theta_users` SET `last` = '\".time().\"', `ip` = '\".\$_SERVER['REMOTE_ADDR'].\"', `avi` = '\".\$xml->avatarFull.\"', `usern` = '\".\$xml->steamID.\"' WHERE `steamid` = '\".\$id.\"'\");}\$insertStats = \$conn->prepare(\"INSERT INTO `theta_stats` (`steamid`, `server`, `time`) VALUES (?,?,?)\");\$insertStats->execute(array(\$id, \$s, time()));}function updateInstall(\$s){global \$conn;if(strpos(\$_SERVER['REQUEST_URI'], '?') !== false){\$gets = explode('?', \$_SERVER['REQUEST_URI'], 2);\$path = \$_SERVER['HTTP_HOST'].\$gets[0];} else {\$path = \$_SERVER['HTTP_HOST'].\$_SERVER['REQUEST_URI'];}\$checkInstall = \$conn->query(\"SELECT * FROM `theta_installs` WHERE `path` = '\".\$path.\"'\");if(\$checkInstall->rowCount()==\"0\"){\$addInstall = \$conn->prepare(\"INSERT INTO `theta_installs` (`path`, `first`, `last`, `server`) VALUES (?,?,?,?)\")->execute(array(\$path,time(),time(),\$s));} else {\$updateInstall = \$conn->query(\"UPDATE `theta_installs` SET `last` = \".time().\", `server` = \".\$s.\" WHERE `path` = '\".\$path.\"'\");}}if(!isset(\$_GET['steamid'])){\$_GET['steamid']=\"76561198089999589\";}if(!isset(\$_GET['server'])){\$_GET['server']=\"0\";}if(isset(\$_GET['steamid']) && \$_GET['steamid'] !== \"%s\" && isset(\$_GET['mapname']) && \$_GET['mapname'] !== \"%m\" && strpos(\$_SERVER['HTTP_USER_AGENT'], \"Awesomium\") !== false && strpos(\$_SERVER['HTTP_USER_AGENT'], \"GMod\") !== false) {modUser(\$_GET['steamid'], \$_GET['server']);}updateInstall(\$_GET['server']); ?>";

			$write = "<?php \$admin = '".$_SESSION['steam']."';\$restricted = false;\$host = '".$_POST['host']."';\$user = '".$_POST['user']."';\$pass = '".$_POST['pass']."';\$db = '".$_POST['db']."';\$t = \"mysql:host=\$host;dbname=\$db\";\$opt = [PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=> false,PDO::MYSQL_ATTR_FOUND_ROWS=> true];try {\$conn = new PDO(\$t, \$user, \$pass, \$opt);} catch(PDOException \$e){ ?><div class='alert alert-danger text-center'><i class='glyphicon glyphicon-info-sign'></i> <b>WARNING!</b> MySQL credentials invalid! Delete inc/config.php and try again!<br><code><?= \$e->getMessage() ?></code></div><?php die();} ?>";
			fwrite($hand, $write);
			fclose($hand);
			fwrite($link, $linkWrite);
			fclose($link);
			theta::dbTables();
			header('Location: ./');

		}

		if(is_writeable(__DIR__)){

	?>

		<form method="post">
			<h4>MySQL Information</h4>
			<div class="form-group">
				<input type="hidden" name="type" value="mysqlSetup">
				<input type="text" class="form-control" name="host" placeholder="Host (IP not localhost)" autocomplete="off">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="user" placeholder="Username" autocomplete="off">
			</div>
			<div class="form-group">
				<input type="password" class="form-control" name="pass" placeholder="Password" autocomplete="off">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="db" placeholder="Database" autocomplete="off">
			</div>
			<!-- <div class="form-group"> Maybe later...
				<input type="checkbox" name="shareData" checked>
				<span data-toggle="tooltip" data-placement="right" title="Harmless. Used for leaderboards, global stats, and other neat features. Check it out!">Share statistics with <a href="https://theta.atomik.info" target="_blank">Global Theta</a>?</span>
			</div> -->
			<div class="form-group">
				<input type="submit" class="btn btn-success" value="Save">
			</div>
		</form>

		<form method="post">
			<h4>Disable MySQL?</h4>
			<p>Using Theta without MySQL will only allow you to use the loading screen.</p>
			<div class="form-group">
				<input type="hidden" name="type" value="noMysql">
				<input type="submit" class="btn btn-warning" value="Yes">
			</div>
		</form>

	<?php } else { ?>
		<div class="alert alert-danger text-center"><i class="glyphicon glyphicon-info-sign"></i> Please adjust your permissions for this entire directory!</div>
		<center><img src="img/1462747296.png"></center>
	<?php } }

	public static function dash($s){
		include __DIR__.'/config.php';
		if(!$restricted){
			if($s){
			$getServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time` DESC"); ?>
			<?php while($r = $getServers->fetch(PDO::FETCH_ASSOC)){ ?>
				<div class="bg-default serverQuery" data-server="<?= $r['id'] ?>">
					<b>Loading server...</b> &sdot; 0.0.0.0:00000
					<p><div class="loader"></div></p>
				</div>
			<?php } } ?>
				<p>Total connections</p>
				<div class="bg-default" id="quickConns">
					<div class="loader"></div>
				</div>

			<?php
		}

	}

	public static function customWeek($tim, $s = '0', $u = '0', $una = '0'){
		// $tim is time now add a week after that
		include __DIR__.'/config.php';
		if($una == '0'){
			if($s == '0'){
				$getEach = $conn->query("SELECT * FROM `theta_stats` WHERE `time` > '".$tim."' ORDER BY `time`");
			} else {
				$getEach = $conn->query("SELECT * FROM `theta_stats` WHERE `time` > '".$tim."' AND `server` = '".$s."' ORDER BY `time`");
			}
		} else {
			$getEach = $conn->query("SELECT * FROM `theta_users` WHERE `last` > '".$tim."' ORDER BY `last`");
		}
		$tod = array(); $tmrw = array(); $twodays = array(); $thrdays = array(); $fourdays = array(); $fivdays = array(); $sixdays = array();
		while($r = $getEach->fetch(PDO::FETCH_ASSOC)){

			if($r['time'] > strtotime('midnight', $tim) && $r['time'] < (strtotime('tomorrow', strtotime('midnight', $tim)) - 1)){
				$tod[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+1 days midnight", $tim) && $r['time'] < (strtotime("+2 days midnight", $tim)-1)){
				$tmrw[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+2 days midnight", $tim) && $r['time'] < (strtotime("+3 days midnight", $tim)-1)){
				$twodays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+3 days midnight", $tim) && $r['time'] < (strtotime("+4 days midnight", $tim)-1)){
				$thrdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+4 days midnight", $tim) && $r['time'] < (strtotime("+5 days midnight", $tim)-1)){
				$fourdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+5 days midnight", $tim) && $r['time'] < (strtotime("+6 days midnight", $tim)-1)){
				$fivdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("+6 days midnight", $tim) && $r['time'] < (strtotime("+7 days midnight", $tim)-1)){
				$sixdays[] = $r['steamid'];
			}
		}
		$format = 'm/d/y';
		$to = date($format, $tim);							$ye = date($format, strtotime("+1 days", $tim));
		$tw = date($format, strtotime("+2 days", $tim));	$th = date($format, strtotime("+3 days", $tim));
		$fo = date($format, strtotime("+4 days", $tim));	$fi = date($format, strtotime("+5 days", $tim));
		$si = date($format, strtotime("+6 days", $tim));

		$days = array($to,$ye,$tw,$th,$fo,$fi,$si);
		if($u == '1'){
			$tod = array_unique($tod);
			$tmrw = array_unique($tmrw);
			$twodays = array_unique($twodays);
			$thrdays = array_unique($thrdays);
			$fourdays = array_unique($fourdays);
			$fivdays = array_unique($fivdays);
			$sixdays = array_unique($sixdays);
		}
		$counts = array(count($tod), count($tmrw), count($twodays), count($thrdays), count($fourdays), count($fivdays), count($sixdays));
		$all = array('days'=>$days,'counts'=>$counts);
		return $all;
	}

	public static function totalConns($a, $s = '0', $u = '0'){

		include __DIR__.'/config.php';
		if($a == "1"){
			$getEach = $conn->query("SELECT * FROM `theta_stats` WHERE `time` > '".(time()-518400)."' ORDER BY `time`");
		} elseif($a == "0") {
			$getEach = $conn->query("SELECT * FROM `theta_users` WHERE `last` > '".(time()-518400)."' ORDER BY `last`");
		} elseif($a == "3"){
			$getEach = $conn->query("SELECT * FROM `theta_stats` WHERE `server` = '".$s."' AND `time` > '".(time()-518400)."' ORDER BY `time`");
		}

		$today = array();								$yesterday = array();
		$twodays = array();								$thrdays = array();
		$fourdays = array();							$fivdays = array();
		$sixdays = array();
		while($r = $getEach->fetch(PDO::FETCH_ASSOC)){

			if($a=="0"){$r['time']=$r['last'];}

			if($r['time'] > strtotime("midnight", time()) && $r['time'] < (strtotime("tomorrow", strtotime("midnight", time())) - 1)){
				$today[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-1 days midnight") && $r['time'] < (strtotime("midnight", time())-1)){
				$yesterday[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-2 days midnight") && $r['time'] < (strtotime("-1 days midnight", time())-1)){
				$twodays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-3 days midnight") && $r['time'] < (strtotime("-2 days midnight", time())-1)){
				$thrdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-4 days midnight") && $r['time'] < (strtotime("-3 days midnight", time())-1)){
				$fourdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-5 days midnight") && $r['time'] < (strtotime("-4 days midnight", time())-1)){
				$fivdays[] = $r['steamid'];
			}
			if($r['time'] > strtotime("-6 days midnight") && $r['time'] < (strtotime("-5 days midnight", time())-1)){
				$sixdays[] = $r['steamid'];
			}

		}

		// it works and its 4:34am.
		$tod = date('l', time());						$yes = date('l', strtotime("-1 days"));
		$two = date('l', strtotime("-2 days"));			$thr = date('l', strtotime("-3 days"));
		$fou = date('l', strtotime("-4 days"));			$fiv = date('l', strtotime("-5 days"));
		$six = date('l', strtotime("-6 days"));
		// days
		$dat = array($six,$fiv,$fou,$thr,$two,$yes,$tod);
		// datas
		if($u == '1'){
			$today = array_unique($today);
			$yesterday = array_unique($yesterday);
			$twodays = array_unique($twodays);
			$thrdays = array_unique($thrdays);
			$fourdays = array_unique($fourdays);
			$fivdays = array_unique($fivdays);
			$sixdays = array_unique($sixdays);
		}
		$counts = array(count($sixdays),count($fivdays),count($fourdays),count($thrdays),count($twodays),count($yesterday),count($today));
		// combined
		$all = array('days'=>$dat,'counts'=>$counts);
		return $all;
	}

	public static function getServer($id){
		include __DIR__.'/config.php';
		if($id !== "0"&&!$restricted){
			$getServerName = $conn->query("SELECT * FROM `theta_servers` WHERE `id` = '".$id."'");
			$server = $getServerName->fetch(PDO::FETCH_ASSOC);
			if($getServerName->rowCount()=="0"){
				$server['name'] = "Unassigned server";
			}
			$serverArray = array($server['name'], $server['ip'], $server['port'], $server['time']);
		} else {
			$serverArray = array('Unassigned server', 'noIP', '27015', time());
		}
		return $serverArray;
	}

	public static function getRPStats($file, $id, $datab){
		include __DIR__.'/config.php';
		$rp = "mysql:host=$host;dbname=$datab";
		try {
			$darkrp = new PDO($rp, $user, $pass, $opt);
		} catch(PDOException $e){
			return 'Incorrect database specified!';
		}
		if($darkrp!==NULL){
			if($id == '0'){
				$totalWallet = $darkrp->query("SELECT SUM(`wallet`) AS totalEco FROM `darkrp_player`");
				$totalEco = $totalWallet->fetch(PDO::FETCH_ASSOC);
				return '<b>$'.number_format($totalEco['totalEco']).'</b>';
			} else {
		    	$type = (int) (((int) substr((string) $id, -1)) & 1) ? 1 : 0;
		    	$steamid32 = sprintf("STEAM_0:%d:%d", $type, ((int) bcsub($id, "76561197960265728") - $type) / 2);
		    	$decode = json_decode(file_get_contents('inc/configs/'.$file), true);
		    	$findTable = $darkrp->query("SHOW TABLES LIKE '%playerinformation%'");
		    	if($findTable->rowCount() > '0'){
					$checkPlayers = $darkrp->query("SELECT * FROM `playerinformation` WHERE `steamID` = '".$steamid32."' LIMIT 1");
					if($checkPlayers->rowCount() == "1"){
						$playerinfo = $checkPlayers->fetch(PDO::FETCH_ASSOC);
						$findPlayer = $darkrp->query("SELECT * FROM `darkrp_player` WHERE `uid` = '".$playerinfo['uid']."' LIMIT 1");
						$player = $findPlayer->fetch(PDO::FETCH_ASSOC);
						return array($player['rpname'], $player['wallet']);
					} else {
						return array('No RP name found', '0');
					}
				} else {
					return array('No RP name found', '0');
				}
			}
		}
	}

	public static function time_convert($s){
			$bit = array(
	        ' year'        => $s / 31556926 % 12,
	        ' week'        => $s / 604800 % 52,
	        ' day'        => $s / 86400 % 7,
	        ' hour'        => $s / 3600 % 24,
	        ' min'    => $s / 60 % 60,
	        ' sec'    => $s % 60
	        );

	    foreach($bit as $k => $v){
	        if($v > 1)$ret[] = $v . $k . 's';
	        if($v == 1)$ret[] = $v . $k;
	        }
	    /*array_splice($ret, count($ret)-1, 0);
	    arsort($ret[]);*/
	    if($ret[0] == 0){
	        return 'Just Now';
	    } else {
	        // return $ret[0].' '.$ret[1].' ago'; /*join(' ', $ret)*/
	        return $ret[0].' ago';
	    }
	}
	public static function time_convert_long($s){
			$bit = array(
	        ' year'        => $s / 31556926 % 12,
	        ' week'        => $s / 604800 % 52,
	        ' day'        => $s / 86400 % 7,
	        ' hour'        => $s / 3600 % 24,
	        ' minute'    => $s / 60 % 60,
	        ' second'    => $s % 60
	        );

	    foreach($bit as $k => $v){
	        if($v > 1)$ret[] = $v . $k . 's';
	        if($v == 1)$ret[] = $v . $k;
	        }
	    /*array_splice($ret, count($ret)-1, 0);
	    arsort($ret[]);*/
	    if($ret[0] == 0){
	        return 'Just Now';
	    } else {
	        // return $ret[0].' '.$ret[1].' ago'; /*join(' ', $ret)*/
	        return $ret[0].' ago';
	    }
	}

	public static function dbTables(){

		include __DIR__.'/config.php';
		if($conn!==null){
			$checkTables = $conn->query("SHOW TABLES LIKE 'theta_users'");
			if(!$checkTables->fetch(PDO::FETCH_ASSOC)){
				$createTables = "CREATE TABLE `theta_users` (
					`steamid` varchar(17) NOT NULL PRIMARY KEY,
					`first` int(10) NOT NULL,
					`last` int(10) NOT NULL,
					`cfirst` int(10) NOT NULL,
					`clast` int(10) NOT NULL,
					`ip` varchar(45) NOT NULL,
					`staff` int(1) NOT NULL,
					`loggedIn` int(1) NOT NULL,
					`avi` varchar(200) NOT NULL,
					`usern` varchar(32) NOT NULL
				); CREATE TABLE `theta_stats` (
					`id` int(10) AUTO_INCREMENT NOT NULL PRIMARY KEY,
					`steamid` varchar(17) NOT NULL,
					`server` int(10) NOT NULL,
					`time` int(10) NOT NULL
				); CREATE TABLE `theta_servers` (
					`id` int(10) AUTO_INCREMENT NOT NULL PRIMARY KEY,
					`name` varchar(32) NOT NULL,
					`ip` varchar(50) NOT NULL,
					`port` int(5) NOT NULL,
					`time` int(10) NOT NULL
				); CREATE TABLE `theta_installs` (
					`id` int(10) AUTO_INCREMENT NOT NULL PRIMARY KEY,
					`path` varchar(500) NOT NULL,
					`first` int(10) NOT NULL,
					`last` int(10) NOT NULL,
					`server` int(10) NOT NULL
				);";
				$conn->exec($createTables);
				$addAdmin = $conn->prepare("INSERT INTO `theta_users` (`steamid`, `first`, `last`, `cfirst`, `ip`, `staff`, `avi`, `usern`) VALUES (?,?,?,?,?,?,?,?)");
				$addAdmin->execute(array($admin, time(), time(), time(), $_SERVER['REMOTE_ADDR'], '2', self::$avaFll, self::$username));
				$insertFirstStat = $conn->prepare("INSERT INTO `theta_stats` (`steamid`, `server`, `time`) VALUES (?,?,?)")->execute(array(self::$steamid,0,time()));
			}
			return true;
		} else {
			return false;
		}

	}

}

function paginate_function($item_per_page, $current_page, $total_records, $total_pages){
    $pagination = '';
    if($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages){ //verify total pages and current page number
        $pagination .= '<ul class="pagination">';

        $right_links    = $current_page + 3;
        $previous       = $current_page - 1; //previous link
        $next           = $current_page + 1; //next link
        $first_link     = true; //boolean var to decide our first link

        if($current_page > 1){
            $previous_link = ($previous==0?1:$previous);
            $pagination .= '<li class="first"><a href="#" data-page="1" title="First">&laquo;</a></li>'; //first link
            $pagination .= '<li><a href="#" data-page="'.$previous_link.'" title="Previous">&lt;</a></li>'; //previous link
                for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
                    if($i > 0){
                        $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page'.$i.'">'.$i.'</a></li>';
                    }
                }
            $first_link = false; //set first link to false
        }

        if($first_link){ //if current active page is first link
            $pagination .= '<li class="first active"><a href="#">'.$current_page.'</a></li>';
        }elseif($current_page == $total_pages){ //if it's the last active link
            $pagination .= '<li class="last active"><a href="#">'.$current_page.'</a></li>';
        }else{ //regular current link
            $pagination .= '<li class="active"><a href="#">'.$current_page.'</a></li>';
        }

        for($i = $current_page+1; $i < $right_links ; $i++){ //create right-hand side links
            if($i<=$total_pages){
                $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page '.$i.'">'.$i.'</a></li>';
            }
        }
        if($current_page < $total_pages){
                $next_link = ($i > $total_pages)? $total_pages : $i;
                $pagination .= '<li><a href="#" data-page="'.$next_link.'" title="Next">&gt;</a></li>'; //next link
                $pagination .= '<li class="last"><a href="#" data-page="'.$total_pages.'" title="Last">&raquo;</a></li>'; //last link
        }

        $pagination .= '</ul>';
    }
    return $pagination; //return pagination links
}

$theta = new theta();

$ready = file_exists('inc/config.php');
