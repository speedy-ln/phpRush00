<?php
header('Access-Control-Allow-Origin: *');
foreach (glob("Libraries/*.php") as $filename){
    require_once $filename;
}
foreach (glob("Controllers/*.php") as $filename){
    require_once $filename;
}
foreach (glob("Models/*.php") as $filename) {
    require_once $filename;
}

construct();
