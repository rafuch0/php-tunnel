<?php

date_default_timezone_set('EST');
set_time_limit(0);
error_reporting(0);

if (isset($_GET['s'])) die(highlight_file(__FILE__, 1));

$hostname = 'nohost';
$randport = 'noport';

if(isset($_POST['host'])) $hostname = strip_tags(basename(strval($_POST['host'])));

if(preg_match("/^([a-z0-9-.]*)(\.([a-z]{2,3}))?\:[0-9]{2,5}?$/",$hostname)) $hostname = escapeshellarg(escapeshellcmd($hostname));
else $hostname = 'nohost';

if(isset($_SERVER['REMOTE_ADDR'])) $remoteaddr = escapeshellarg(escapeshellcmd(strip_tags(basename(strval($_SERVER['REMOTE_ADDR'])))));

if(isset($_POST['port'])) $randport = escapeshellarg(escapeshellcmd(strip_tags(basename(strval($_POST['port'])))));

if(($hostname != 'nohost') && ($randport == 'noport'))
{
	$randport = mt_rand(50000,50200);
	$randport = escapeshellarg(escapeshellcmd($randport));

	$requestCode = md5($remoteaddr.'-'.$hostname.':'.$randport);

	if(!file_exists('cache/'.$remoteaddr.'-'.$requestCode))
	{
		$fp = fopen('cache/'.$remoteaddr.'-\''.$requestCode.'\'','w');
		if($fp)	fputs($fp, $hostname."\t".$randport);
		fclose($fp);
	}
}

if(($hostname != 'nohost') && ($randport != 'noport') && isset($_POST['request']))
{
	header('HTTP/1.0 200 OK');
	header('Status: 200 OK');
	header('content-type:text/plain');
        header('Content-Transfer-Encoding: compress');
        header('Content-Encoding: compress');

	$requestCode = strval($_POST['request']);

	if($requestCode != md5($remoteaddr.'-'.$hostname.':'.$randport)) die('Error: Success!');

	if(!file_exists('cache/'.$remoteaddr.'-\''.$requestCode.'\''))	die('Error: Success!');
	else
	{
		@exec('nice -n 19 netcat -vv -t -r -c -w120 -s192.168.0.200 -p'.$randport.' -L'.$hostname.' 2>&1');
		//disabled cache and printing of it
		//exec('nice -n 19 netcat -vv -t -r -c -w120 -s192.168.0.3 -p'.$randport.' -L'.$hostname.' -o "cache/'.$remoteaddr.'-'.$hostname.':'.$randport.'" 2>&1');
		//readfile('cache/'.$remoteaddr.'-'.$hostname.':'.$randport);
	}
}
else
{
	header('content-type:text/html');
        header('Content-Transfer-Encoding: compress');
        header('Content-Encoding: compress');

	echo '<html><head><title></title><script type="text/javascript" src="cornify.js"></script></head><body><center><form method="post">';
	if($randport != 'noport')
	{
		$porttrim = trim($randport,'\'');
		$hosttrim = trim($hostname,'\'');
		echo $_SERVER['HTTP_HOST'].':'.$porttrim.' => '.$hosttrim;
		echo '<input type="hidden" name="host" value="'.$hosttrim.'">';
		echo '<input type="hidden" name="port" value="'.$porttrim.'">';
		echo '<input type="hidden" name="request" value="'.$requestCode.'">';
		echo '<br><input type="submit" value="hurf!">';
	}
	else
	{
		echo 'You: '.$_SERVER['REMOTE_ADDR'].'<br>';
		echo 'Bounce To Host/IP:Port<br><input name="host">';
		echo '<br><input type="submit" value="prehurf">';
	}
	echo '</form></center><script type="text/javascript">setInterval(cornify_add, 15000);</script></body></html>';
}
