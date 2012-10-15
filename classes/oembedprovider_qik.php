<?php

class oembedprovider_qik implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'http://qik.com/*'                              => array( 'http://qik.com/api/oembed.{format}',       false )
        );
    }
}

?>