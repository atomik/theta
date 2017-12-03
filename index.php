<?php
	// ONLY UNCOMMENT THIS IF ATOMIK TOLD YOU TO
	// ini_set('display_errors', '-1');
	include 'inc/theta.class.php';
	if($ready&&isset($_SESSION['steam'])){ theta::modUser(theta::$steamid, true, 0); }
	(!isset($_GET['page'])?$_GET['page']='dash':'');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Theta</title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="compiled/css/styles.min.css">
	<style type="text/css">body,h1,h2,h3,h4,h5,h6{font-family: 'Open Sans'}
		body {
			min-height: 100%;
			background: url('img/o5KUipR.jpg') no-repeat !important;
			background-size: cover !important;
			background-attachment: fixed !important;
			background-position: center;
		}
	</style>
	<script type="text/javascript" src="compiled/js/jquery.js"></script>
	<script type="text/javascript" src="compiled/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="compiled/js/chart.min.js"></script>
	<script type="text/javascript" src="compiled/js/core.min.js"></script>
	<script type="text/javascript" src="compiled/js/1.3.5.min.js"></script>
</head>
<body>
	<?= ($ready)?theta::nav():'' ?>
	<div class="container">
		<?php if($_GET['page']=='dash'||!isset($_GET['page'])){ ($ready)?include __DIR__.'/inc/config.php':'' ?>
			<?php ($ready&&$restricted)?header('Location: ./index.php?page=load'):'' ?>
			<div class="row">
				<div class="col-md-6<?= (!$ready?' col-md-offset-3':'') ?>">
					<div class="panel panel-default">
						<div class="panel-heading"><?= ($ready?'dashboard':'setup') ?></div>
						<div class="panel-body">
							<?= ($ready?theta::dash(1):theta::host()) ?>
						</div>
					</div>
				</div>
				<?php if($ready){ ?>
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-heading">latest connections<span class="pull-right"><div class="loader"></div></span></div>
							<div class="panel-body">
								<div id="latestPlayers"></div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } elseif($_GET['page']=='load'){
			if(!file_exists('inc/configs/noIP.json')){
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
					"serverInfo" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer est purus, fringilla id vehicula ut, accumsan in risus.",
					"darkrpDB" => ""
				);
				$arrayToJson = json_encode($LSConfig);
				$createConfig = fopen('inc/configs/noIP.json', 'w') or die('Permissions changed 532');
				fwrite($createConfig, $arrayToJson);
				fclose($createConfig);
				header('Location: ./index.php?page=load');
			}
			$ls = json_decode(file_get_contents('inc/configs/noIP.json'), true);
			if(isset($_POST['type']) && $_POST['type'] == "saveConfig"){
				$music = trim($_POST['music']);
				$music = explode("\n", $music);
				$musicA = array();
				foreach($music as $songz){
					$song = explode(' ', $songz);
					$musicA[$song[0]] = str_replace($song[0], '', $songz);
				}
				$LSConfig = array(
					"fontFamily" 		=> $_POST['fontFamily'],
					"titleImg"			=> htmlspecialchars($_POST['titleImg']),
					"titleImgHeight"	=> $_POST['titleImgHeight'],
					"title" 			=> $_POST['title'],
					"titleFontSize"		=> htmlspecialchars($_POST['titleFontSize']),
					"gametracker" 		=> htmlspecialchars($_POST['gametracker']),
					"mapImage" 			=> htmlspecialchars($_POST['mapImage']),
					"borderColor"		=> $_POST['borderColor'],
					"backgroundImage"	=> $_POST['backgroundImage'],
					"customMsg" 		=> htmlspecialchars($_POST['customMsg']),
					"customMsgAnswer" 	=> htmlspecialchars($_POST['customMsgAnswer']),
					"rules" 			=> htmlspecialchars($_POST['rules']),
					"animation" 		=> htmlspecialchars($_POST['animation']),
					"aboutTitle" 		=> $_POST['aboutTitle'],
					"aboutUs" 			=> htmlspecialchars($_POST['aboutUs']),
					"music" 			=> $musicA,
					"volume"			=> $_POST['volume'],
					"serverInfo"		=> htmlspecialchars($_POST['serverInfo']),
					"darkrpDB"			=> ""
				);
				$arrayToJson = json_encode($LSConfig);
				$createConfig = fopen('inc/configs/noIP.json', 'w') or die('Permissions changed 531');
				fwrite($createConfig, $arrayToJson);
				fclose($createConfig);
				header('Location: ./');
			}
		?>
			<script type="text/javascript" src="compiled/js/bootstrap-markdown.js"></script>
			<script type="text/javascript" src="compiled/js/to-markdown.js"></script>
			<script type="text/javascript" src="compiled/js/markdown.js"></script>
			<h4><b>Setup loading screen</b></h4>
			<div class="bg-default" style='padding:15px'>
				<div class="row">
					<div class="col-md-4">

					</div>
				</div>
				<div class="row">
					<div id="displayConfig" class="col-md-12" style="visibility:hidden">
						Config line: <code>sv_loadingurl &quot;http://<?= $_SERVER['HTTP_HOST'].str_replace('index.php', '', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))) ?>loading.php?steamid=%s&mapname=%m&quot;</code>
					</div>
				</div>
			</div>
			<div>
				<h4><b>Preview</b></h4>
				<div class="bg-default" style='padding:15px'>
					<a href="" data-steamid="<?= theta::$steamid ?>" class="btn btn-info specificLink" target="_blank">Click here</a>
				</div>
				<h4><b>Configuration</b><span id="savedConfig"></span></h4>
				<div class="bg-default" style='padding:15px'>
					<form method="post">
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
									<p>Title image prefix height<small> &sdot; You'll have to adjust the font size to move it up more</small></p>
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
									echo $music.' '.$songName."\n";
								}
							?></textarea>
						</div>
						<div class="form-group">
							<p>Volume &sdot; <small>0 - 100</small></p>
							<input type="text" class="form-control" name="volume" value="<?= $ls['volume'] ?>">
						</div>
						<div class="form-group">
							<p>Server info</p>
							<textarea class="form-control" data-provide="markdown" name="serverInfo" style="max-width:100%" rows="16"><?= $ls['serverInfo'] ?></textarea>
						</div>
						<div class="form-group text-right">
							<input type="hidden" name="type" value="saveConfig">
							<input type="submit" class="btn btn-success btn-lg" value="Save">
						</div>
					</form>
				</div>
			</div>
		<?php } elseif($_GET['page']=='servers'){
			$u=theta::getUser(theta::$steamid);
			if($u[7]=='2'||$admin==theta::$steamid){
		?>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">servers<span class="pull-right"><div class="loader"></div></span></div>
							<div class="panel-body">
								<div class="row">
									<div id="getServers"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="panel panel-default">
							<div class="panel-heading">Add a server</div>
							<div class="panel-body">
								<div class="alert alert-success alert-dismissible" role="alert" style="display:none">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<strong>Success!</strong> Server added!
								</div>
								<form method="post" id="addServer">
									<div class="form-group">
										<input type="text" autocomplete="off" class="form-control" name="name" placeholder="Name">
										<input type="hidden" name="type" value="addServer">
									</div>
									<div class="form-group">
										<input type="text" autocomplete="off" class="form-control" name="host" placeholder="Host">
									</div>
									<div class="form-group">
										<input type="text" autocomplete="off" class="form-control" name="port" placeholder="Port" value="27015">
									</div>
									<div class="form-group text-right" style="margin-bottom:0">
										<input type="submit" class="btn btn-success" value="Add">
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-default">
							<div class="panel-heading">Edit a server<span class="pull-right"><div class="loader"></div></span></div>
							<div class="panel-body">
								<div class="bg-default">
									<i class="glyphicon glyphicon-info-sign"></i> Recommended: Keep names short
								</div>
								<div class="alert alert-success alert-dismissible" role="alert" style="display:none">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<strong>Success!</strong> Server saved!
								</div>
								<form method="post" id="editServer">
									<div class="form-group serverAddOptions"></div>
									<div class="toggleOptions"></div>
								</form>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-default">
							<div class="panel-heading">Delete a server</div>
							<div class="panel-body">
								<div class="alert alert-danger alert-dismissible" role="alert" style="display:none">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<strong>Warning!</strong> Server deleted!
								</div>
								<form method="post" id="deleteServer">
									<div class="form-group">
										<div class="deleteServerForm"></div>
									</div>
									<div class="form-group text-right" style="margin-bottom:0">
										<input type="submit" class="btn btn-danger" value="Delete">
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			<?php } else { header('Location: ?page=dash'); } ?>
		<?php } elseif($_GET['page']=="stats"){ ?>
			<div class="row">
				<div class="panel panel-default">
					<div class="panel-heading">server statistics<span class="pull-right"><div class="loader"></div></span></div>
					<div class="panel-body">
						<div id="serverStats"></div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">global statistics</div>
					<div class="panel-body">
						<?= theta::dash(0) ?>
					</div>
				</div>
			</div>
		<?php } elseif($_GET['page']=="users"){ ?>
			<div class="row">
				<div class="panel panel-default">
					<div class="panel-heading">search for a user</div>
					<div class="panel-body">
						<div class="col-md-12">
							<form id="searchUser">
								<div class="form-group">
									<input type="text" class="form-control" name="userThang" placeholder="Steam ID 64/32, Steam URL, etc.">
								</div>
							</form>
							<div class="row">
								<div id="loadUsers"></div>
							</div>
						</div>
					</div>
				</div>
				<!-- Because my stupid ass deleted the uncompressed javascript file so FUUUUCK IT. >:l -->
				<script type="text/javascript">
					$("#searchUser").on('keyup', function(){
						$.get('inc/ajax.php', {type:"searchUser", query:$(this).find('[name="userThang"]').val()}).done(function(html){
							$("#loadUsers").html(html);
						});
					});
				</script>
				<div class="panel panel-default">
					<div class="panel-heading">users that connected to your server(s)</div>
					<div class="panel-body">
						<div class="col-md-12">
							<div id="allUsers"></div>
						</div>
					</div>
				</div>
			</div>
		<?php } elseif($_GET['page']=="profile"&&isset($_GET['id'])){
			include __DIR__.'/inc/config.php';
			$u=theta::getUser($_GET['id']);
			$v=theta::getUser(theta::$steamid);
			if(!$u){ ?>
				<div class="alert alert-danger text-center"><i class="glyphicon glyphicon-info-sign"></i> Warning! Invalid Steam ID / User has not connected to a server!</div>
			<?php } else {

				// All connection count
				$all = $conn->prepare("SELECT * FROM `theta_stats` WHERE `steamid` = :steamid");
				$all->bindParam(':steamid', $_GET['id']);
				$all->execute();

				// Last server connection search
				$last = $conn->prepare("SELECT * FROM `theta_stats` WHERE `steamid` = :steamid ORDER BY `time` DESC LIMIT 1");
				$last->bindParam(':steamid', $_GET['id']); $last->execute();
				$L = $last->fetch(PDO::FETCH_ASSOC); // because i took an L here on 8/02/2016 6:17 AM
				$server = theta::getServer($L['server']);

				// First server connection search
				$first = $conn->prepare("SELECT * FROM `theta_stats` WHERE `steamid` = :steamid ORDER BY `time` LIMIT 1");
				$first->bindParam(':steamid', $_GET['id']); $first->execute();
				$F = $first->fetch(PDO::FETCH_ASSOC);
				$serverF = theta::getServer($F['server']);
		?>
				<div class="row">
					<div class="col-md-3 col-sm-4 col-xs-12">
						<div class="bg-default text-center">
							<img style="width:100%" src="<?= $u[1] ?>">
							<h3><b><?= $u[0] ?></b></h3>
							<p id="staffRank"><?= theta::staff($u[7]) ?></p>
							<p>Connected: <?= number_format($all->rowCount()) ?> times!</p>
							<hr/>
							<p>First connected to:<br/><b><?= $serverF[0] ?></b><br><?= ($F['time']!=='')?date('F d', $F['time']).' &sdot; '.theta::time_convert(time()-$F['time']):'No connection history.' ?></p>
							<p>Last connected to:<br/><b><?= $server[0] ?></b><br><?= ($L['time']!=='')?theta::time_convert(time()-$L['time']):'No connection history.' ?></p>
						</div>
						<div class="bg-default text-center">
							<div class="btn-group-xs" role="group">
								<a href="http://steamcommunity.com/profiles/<?= $_GET['id'] ?>" target="_blank" class="btn btn-default">Steam Profile</a>
								<a href="http://scriptfodder.com/users/view/<?= $_GET['id'] ?>" target="_blank" class="btn btn-default">ScriptFodder</a>
								<a href="steam://friends/add/<?= $_GET['id'] ?>" class="btn btn-success">Add Friend</a>
							</div>
						</div>
						<?php if($v[7]=='2'||$admin==theta::$steamid){ ?>
							<div class="bg-default text-center" style="margin-top:15px;padding-top:15px">
								<?= ($u[7] == '2')?'':'<form id="promoteUser" style="margin-right:10px;display:inline">Promote <input type="submit" class="btn btn-xs btn-success" value="+"></form>' ?>
								<?= ($u[7] == '0')?'':'<form id="demoteUser" style="display:inline">Demote <input type="submit" class="btn btn-xs btn-danger" value="X"></form>' ?>
							</div>
							<script type="text/javascript">
								$("#promoteUser").submit(function(e){
									e.preventDefault();
									$.post('inc/ajax.php', {type:"promoteUser",rank:"2",steamid:"<?= $_GET['id'] ?>"}).done(function(){
										$("#staffRank").html("<?= theta::staff(2) ?>")
									});
								});
								$("#demoteUser").submit(function(e){
									e.preventDefault(); $.post('inc/ajax.php', {type:"demoteUser",rank:"0",steamid:"<?= $_GET['id'] ?>"}).done(function(){
										$(document).find("#staffRank").html("<?= theta::staff(0) ?>")
									});
								});
							</script>
						<?php } ?>
					</div>
					<div class="col-md-9 col-sm-8 col-xs-12">
						<div id="curlSteamInfo" class="bg-default" data-sid="<?= $_GET['id'] ?>">
							<div class="loader"></div>
						</div>
						<div class="bg-default">
							<h3>DarkRP stats</h3>
							<div id="configReads" data-steamid="<?= $_GET['id'] ?>">
								<small>This may take a while depending on how many servers there are in this community.</small>
								<div class="loader"></div>
							</div>
						</div>
						<div class="alert alert-info text-center"><i class="glyphicon glyphicon-info-circle"></i> More stats coming soon...</div>
					</div>
				</div>
			<?php }
		} elseif($_GET['page'] == "admin"){
			include __DIR__.'/inc/config.php';
			$u=theta::getUser(theta::$steamid);
			(!isset($_GET['tab'])?$_GET['tab']='load':'');
			if($u[7]=="2"||$admin==theta::$steamid){
		?>
			<div class="row">
				<div class="col-md-3 col-sm-4">
					<div class="bg-default">
						<ul>
							<li<?= ($_GET['tab']=='load'?' class="active"':'') ?>><a href="?page=admin&tab=load">Loading Screen</a></li>
							<li<?= ($_GET['tab']=='installs'?' class="active"':'') ?>><a href="?page=admin&tab=installs">Install</a></li>
							<li class="divider"></li>
							<li><a href="http://atomik.info/theta/support">Support</a></li>
						</ul>
					</div>
				</div>
				<div class="col-md-9 col-sm-8">
					<?php if($_GET['tab']=="load"){ ?>
						<h4><b>Setup loading screen</b></h4>
						<div class="bg-default">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<select id="selectServer" class="form-control">
											<?php $getServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time`");
											if($getServers->rowCount()>0){
												while($r = $getServers->fetch(PDO::FETCH_ASSOC)){ ?>
													<option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
												<?php } ?>
											<?php } else { ?>
												<option value="0">No server found!</option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<?php if($getServers->rowCount()>0){ ?>
								<div class="row">
									<div class="col-md-12">
										<div class="alert alert-info text-center"><i class="glyphicon glyphicon-info-sign"></i> Please add the line below to the server's config specified above.</div>
									</div>
								</div>
							<?php } ?>
							<div class="row">
								<div id="displayConfig" class="col-md-12" style="visibility:hidden">
									Config line: <code>sv_loadingurl &quot;http://<?= $_SERVER['HTTP_HOST'].str_replace('index.php', '', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))) ?>loading.php?steamid=%s&mapname=%m&server=<span id="inputServer"></span>&quot;</code>
								</div>
							</div>
						</div>
						<div id="serverSpecific" style="visibility:hidden">
							<h4><b>Preview</b></h4>
							<div class="bg-default">
								<a href="" data-steamid="<?= theta::$steamid ?>" class="btn btn-info specificLink" target="_blank">Click here</a>
							</div>
							<h4><b>Configuration</b><span id="savedConfig"></span></h4>
							<div class="bg-default">
								<div id="configForm"><div class="loader"></div></div>
							</div>
						</div>
					<?php } elseif($_GET['tab'] == "installs"){ ?>
						<h4><b>Setup loading screen</b></h4>
						<div class="bg-default">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<select id="selectServer" class="form-control">
											<?php $getServers = $conn->query("SELECT * FROM `theta_servers` ORDER BY `time`");
											if($getServers->rowCount()>0){
												while($r = $getServers->fetch(PDO::FETCH_ASSOC)){ ?>
													<option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
												<?php } ?>
											<?php } else { ?>
												<option value="0">No server found!</option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<?php if($getServers->rowCount()>0){ ?>
								<div class="row">
									<div class="col-md-12">
										<div class="alert alert-info text-center"><i class="glyphicon glyphicon-info-sign"></i> Please add the line below to the server's config specified above.</div>
									</div>
								</div>
							<?php } ?>
							<div class="row">
								<div id="displayConfig" class="col-md-12" style="visibility:hidden">
									Config line: <code>sv_loadingurl &quot;http://<?= $_SERVER['HTTP_HOST'].str_replace('index.php', '', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))) ?>loading.php?steamid=%s&mapname=%m&server=<span id="inputServer"></span>&quot;</code>
								</div>
							</div>
						</div>
						<h4><b>Install Theta to another loading screen</b></h4>
						<div class="well">
							<a href="https://youtu.be/8G2I7sPcfoI?t=1m26s" target="_blank">Watch this for a visual aid.</a><br/>
							<b>Instructions:</b>
							<ol>
								<li>Transfer &quot;thetaLink.php&quot; from the inc directory into the main directory of the loading screen you'd like to track.</li>
								<li>Open up the file that the player views when connecting to the loading screen (usually index.php)</li>
								<li>Place this snippet at the <b>very</b> top of the loading screen</li>
								<li><code>&lt;?php include __DIR__.'/thetaLink.php'; ?&gt;</code></li>
								<li>Read the below tips/warnings.</li>
							</ol>
							<ul>
								<li>If you'd like to track more servers, simply copy the thetaLink.php file multiple times!</li>
								<li>To remove "Unassigned server"</li>
								<ul>
									<li>You must include <code>&server=&lt;SERVER_ID&gt;</code> at the end of your sv_loadingurl in order for the stats page to track it.</li>
								</ul>
							</ul>
						</div>
						<h4><b>Installations</b></h4>
						<div class="bg-default">
							<div id="getInstalls"><div class="alert alert-info text-center" style="margin-bottom:0"><i class="glyphicon glyphicon-info-sign"></i> Loading Screens that are being tracked will appear here!</div></div>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } else { header('Location: ?page=dash'); } } ?>
	</div>
	<div class="footer">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-3">
					<h1 class="text-center">Theta</h1>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-5">
					<ul>
						<li><a href="http://atomik.info/theta/support">Support</a></li>
						<li><a href="https://scriptfodder.com/community/threads/799-theta">ScriptFodder Thread</a></li>
						<li><a href="https://scriptfodder.com/scripts/view/2806">ScriptFodder Link</a></li>
						<li><a href="http://atomik.info">Author</a></li>
					</ul>
				</div>
			</div>
			<div class="row">
				<p class="text-center">&copy; Theta <?= (date('Y',time())=='2016')?'2016':'2016-'.date('Y', time()) ?></p>
			</div>
		</div>
	</div>
</body>
</html>
