<?php

class oembedprovider_photobucket implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'http://i*.photobucket.com/albums/*'            => array( 'http://photobucket.com/oembed',            false ),
            'http://gi*.photobucket.com/groups/*'           => array( 'http://photobucket.com/oembed',            false )
        );
    }
}

?>