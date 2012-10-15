<?php

class oembedprovider_vimeo implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?vimeo\.com/.*#i' => array( 'http://vimeo.com/api/oembed.{format}', true  )
        );
    }
}

?>