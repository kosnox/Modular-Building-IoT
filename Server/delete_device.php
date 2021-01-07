<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
require("rb-mysql.php");

if (!empty($_GET["floor"])) {
  require 'db.php';
  R::setup(dbCredentials());
    $device = R::findOne( 'device', ' _floor = :floor AND _name = :name',
              [ ":floor" => $_GET["floor"], ":name" => $_GET["name"] ]);
    $deviceSettings = R::findAll( 'devicesettings', 'device_id = :device_id',
              [ ":device_id" => $device->id]);
     $deviceReport = R::findOne( 'devicereport', 'device_id = :device_id',
              [ ":device_id" => $device->id]);
     $deviceData = R::findOne( 'devicedata', 'device_id = :device_id',
              [ ":device_id" => $device->id]);

    if($device != null) R::trash( $device );
    if($deviceSettings != null) R::trashAll( $deviceSettings );
    if($deviceReport != null) R::exec('DELETE FROM devicereport WHERE device_id=:device_id', [ ":device_id" => $device->id]);
    if($deviceData != null) R::trashAll( $deviceData );
    if (isset($_SERVER["HTTP_REFERER"])) {
      header("Location: " . $_SERVER["HTTP_REFERER"]);
  }
  }
?>
