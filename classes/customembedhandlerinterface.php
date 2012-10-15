<?php

interface OCCustomEmbedHandlerInterface
{
    static public function regex();	
    static public function callback( $matches, $url, $args );
}

?>
