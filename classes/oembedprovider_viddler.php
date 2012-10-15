<?php

class oembedprovider_viddler implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?viddler\.com/.*#i'             => array( 'http://lab.viddler.com/services/oembed/',  true  )
        );
    }
}

?>