<?php
// This file is executed once per minute by cron on the same machine as the proxy. 
// It pings each defined server and updates the proxy with the correct number of players.
$host = "192.168.5.106";
$port = "25565";
$hostA = "192.168.5.106";
$portA = "25565";
$hostB = "192.168.5.106";
$portB = "25566";
$hostT = "araeosia.com";
$portT = "25565";
$config = "/home/agentkid/Proxy/config.json";
// Ping function.
function pingserver($host, $port=25565, $timeout=30) {
	$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
	if (!$fp) return false;
	fwrite($fp, "\xFE");
	$d = fread($fp, 256);
	if ($d[0] != "\xFF") return false;
	$d = substr($d, 3);
	$d = mb_convert_encoding($d, 'auto', 'UCS-2');
	$d = explode("\xA7", $d);
	return array(
		'motd'        =>        $d[0],
		'players'     => intval($d[1]),
		'max_players' => intval($d[2]));
}
// Ping each specified server
$serverinfoA = pingserver($hostA, $portA, $timeout=30);
$serverinfoB = pingserver($hostB, $portB, $timeout=30);
// Calculate the total number of players online
$playersum = $serverinfoA['players'] + $serverinfoB['players'];
$serverinfoT = pingserver($hostT, $portT, $timeout=30);
// Save us the hassle of updating the file if it's already correct.
if($playersum != $serverinfoT['players']){
	// Delete old file
	unlink($config);
	$fh = fopen($config, 'w') or die("can't open file");
	$stringData = '{
  "motd": "Araeosia 1.2.4",
  "capacity": 1000,
  "players": ' . $playersum . ',
  "hosts": {
    "araeosia.com": {
      "host": "192.168.5.106",
      "port": 25566,
      "alias": [

        "mc.araeosia.com",
        "play.araeosia.com",
        "Araeosia.com",
        "Araeosia.Com"
      ]
    },
    "freebuild.araeosia.com": {
      "host": "192.168.5.104",
      "port": 25565,
      "alias": [

        "free.araeosia.com",
        "fb.araeosia.com",
        "play2.araeosia.com",
        "fusioncraft.org",
        "FusionCraft.org",
        "FusionCraft.Org"
      ]
    }
  }
}';
	fwrite($fh, $stringData);
	fclose($fh);
// Fetch the PID of the proxy, then reload it.
	$pid = exec('pidof python');
	exec('kill -1 ' . $pid);
}
echo "Done!";