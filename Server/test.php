<?php
require("rb-mysql.php");

require 'db.php';
R::setup(dbCredentials());

$device = R::dispense( 'device' );
$device->Name = 'termometr';
$device->Floor = 1;
$id = R::store( $device );

?>
