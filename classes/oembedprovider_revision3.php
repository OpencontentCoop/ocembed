<?php

class oembedprovider_revision3 implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            'https?://revision3.com/*'                        => array( 'http://revision3.com/api/oembed/',         false )
        );
    }
}

?>