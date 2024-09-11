#!/usr/bin/php
<?php
require __DIR__ . "/pfsense.php";

//////////////////////////////////////////////////////////////////////////////////////////////
$HOST     = "192.168.4.189";
$USERNAME = 'admin';
$PASSWORD = 'passwOrd';
$DEBUG    = 1;
//////////////////////////////////////////////////////////////////////////////////////////////

$psa = new PfSenseAPI($HOST, $USERNAME, $PASSWORD, $DEBUG);

$pfVLANS = $psa->get('interface/vlans')['data'];

$res = $psa->post('interface',[
    'if' => 'ixl2.1',
    'enable' => true,
    'descr' => 'VLAN1',
    'typev4' => 'static',
    'ipaddr' => '192.168.1.1',
    'subnet' => 24,
    'typev6' => 'none',
]);
