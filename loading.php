<?php
	// ini_set('display_errors', '-1');
	include __DIR__.'/inc/config.php'; include __DIR__.'/inc/theta.class.php'; include __DIR__.'/inc/parsedown.php';
	(!isset($_GET['server'])?$_GET['server']=0:'');
	(!isset($_GET['steamid'])?$_GET['steamid']='76561198089999589':'');
	$user = theta::getUser($_GET['steamid']);
	$server = theta::getServer($_GET['server']);
	$ls = json_decode(file_get_contents(__DIR__.'/inc/configs/'.$server[1].'-'.$server[2].'.json'), true);
	$mapImage = $ls['gametracker'];
	($ls['darkrpDB']!==""&&!$restricted?$rp=theta::getRPStats($server[1].'-'.$server[2].'.json', $_GET['steamid'], $ls['darkrpDB']):'');
	$parse = new Parsedown;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Theta &sdot; Loading Screen</title>
	<?php
		// hacky way to get the custom google font to work.
		$font = $ls['fontFamily'];
		$checkFont = substr($font, 18, strpos($font, "' rel")-18);
		$startOfFont = (strpos($font, "ily=")+4);
		$xFont = substr($font, $startOfFont, strpos($font, "' rel")-$startOfFont);
		if(strpos($checkFont, ':') !== false){
			$fixFont = substr($checkFont, strpos($checkFont, ':'));
			$repFont = 'https:'.str_replace($fixFont, '', $checkFont).':400,800,300';
			$xFont = str_replace($fixFont, '', $xFont);
		} else {
			$repFont = 'https:'.$checkFont.':400,800,300';
		}
		$xFont = str_replace("+", " ", $xFont);
		echo '<link href="'.$repFont.'" rel="stylesheet" type="text/css">';
	?>
	<style type="text/css">body,h1,h2,h3,h4,h5,h6{font-family: "<?= $xFont ?>" !important}</style>
	<link rel="stylesheet" type="text/css" href="compiled/css/styles.min.css">
	<script type="text/javascript" src="compiled/js/jquery.js"></script>
	<script type="text/javascript" src="compiled/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="compiled/js/core.min.js"></script>
	<style type="text/css">
		<?php if($ls['backgroundImage'] !== ""){ ?>body {
			background: url('<?= $ls['backgroundImage'] ?>') no-repeat !important;
			background-size: 100% 100% !important;
		}<?php } ?>
		.loading-wrapper .bg-default, .music .bg-default {
			border-left:4px solid <?= $ls['borderColor'] ?> !important;
		}
		.loading-wrapper h1.title {
			height: <?= $ls['titleImgHeight'] ?>;
			font-size: <?= $ls['titleFontSize'] ?> !important
		}
		.loading-wrapper h1.title.text-center .img-height,
		.loading-wrapper h1.title.text-center .img-height img {
			height: 100% !important
		}
	</style>
</head>
<body>
<?php if(isset($_GET['steamid']) && $_GET['steamid'] !== "%s" && isset($_GET['mapname']) && $_GET['mapname'] !== "%m" && strpos($_SERVER['HTTP_USER_AGENT'], "GMod") !== false) { theta::modUser($_GET['steamid'], false, $_GET['server']); } ?>
<div class="playerInfo">
	<img src="<?= $user[1] ?>">
	<p<?= ($restricted)?' style="margin-top:20px"':'' ?>>
		<span class="username"><span><?= $user[0] ?></span><?= ($ls['darkrpDB']!=='')?' &sdot; <small>'.$rp[0].' &sdot; <b>$'.number_format($rp[1]).'</b>':'' ?></span><br>
		<?php
			if(!$restricted){
				$lastLog = $conn->query("SELECT * FROM `theta_stats` WHERE `steamid` = '".$_GET['steamid']."' ORDER BY `time` DESC LIMIT 2,1");
				if($lastLog->rowCount()!==0){ $last = $lastLog->fetch(PDO::FETCH_ASSOC);
				echo '<b>Last visited: </b> '.date('F jS, g:i A', $last['time']).' ('.theta::time_convert_long(time()-$last['time']).')';
				} else { echo '<b>Welcome to the server!</b>'; } ?>
			<?php }
		?>
	</p>
