<?php

$urlEncoded = eZHTTPTool::instance()->getVariable('u');
$url = base64_decode($urlEncoded);
if ($url) {
    $oembedHandler = new OCoEmbed;
    $oembed = (array)$oembedHandler->get_html($url, [], true);
    if (eZHTTPTool::instance()->hasGetVariable('debug')) {
        header('Content-Type: application/json');
        echo json_encode($oembed);
    } else {
        $tpl = eZTemplate::factory();
        $tpl->setVariable('oembed', $oembed);
        $tpl->setVariable('url', $url);
        echo $tpl->fetch('design:ocembed/preview.tpl');
    }
}

eZExecution::cleanExit();
