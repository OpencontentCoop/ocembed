<?php

class OCEmbed
{
	public $handlers = array();	
    public $separator;
    public $args = array();
    public $links = array();

    protected static $cachedData;
    
	function __construct()
    {
		$ini = eZINI::instance( 'ocembed.ini' );
        $this->embed_defaults = (array)$ini->variable( 'OCEmbedSettings', 'EmbedDefaults' );
        $this->handlers = $ini->variable( 'OCEmbedSettings', 'CustomEmbedHandlers' );

        $data = eZSiteData::fetchByName( 'oembed_cached_data');
        if ( !$data instanceof eZSiteData ){
            $emptyData = json_encode(array());
            eZDB::instance()->query( "INSERT INTO ezsite_data ( name, value ) values( 'oembed_cached_data',  '$emptyData' )" );
            $data = eZSiteData::fetchByName( 'oembed_cached_data');
        }
        if ( $data instanceof eZSiteData ){
            self::$cachedData = $data;
        }
	}


	function run( $url = '', $args = array() )
    {
        $cacheKey = $this->getCacheKey($url, $args);
		if ( empty($url) )
			return '';

        $cacheData = $this->getCachedData($cacheKey);
        if ($cacheData)
            return $cacheData;

		$args = array_merge( $this->embed_defaults, $args );

		foreach ( $this->handlers as $handler )
        {
            
			if ( in_array( 'OCCustomEmbedHandlerInterface', class_implements($handler) ) )
			{
				if ( $regex = call_user_func( array( $handler, 'regex' ) ) )
                {                                                            
                    if ( preg_match( $regex, $url, $matches )  )
                    {
                        if ( false !== $result = call_user_func( array( $handler, 'callback' ), $matches, $url, $args ) ){
                            eZDebugSetting::writeNotice( 'ocembed', 'Autoembed has found url "' . $url . '" in ' . $handler, __METHOD__ );	
                            $this->setCachedData($cacheKey,$result);
                            return $result;
                        }
                    }
                }
			}
		}
       
        $oembed = new OCoEmbed;
        $result = $oembed->get_html( $url, $args );
        if ( $result )
        {
            eZDebugSetting::writeNotice( 'ocembed', 'Autoembed has found url "' . $url . '" in a OEmbed provider', __METHOD__ );	
            if ( !eZINI::instance( 'ocembed.ini' )->hasVariable( 'Settings', 'DisableFixHttps' ) )
            {
                $result = str_replace( 'http://', '//', $result );   
            }
            $this->setCachedData($cacheKey,$result);
            return $result;
        }

		// Still unknown
        eZDebugSetting::writeNotice( 'ocembed', 'Autoembed did not find url "' . $url . '"', __METHOD__ );	
		return array( $this->maybe_make_link( $url ) );
	}

    protected static function isCacheEnabled()
    {
        if ( eZINI::instance( 'ocembed.ini' )->hasVariable('OCEmbedSettings', 'Cache') ){
            return eZINI::instance( 'ocembed.ini' )->variable('OCEmbedSettings', 'Cache') == 'enabled';
        }
        return false;
    }


    protected function getCacheKey($url, $args)
    {
        $url = rtrim( $url, '/' );
        ksort($args);
        $argString = json_encode($args);
        return md5( $url . $argString );
    }

	protected function getCachedData( $cacheKey )
	{
        if ( self::isCacheEnabled() && self::$cachedData instanceof eZSiteData ){
            $cachedData = json_decode( self::$cachedData->attribute( 'value' ), 1);
            if ( isset( $cachedData[$cacheKey] ) )
            {
                eZDebugSetting::writeNotice(
                    'ocembed',
                    'Autoembed found url in a OEmbed cache',
                    __METHOD__
                );

                return $cachedData[$cacheKey];
            }
        }
        return null;
	}

    public static function clearCachedData()
    {
        $data = eZSiteData::fetchByName( 'oembed_cached_data');
        if ( $data instanceof eZSiteData ){
            $emptyData = json_encode(array());
            $data->setAttribute( 'value', $emptyData );
            $data->store();
        }
    }

	protected function setCachedData( $cacheKey, $data ){
        if ( self::isCacheEnabled() && self::$cachedData instanceof eZSiteData ){
            $cachedData = json_decode( self::$cachedData->attribute( 'value' ), 1);
            $cachedData[$cacheKey] = $data;
            self::$cachedData->setAttribute( 'value', json_encode($cachedData) );
            self::$cachedData->store();
        }
	}

	function autoembed( $content, $separator = "\n", $args = array(), $search = false )
    {
        $this->links = array();
		$this->separator = $separator;
        $this->args = $args;
                
        //http://regexadvice.com/forums/thread/48395.aspx
        preg_match_all(
            '#<a\s
              (?:(?= [^>]* href="   (?P<href>  [^"]*) ")|)
              (?:(?= [^>]* title="  (?P<title> [^"]*) ")|)
              (?:(?= [^>]* target=" (?P<target>[^"]*) ")|)
              [^>]*>
              (?P<text>[^<]*)
              </a>
            #xi',
            $content,
            $matches,
            PREG_SET_ORDER
          );
        
        $links = array();
		
        foreach( $matches as $m )
        {
            if ( strpos( $m['href'], '/') > 1 )
            {
                $this->links[$m['href']] = $m;
                $links[] = $m['href'];
            }
        }
		
		unset( $matches );
		$pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
        preg_match_all( $pattern, $content, $matches );
		
		if ( isset( $matches[0] ) )
		{
			foreach( $matches[0] as $m )
			{
				if ( ( strpos( $m, '/') > 1 ) && !array_key_exists( $m, $this->links ) )
				{
					$this->links[$m] = $m;
					$links[] = $m;
				}
			}
		}
		
		foreach( $this->links as $m )
		{
			if ( is_array( $m ) )
			{
				$content = str_replace( $m[0], $this->autoembed_callback( array( $m['href'] ) ), $content );
			}
			else
			{
				$content = str_replace( $m, $this->autoembed_callback( array( $m ) ), $content );
			}
		}
		
        if ( $search )
            return $links;
		else
			return $content;
			
        //$pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
        //return preg_replace_callback( $pattern, array(&$this, 'autoembed_callback'), $content );
	}

	function autoembed_callback( $match )
    {
		$return = $this->run( $match[0], $this->args );
		
        if ( is_array( $return ) )
		{
            return $return[0];
		}
		
		if ( is_array( $this->separator ) )
        {
            $open = $this->separator[0];
            $close = $this->separator[1];
            return "$open$return$close";
        }
        else
            return "$this->separator$return$this->separator";
	}

	/**
	 * Conditionally makes a hyperlink based on an internal class variable.
	 *
	 * @param string $url URL to potentially be linked.
	 * @return string Linked URL or the original URL.
	 */
	function maybe_make_link( $url )
    {	
		if ( array_key_exists( $url, $this->links ) )
		{
			if ( is_array( $this->links[$url] ) )
			{
				$output = $this->links[$url][0];
			}
			else
			{
				$output = $this->links[$url];
			}
		}
		else
		{
			$output = $url; 
		}
		return $output;
	}
    
    function search_embed( $content, $separator = "\n", $args = array() )
    {
        return $this->autoembed( $content, "\n", array(), true );
    }
}