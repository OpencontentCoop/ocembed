<?php

class oembedprovider_funnyordie implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?funnyordie\.com/videos/.*#i' => array( 'http://www.funnyordie.com/oembed', true  )
        );
    }
}

?>