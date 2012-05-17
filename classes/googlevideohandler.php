<?php

class googlevideohandler implements OCCustomEmbedHandlerInterface
{
    static public function regex()
    {
        return '#http://video\.google\.([A-Za-z.]{2,5})/videoplay\?docid=([\d-]+)(.*?)#i';
    }
    
    static public function callback( $matches, $url, $args )
    {        
    	extract($args);
	
    	return '<embed type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docid=' . $matches[2] . '&amp;hl=en&amp;fs=true" style="width:' . $width . 'px;height:' . $height . 'px" allowFullScreen="true" allowScriptAccess="always"></embed>';
    }
}

?>