</div>
<div class="music">
	<?php
		$songs = array();
		foreach($ls['music'] as $song => $title){
			$lastSeven = substr($song, strpos($song, '?v=')+3, strpos($song, '?v=')+11);
			$songs[$lastSeven] = $title;
		}
		$kay = array_rand($songs);
		$vee = $songs[$kay];
	?>
	<div class="bg-default">
		<h4><b>Now Playing</b></h4>
		<p><?= $vee ?></p>
	</div>
	<div id="player"></div>
	<script>
		var tag = document.createElement('script');

		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

		var player;
		function onYouTubeIframeAPIReady() {
			player = new YT.Player('player', {
				height: '0',
				width: '0',
				videoId: '<?= $kay ?>',
				events: {
					'onReady': onPlayerReady,
					'onStateChange': onPlayerStateChange
				}
			});
		}

		function onPlayerReady(event) {
			event.target.playVideo();
			event.target.setVolume(<?= $ls['volume'] ?>);
		}

		function onPlayerStateChange(event) {

			if(event.data == YT.PlayerState.ENDED){

				player.seekTo(0);

			}

		}

	</script>
</div>
<div class="loading-wrapper">
	<div class="row">
		<h1 class="text-center title"><?= ($ls['titleImg'] == '')?'':'<div class="img-height"><img src="'.$ls['titleImg'].'"></div>' ?><?= $ls['title'] ?></h1>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<div class="top">
				<div class="col-md-12">
					<div class="bg-default" style="overflow:hidden">
						<img style="height:100%<?= ($mapImage == '2'?';margin-left:50%;transform:translateX(-50%)':'') ?>" src="<?= ($mapImage!=="1")?$ls['mapImage']:'http://image.www.gametracker.com/images/maps/160x120/garrysmod/'.$_GET['mapname'].'.jpg' ?>">
						<?php if($mapImage !== '2'){ ?>
							<div class="server"<?= ($restricted)?' style="margin-top: 30px !important"':'' ?>>
								<p>We're currently playing on</p>
								<div class="map"><?= $_GET['mapname'] ?></div>
								<div id="getGM" data-server="<?= $_GET['server'] ?>"><small><div class="loader"></div></small></div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="bottom">
				<div class="col-md-12">
					<div class="bg-default">
						<h1 class="text-center animated fadeIn"><b>Server</b><?= (!$restricted)?'Status':'Info' ?></h1>
						<?= ($restricted)?'<p style="margin-top:30px">'.$parse->text($ls['serverInfo']).'</p>':'' ?>
						<ul class="animated fadeIn"<?= ($restricted)?' style="padding-bottom:20px"':'' ?>>
							<?php if(!$restricted){ ?>
								<li>
									<img src="img/user_gray.png"><span id="getPlayers" data-server="<?= $_GET['server'] ?>"></span> player(s) online<span style="float:right"><div class="loader"></div></span>
								</li>
								<li>
									<img src="img/user_add.png"> Newest: <span id="getNewestPlayer"><span class='pull-right'><div class="loader"></div></span></span>
								</li>
								<li>
									<img src="img/<?= ($ls['darkrpDB']!==""?'money':'tux') ?>.png"> <?= ($ls['darkrpDB']=="")?$server[1].':'.$server[2]:theta::getRPStats($server[1].'.json', 0, $ls['darkrpDB']).' total economy' ?>
								</li>
							<?php } ?>
							<li>
								<img src="img/rainbow.png"> <?= $ls['customMsg'] ?>: <span><b><?= $ls['customMsgAnswer'] ?></b></span>
							</li>
						</ul>
						<h3 id="status" class="text-center">Sending client info...</h3>
						<div class="progress">
							<div id="progressBar" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
								<div id="percentage"></div>
							</div>
						</div>
						<p id="downloads" class="text-center"><span id="needed">0</span>/<span id="total">0</span> downloads<br></p>
						<p id="file" class="text-center" style="font-size:10px"></p>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<div class="bg-default rulesAnimation">
				<h1 class="text-center animated fadeIn"><b>Rules</b></h1>
				<?php $rules = $parse->text($ls['rules']);
					if(strpos($rules, '<ol>') !== false){
						// rly hacky fix to add animations
						$rules = str_replace('<ol>', '', str_replace('</ol>', '', $rules));
						echo '<ol>';
						$rulez = explode("<li>", $rules);
						$i = 0.2;
						$count = 0;
						foreach($rulez as $rule){ $count++; if($count==1){continue;} ?>
							<li class="animated <?= $ls['animation'] ?>" style="animation-delay:<?= $i ?>s"><?= $rule ?></li>
							<?php $i = $i + 0.2;
						}
						echo '</ol>';
					} else {
						echo $rules;
					}
				?>
			</div>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-4">
			<div class="bg-default">
				<h1 class="text-center animated fadeIn"><?= $ls['aboutTitle'] ?></h1>
				<div class="animated fadeIn" style="animation-delay:0.2s">
					<?= $parse->text($ls['aboutUs']) ?>
				</div>
			</div>
		</div>
	</div>
</div>
	<script type="text/javascript" src="compiled/js/loading.min.js"></script>
</body>
</html>
