<?php

class oembedprovider_qik implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'https?://qik.com/*'                              => array( 'http://qik.com/api/oembed.{format}',       false )
        );
    }
}

?>