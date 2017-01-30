<?php

class oembedprovider_blip implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'http://blip.tv/file/*' => array( 'http://blip.tv/oembed/',false )
        );
    }
}

?>