<?php

class oembedprovider_viddler implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(www\.)?viddler\.com/.*#i'             => array( 'http://lab.viddler.com/services/oembed/',  true  )
        );
    }
}

?>