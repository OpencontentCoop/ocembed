<?php

class OCoEmbed
{
    public $providers = [];

    function __construct()
    {
        // List out some popular sites that support oEmbed.
        $ini = eZINI::instance('ocembed.ini');
        $providers = $ini->variable('OCEmbedSettings', 'oEmbedProviders');
        foreach ($providers as $provider) {
            if (in_array('oEmbedProviderInterface', class_implements('oembedprovider_' . $provider))) {
                $new_provider = call_user_func(['oembedprovider_' . $provider, 'definition']);
                if (is_array($new_provider)) {
                    $this->providers = array_merge($this->providers, $new_provider);
                }
            }
        }
        $this->embed_defaults = $ini->variable('OCEmbedSettings', 'EmbedDefaults');
    }

    function get_html($url, $args = [], $asObject = false, $useFilter = false, $separator = false)
    {
        $cacheKey = OCEmbedCache::getCacheKey($url);

        $cacheData = OCEmbedCache::getCachedData($cacheKey);

        if (!$cacheData) {
            eZDebug::createAccumulator('get_html ' . $url, 'OCoembed', $url);
            eZDebug::accumulatorStart('get_html ' . $url, 'OCoembed', $url, false);
            $provider = false;
            if (!isset($args['discover']))
                $args['discover'] = false;

            foreach ($this->providers as $matchmask => $data) {
                [$providerurl, $regex] = $data;

                // Turn the asterisk-type provider URLs into regex
                if (!$regex)
                    $matchmask = '#' . str_replace('___wildcard___', '(.+)', preg_quote(str_replace('*', '___wildcard___', $matchmask), '#')) . '#i';

                if (preg_match($matchmask, $url)) {
                    $provider = str_replace('{format}', 'json', $providerurl); // JSON is easier to deal with than XML
                    break;
                }
            }

            if (!$provider && $args['discover'])
                $provider = $this->discover($url);

            if (!$provider || false === $data = $this->fetch($provider, $url, $args)) {
                eZDebug::accumulatorStop('get_html ' . $url, false);
                return false;
            }
            OCEmbedCache::setCachedData($cacheKey, $data);
            eZDebug::accumulatorStop('get_html ' . $url, false);
        }else{
            $data = (object)$cacheData;
        }

        if ($asObject) {
            if (isset($data->html)){
                $html = $this->data2html($data, $url, $useFilter);
                if (is_array($separator)) {
                    $open = $separator[0];
                    $close = $separator[1];
                    $html = "$open$html$close";
                } elseif ($separator) {
                    $html = "$separator$html$separator";
                }
                $data->html = $html;
            }
            // youtube preview resolution workaround
            if (isset($data->thumbnail_url) && $data->type == 'video' && $data->provider_name == 'YouTube'){
                $maxResThumb = str_replace('hqdefault', 'maxresdefault', $data->thumbnail_url);
                if (eZHTTPTool::getDataByURL($maxResThumb, true)) {
                    $data->thumbnail_url = $maxResThumb;
                }
            }

            return $data;
        }

        return $this->data2html($data, $url, $useFilter);
    }

    function discover($url)
    {
        $providers = [];
        // Fetch URL content
        if (!self::getDataByUrl($url, true)) {
            return $providers;
        }
        if ($html = self::getDataByUrl($url)) {

            // <link> types that contain oEmbed provider URLs
            $linktypes = [
                'application/json+oembed' => 'json',
                'text/xml+oembed' => 'xml',
                'application/xml+oembed' => 'xml', // Incorrect, but used by at least Vimeo
            ];

            // Strip <body>
            $html = substr($html, 0, stripos($html, '</head>'));

            // Do a quick check
            $tagfound = false;
            foreach ($linktypes as $linktype => $format) {
                if (stripos($html, $linktype)) {
                    $tagfound = true;
                    break;
                }
            }

            if ($tagfound && preg_match_all('/<link([^<>]+)>/i', $html, $links)) {
                foreach ($links[1] as $link) {
                    $atts = $this->_shortcode_parse_atts($link);

                    if (!empty($atts['type']) && !empty($linktypes[$atts['type']]) && !empty($atts['href'])) {
                        $providers[$linktypes[$atts['type']]] = $atts['href'];

                        // Stop here if it's JSON (that's all we need)
                        if ('json' == $linktypes[$atts['type']])
                            break;
                    }
                }
            }
        }

        // JSON is preferred to XML
        if (!empty($providers['json']))
            return $providers['json'];
        elseif (!empty($providers['xml']))
            return $providers['xml'];
        else
            return false;
    }

