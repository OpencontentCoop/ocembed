<?php

class oembedprovider_issuu implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?issuu\.com/.+/docs/.+#i'              => array( 'https://issuu.com/oembed?url=',   true  )
        );
    }
}

?>