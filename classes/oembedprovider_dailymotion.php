<?php

class oembedprovider_dailymotion implements oEmbedProviderInterface
{
    static public function definition()
    {
        //'#http://(www\.)?dailymotion\.virgilio\.it/.video/.*#i'         => array( 'http://www.dailymotion.com/api/oembed',    true  )             
        return array(
            '#http://(www\.)?dailymotion\.com/.*#i' => array( 'http://www.dailymotion.com/api/oembed',    true  ),
            'http://dailymotion.virgilio.it/video/*' => array( 'http://www.dailymotion.com/api/oembed', false )
        );
    }
}

?>