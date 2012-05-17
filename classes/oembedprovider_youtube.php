<?php

class oembedprovider_youtube implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(www\.)?youtube.com/watch.*#i'         => array( 'http://www.youtube.com/oembed',            true  ),
            '#http://(www\.)?youtube.com/playlist.*#i'      => array( 'http://www.youtube.com/oembed',            true  ),
            'http://youtu.be/*'                             => array( 'http://www.youtube.com/oembed',            false )
        );
    }
}

?>