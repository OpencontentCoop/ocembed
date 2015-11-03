<?php

class oembedprovider_twitter implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i' => array( 'https://api.twitter.com/1/statuses/oembed.{format}', true  )
        );
    }
}

?>
