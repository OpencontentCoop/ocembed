<?php

class oembedprovider_flickr implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(www\.)?flickr\.com/.*#i'              => array( 'http://www.flickr.com/services/oembed/',   true  )
        );
    }
}

?>