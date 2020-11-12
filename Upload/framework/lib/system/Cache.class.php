<?php

class Cache {
    /**
     * Bereits geladene Cache Items
     * @var array
     */
    private $loadedCacheItems = [

    ];

    /**
     * Ist der Cache deaktiviert? (Liefert nur null und setzt keine DB Anfragen ab)
     * @var bool
     */
    private $cacheDisabled = false;

    public function __construct() {}

    /**
     * Deaktiviert den Cache programmatisch.
     */
    public function disableCache() {
        $this->cacheDisabled = true;
    }

    /**
     * Ist das Cache Item gesetzt?
     * @param $cacheKey
     * @return bool
     */
    public function isItemSet($cacheKey) {
        if($this->cacheDisabled) return false;

        $item = $this->loadItem($cacheKey);

        if($item != null) return true;
        else return false;
    }

    /**
     * Ist Caching aktiviert?
     * @return bool
     */
    public function isCacheEnabled() {
        return !$this->cacheDisabled;
    }

    /**
     * Lädt den Inhalt als Objekt. (Muss als Objekt abgelegt worden sein.)
     * @param $cacheKey
     * @return object|null
     */
    public function getAsObject($cacheKey) {
        if($this->cacheDisabled) return null;

        $cacheItem = $this->loadItem($cacheKey);
        if($cacheItem != null) {
            if($cacheItem['cacheObject'] != null) return $cacheItem['cacheObject'];
        }

        return null;
    }

    /**
     * Lädt den Inhalt als Text.
     * @param $cacheKey
     * @return string|null
     */
    public function getAsText($cacheKey) {
        if($this->cacheDisabled) return null;

        $cacheItem = $this->loadItem($cacheKey);
        if($cacheItem != null) {
            if($cacheItem['cacheText'] != null) return $cacheItem['cacheText'];
        }

        return null;
    }

    /**
     * Lädt den Inhalt von der base64 Repräsentation. (z.B. für Grafiken)
     * @param $cacheKey
     * @return mixed|null
     */
    public function getFromBase64($cacheKey) {
        if($this->cacheDisabled) return null;

        $cacheItem = $this->loadItem($cacheKey);
        if($cacheItem != null) {
            if($cacheItem['cacheBase64'] != null) return $cacheItem['cacheBase64'];
        }

        return null;
    }


    /**
     * @param $cacheKey
     * @param $cacheObject
     * @param int $ttl
     */
    public function storeObject($cacheKey, $cacheObject, $ttl = 0) {
        if($this->isItemSet($cacheKey)) $this->forgetItem($cacheKey);
        $this->storeCacheData($cacheKey, 'object', serialize($cacheObject), $ttl);
    }

    /**
     * @param $cacheKey
     * @param $cacheText
     * @param int $ttl
     */
    public function storeText($cacheKey, $cacheText, $ttl = 0) {
        if($this->isItemSet($cacheKey)) $this->forgetItem($cacheKey);
        $this->storeCacheData($cacheKey, 'text', $cacheText, $ttl);
    }

    /**
     * @param $cacheKey
     * @param $cacheObject
     * @param int $ttl
     */
    public function storeAsBase64($cacheKey, $cacheObject, $ttl = 0) {
        if($this->isItemSet($cacheKey)) $this->forgetItem($cacheKey);
        $this->storeCacheData($cacheKey, 'base64', base64_encode($cacheObject), $ttl);
    }


    /**
     * Cache Item entfernen.
     * @param $cacheKey
     */
    public function forgetItem($cacheKey) {
        if($this->cacheDisabled) return;

        $this->loadedCacheItems[$cacheKey] = null;

        DB::getDB()->query("DELETE FROM cache WHERE cacheKey='" . DB::getDB()->escapeString($cacheKey) . "'");
    }

    /**
     * Läd das Cache Objekt
     * @param $cacheKey
     * @return array|null
     */
    private function loadItem($cacheKey) {
        if(isset($this->loadedCacheItems[$cacheKey])) {
            return $this->loadedCacheItems[$cacheKey];
        }

        $item = DB::getDB()->query_first("SELECT * FROM cache WHERE cacheKey='" . DB::getDB()->escapeString($cacheKey) . "'");

        if($item['cacheKey'] != "") {

            $cacheItem = [
                'cacheKey' => $cacheKey,
                'cacheType' => $item['cacheType'],
                'cacheObject' => null,
                'cacheText' => null,
                'cacheBase64' => null
            ];

            if($item['cacheType'] == 'object') $cacheItem['cacheObject'] = unserialize($item['cacheData']);
            if($item['cacheType'] == 'text') $cacheItem['cacheText'] = $item['cacheData'];
            if($item['cacheType'] == 'base64') $cacheItem['cacheBase64'] = base64_decode($item['cacheData']);

            $this->loadedCacheItems[$cacheKey] = $cacheItem;

            return $cacheItem;
        }

        return null;
    }

    private function storeCacheData($cacheKey, $cacheType, $cacheData, $cacheTTL) {
        if($this->cacheDisabled) return;

        $ttl = 0;

        if($cacheTTL > 0) {
            $ttl = time() + $cacheTTL;
        }

        DB::getDB()->query("INSERT INTO cache (
            cacheKey,
            cacheTTL,
            cacheType,
            cacheData
        )
        values (
                '" . DB::getDB()->escapeString($cacheKey) . "',
                '" . DB::getDB()->escapeString($ttl) . "',
                '" . DB::getDB()->escapeString($cacheType) . "',
                '" . DB::getDB()->escapeString($cacheData) . "'
        )");
    }

}