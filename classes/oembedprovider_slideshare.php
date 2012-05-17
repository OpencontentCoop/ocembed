<?php

class oembedprovider_slideshare implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(            
            '#http://(www\.)?slideshare.net/*#i' => array( 'http://www.slideshare.net/api/oembed/1', true )
        );
    }
}

?>