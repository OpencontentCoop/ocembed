<?php

class oembedprovider_hulu implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(www\.)?hulu\.com/watch/.*#i'          => array( 'http://www.hulu.com/api/oembed.{format}',  true  )
        );
    }
}

?>