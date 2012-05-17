<?php

class oembedprovider_scribd implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(www\.)?scribd\.com/.*#i' => array( 'http://www.scribd.com/services/oembed', true  )
        );
    }
}

?>