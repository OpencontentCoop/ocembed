<?php

class oembedprovider_youtube implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?youtube.com/watch.*#i'         => array( 'https://www.youtube.com/oembed',            true  ),
            '#https?://(www\.)?youtube.com/playlist.*#i'      => array( 'https://www.youtube.com/oembed',            true  ),
            'https?://youtu.be/*'                             => array( 'https://www.youtube.com/oembed',            false )
        );
    }
}

?>