    static function getDataByURL($url, $justCheckURL = false, $userAgent = false)
    {
        $justCheckURL = false; //workaround per 404 su youtube

        // First try CURL
        if (extension_loaded('curl')) {
            $ch = curl_init($url);
            // Options used to perform in a similar way than PHP's fopen()
            curl_setopt_array(
                $ch,
                [
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]
            );
            if ($justCheckURL) {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                curl_setopt($ch, CURLOPT_NOBODY, 1);
            } else {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            }

            if ($userAgent) {
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            }

            $ini = eZINI::instance();
            $proxy = $ini->hasVariable('ProxySettings', 'ProxyServer') ? $ini->variable('ProxySettings', 'ProxyServer') : false;
            // If we should use proxy
            if ($proxy) {
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                $userName = $ini->hasVariable('ProxySettings', 'User') ? $ini->variable('ProxySettings', 'User') : false;
                $password = $ini->hasVariable('ProxySettings', 'Password') ? $ini->variable('ProxySettings', 'Password') : false;
                if ($userName) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$userName:$password");
                }
            }
            // If we should check url without downloading data from it.
            if ($justCheckURL) {
                if (!curl_exec($ch)) {
                    curl_close($ch);
                    return false;
                }

                curl_close($ch);
                return true;
            }
            // Getting data
            ob_start();
            if (!curl_exec($ch)) {
                curl_close($ch);
                ob_end_clean();
                return false;
            }

            curl_close($ch);
            $data = ob_get_contents();
            ob_end_clean();

            return $data;
        }

        if ($userAgent) {
            ini_set('user_agent', $userAgent);
        }

        // Open and read url
        $fid = fopen($url, 'r');
        if ($fid === false) {
            return false;
        }

        if ($justCheckURL) {
            if ($fid)
                fclose($fid);

            return $fid;
        }

        $data = "";
        do {
            $dataBody = fread($fid, 8192);
            if (strlen($dataBody) == 0)
                break;
            $data .= $dataBody;
        } while (true);

