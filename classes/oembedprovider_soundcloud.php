<?php

class oembedprovider_soundcloud implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?soundcloud.com.*#i'  => array( 'http://soundcloud.com/oembed', true  )
        );
    }
}

?>