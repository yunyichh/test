<?php
function debug($msg){
	$message = $msg;
	 include_once(dirname(__DIR__)."/debug.php");
}
function dump($msg){
    echo "<pre>";
    var_dump($msg);
}
function br(){
    echo "<br/>";
}