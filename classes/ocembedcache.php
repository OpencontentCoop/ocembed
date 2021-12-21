<?php

class OCEmbedCache
{
    const CACHE_KEY = 'oembed_cache';

    protected static $cacheStorage;

    protected static $cachedData;

    public static function clearCache()
    {
        self::clearCachedData();
        eZContentCacheManager::clearAllContentCache();
    }

    public static function clearCachedData()
    {
        $data = self::getCacheStorage();
        $emptyData = json_encode([]);
        $data->setAttribute('value', $emptyData);
        $data->store();

        $data = eZSiteData::fetchByName('oembed_cached_data');
        if ($data instanceof eZSiteData){
            $data->remove();
        }
    }

    protected static function getCacheStorage()
    {
        if (self::$cacheStorage === null) {
            $data = eZSiteData::fetchByName(self::CACHE_KEY);
            if (!$data instanceof eZSiteData) {
                $emptyData = json_encode([]);
                $cacheKey = self::CACHE_KEY;
                eZDB::instance()->query("INSERT INTO ezsite_data ( name, value ) values( '$cacheKey',  '$emptyData' )");
                $data = eZSiteData::fetchByName(self::CACHE_KEY);
            }
            self::$cacheStorage = $data;
        }

        return self::$cacheStorage;
    }

    public static function getCacheKey($url)
    {
        $url = rtrim($url, '/');
        return md5($url);
    }

    public static function getCachedData($cacheKey)
    {
        if (self::isCacheEnabled() && self::getCacheStorage() instanceof eZSiteData) {
            $cachedData = json_decode(self::getCacheStorage()->attribute('value'), 1);
            if (isset($cachedData[$cacheKey])) {
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

    public static function setCachedData($cacheKey, $data)
    {
        if (self::isCacheEnabled() && self::getCacheStorage() instanceof eZSiteData) {
            $cachedData = json_decode(self::getCacheStorage()->attribute('value'), 1);
            $cachedData[$cacheKey] = $data;
            self::getCacheStorage()->setAttribute('value', json_encode($cachedData));
            self::getCacheStorage()->store();
        }
    }

    public static function isCacheEnabled()
    {
        if (eZINI::instance('ocembed.ini')->hasVariable('OCEmbedSettings', 'Cache')) {
            return eZINI::instance('ocembed.ini')->variable('OCEmbedSettings', 'Cache') == 'enabled';
        }
        return false;
    }
}
