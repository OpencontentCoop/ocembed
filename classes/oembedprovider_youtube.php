<?php

class oembedprovider_youtube implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?youtube.com/watch.*#i'         => array( 'http://www.youtube.com/oembed',            true  ),
            '#https?://(www\.)?youtube.com/playlist.*#i'      => array( 'http://www.youtube.com/oembed',            true  ),
            'https?://youtu.be/*'                             => array( 'http://www.youtube.com/oembed',            false )
        );
    }
}

?>