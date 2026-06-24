<?php

namespace App\Services;

class UrlService
{
    /**
     * Check if the URL contains a Facebook identifier.
     *
     * @param string $url
     * @return bool
     */
    public function isFacebook(string $url): bool
    {
        return strpos($url, 'fbclid') !== false;
    }

    /**
     * Check if the URL contains a TikTok identifier.
     *
     * @param string $url
     * @return bool
     */
    public function isTiktok(string $url): bool
    {
        return strpos($url, 'tiktok') !== false;
    }

    /**
     * Check if the URL contains a Zalo identifier.
     *
     * @param string $url
     * @return bool
     */
    public function isZalo(string $url): bool
    {
        return strpos($url, 'zalo') !== false;
    }

    /**
     * Check if the URL contains a Google Ads identifier.
     *
     * @param string $url
     * @return bool
     */
    public function isGoogle(string $url): bool
    {
        return (bool)preg_match('/Adw|Ads|Pmax|Adw_Search|adw|Pmax2/i', $url);
    }

    /**
     * Check if the URL belongs to organic search results.
     *
     * @param string $url
     * @return bool
     */
    public function isOrganic(string $url): bool
    {
        return strpos($url, 'https://www.xevipnoibai.com/bang-gia-taxi-xe-vip-noi-bai/') !== false ||
            strpos($url, 'https://www.xevipnoibai.com/') !== false;
    }
}
