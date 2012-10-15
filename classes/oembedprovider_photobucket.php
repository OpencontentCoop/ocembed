<?php

class oembedprovider_photobucket implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'https?://i*.photobucket.com/albums/*'            => array( 'http://photobucket.com/oembed',            false ),
            'https?://gi*.photobucket.com/groups/*'           => array( 'http://photobucket.com/oembed',            false )
        );
    }
}

?>