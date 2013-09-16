<?php

class oembedprovider_sample implements oEmbedProviderInterface
{
    static public function definition()
    {

    /*
        * @param string $format The format of URL that this provider can handle. You can use asterisks as wildcards.
        * @param string $provider The URL to the oEmbed provider.
        * @param boolean $regex Whether the $format parameter is in a regex format.        
                
        return array(            
            $format => array( $provider, $regex  )
        );        
    */
        return;
    }
}

?>