<?php
$Module = array( 'name' => 'OpenContent Embed' );

$ViewList = array();
$ViewList['preview'] = array(
    'functions' => array( 'preview' ),
    'script' => 'preview.php',
    'params' => array('Key'),
);

$FunctionList = array();
$FunctionList['preview'] = array();
