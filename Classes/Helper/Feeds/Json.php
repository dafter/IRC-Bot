<?php

// File inside Classes/Helper/Litecoin/

// Namespace
namespace Helper\Feeds;

/**
 * Returns the current hashrate of selected LTC pools to the channel.
 *
 * @package Daftbot
 * @subpackage Command
 * @author Tristan van Bokkem <tristanvanbokkem@gmail.com>
 */
class Json {

        /**
     * Fetches JSON data from $uri
     *
     * @param string $uri
     * @return string
     * @author Tristan van Bokkem <tristanvanbokkem@gmail.com>
     */
    public static function GetJsonFeed($json_url)
    {
        $feed = file_get_contents($json_url);
        return json_decode($feed, true);
    }
}