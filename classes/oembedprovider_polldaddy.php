<?php

class oembedprovider_polldaddy implements oEmbedProviderInterface
{
    static public function definition()
    {
        return array(
            '#http://(answers|surveys)\.polldaddy.com/.*#i' => array( 'http://polldaddy.com/oembed/', true  )
        );
    }
}

?>