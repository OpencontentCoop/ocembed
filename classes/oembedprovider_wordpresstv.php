<?php

class oembedprovider_wordpresstv implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'https?://wordpress.tv/*' => array( 'http://wordpress.tv/oembed/', false )
        );
    }
}

?>