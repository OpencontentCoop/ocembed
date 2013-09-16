<?php

class oembedprovider_smugmug implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(.+)?smugmug\.com/.*#i'                => array( 'http://api.smugmug.com/services/oembed/',  true  )
        );
    }
}

?>