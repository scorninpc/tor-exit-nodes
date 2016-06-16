<?php

require_once 'TorExitNodes.php';

$tor = new TorExitNodes;

$ip = $_SERVER['REMOTE_ADDR'];
//$ip = '45.35.90.36';

print 'Your ip is "' . $ip . "\" and <br>\n";

if($tor->isTorExitNode($ip)) {
    print 'is an Tor address.';
    print '<img style="width: 30px; height:30px;" src="img/tor-on.png"></img>';
}
else {
    print 'is NOT an Tor address.';
    print '<img style="width: 30px; height:30px;" src="img/tor-off.png"></img>';
}
?>