        fclose($fid);
        return $data;
    }

    function _shortcode_parse_atts($text)
    {
        $atts = [];
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    function fetch($provider, $url, $args = '')
    {
        $args = array_merge($this->embed_defaults, $args);

        $provider = $this->_add_query_arg('maxwidth', $args['width'], $provider);
        $provider = $this->_add_query_arg('maxheight', $args['height'], $provider);
        $provider = $this->_add_query_arg('url', $url, $provider);

        foreach (['json', 'xml'] as $format) {
            $result = $this->_fetch_with_format($provider, $format);
            return ($result) ? $result : false;
        }
    }

    function _add_query_arg($key, $value, $uri)
    {
        $ret = '';
        if ($frag = strstr($uri, '#'))
            $uri = substr($uri, 0, -strlen($frag));
        else
            $frag = '';

        if (preg_match('|^https?://|i', $uri, $matches)) {
            $protocol = $matches[0];
            $uri = substr($uri, strlen($protocol));
        } else {
            $protocol = '';
        }

        if (strpos($uri, '?') !== false) {
            $parts = explode('?', $uri, 2);
            if (1 == count($parts)) {
                $base = '?';
                $query = $parts[0];
            } else {
                $base = $parts[0] . '?';
                $query = $parts[1];
            }
        } elseif (!empty($protocol) || strpos($uri, '=') === false) {
            $base = $uri . '?';
            $query = '';
        } else {
            $base = '';
            $query = $uri;
        }

        parse_str($query, $qs);
        //array_walk($qs, 'urlencode');
        $qs[$key] = $value;
        foreach ((array)$qs as $k => $v) {
            if ($v === false)
                unset($qs[$k]);
            urlencode($v);
        }
        $ret = $this->_http_build_query($qs);
        $ret = trim($ret, '?');
        $ret = preg_replace('#=(&|$)#', '$1', $ret);
        $ret = $protocol . $base . $ret . $frag;
        $ret = rtrim($ret, '?');
        return $ret;
    }

    function _http_build_query($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
    {
        $ret = [];

        foreach ((array)$data as $k => $v) {
            if ($urlencode)
                $k = urlencode($k);
            if (is_int($k) && $prefix != null)
                $k = $prefix . $k;
            if (!empty($key))
                $k = $key . '%5B' . $k . '%5D';
            if ($v === NULL)
                continue;
            elseif ($v === FALSE)
                $v = '0';

            if (is_array($v) || is_object($v))
                array_push($ret, $this->_http_build_query($v, '', $sep, $k, $urlencode));
            elseif ($urlencode)
                array_push($ret, $k . '=' . urlencode($v));
            else
                array_push($ret, $k . '=' . $v);
        }

        if (NULL === $sep)
            $sep = ini_get('arg_separator.output');

        return implode($sep, $ret);
    }

    function _fetch_with_format($provider_url_with_args, $format)
    {
        $provider_url_with_args = $this->_add_query_arg('format', $format, $provider_url_with_args);
        if (!self::getDataByUrl($provider_url_with_args, true)) {
            return false;
        }

        $response = self::getDataByUrl($provider_url_with_args);

        eZDebugSetting::writeNotice('ocembed', $provider_url_with_args, __METHOD__);

        if (!$response)
            return false;
        $parse_method = "_parse_$format";
        return $this->$parse_method($response);
    }

    function data2html($data, $url, $useFilter = false)
    {
        if (!is_object($data) || empty($data->type))
            return false;

        switch ($data->type) {
            case 'photo':
                if (empty($data->url) || empty($data->width) || empty($data->height))
                    return false;

                $title = (!empty($data->title)) ? $data->title : '';
                $return = '<img src="' . $data->url . '" alt="' . $title . '" width="' . $data->width . '" height="' . $data->height . '" />';
                break;

            case 'video':
            case 'rich':
                $return = (!empty($data->html)) ? $data->html : false;
                if ($useFilter){
                    $return = ezpEvent::getInstance()->filter('oembed/html', $return, $url, $data);
                }
                break;

            case 'link':
                $return = (!empty($data->title)) ? '<a href="' . $url . '">' . $data->title . '</a>' : false;
                break;

            default;
                $return = false;
        }

        if (!eZINI::instance('ocembed.ini')->hasVariable('Settings', 'DisableFixHttps')) {
            $return = str_replace('http://', '//', $return);
        }

        // You can use this filter to add support for custom data types or to filter the result
        return $return;
    }

    // from php.net (modified by Mark Jaquith to behave like the native PHP5 function)

    function _parse_json($response_body)
    {
        return (($data = json_decode(trim($response_body))) && is_object($data)) ? $data : false;
    }

    function _parse_xml($response_body)
    {
        if (function_exists('simplexml_load_string')) {
            $errors = libxml_use_internal_errors('true');
            $data = simplexml_load_string($response_body);
            libxml_use_internal_errors($errors);
            if (is_object($data))
                return $data;
        }
        return false;
    }

    function _strip_newlines($html, $data, $url)
    {
        if (false !== strpos($html, "\n"))
            $html = str_replace(["\r\n", "\n"], '', $html);

        return $html;
    }

}

?>
