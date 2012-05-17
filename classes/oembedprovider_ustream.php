<?php

class oembedprovider_ustream implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(            
            '#http://(www\.)?ustream.tv/*#i' => array( 'http://api.embed.ly/v1/api/oembed', true )
        );
    }
}

?>