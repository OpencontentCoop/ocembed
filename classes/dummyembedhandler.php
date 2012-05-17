<?php

class dummyembedhandler implements OCCustomEmbedHandlerInterface
{
    static public function regex()
    {        
        return false;
    }
    
    static public function callback( $matches, $url, $args )
    {	
    	return false;
    }
}

?>