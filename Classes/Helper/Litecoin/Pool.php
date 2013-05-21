<?php

// File inside Classes/Helper/Litecoin/

// Namespace
namespace Helper\Litecoin;
require_once ('External/Bitcoin/bitcoin.inc.php');

/**
 * Returns the current hashrate of selected LTC pools to the channel.
 *
 * @package Daftbot
 * @subpackage Command
 * @author Tristan van Bokkem <tristanvanbokkem@gmail.com>
 */
class Pool {

    public function getPoolHashrate() {

        $rpcDetails = \Daftbot::get('config')->rpc;
        $this->lcd = new \BitcoinClient($rpcDetails[protocol], $rpcDetails[user], $rpcDetails[password], $rpcDetails[host], $rpcDetails[port]);

        $hash = trim(str_replace(",", ".", $args["arg1"]));
        $net_hashrate = $this->lcd->query("getnetworkhashps") / 1000000;
        $net_hashrate_new = number_format($net_hashrate, 2, '.', ',`');

        $pools = \Daftbot::get('config')->pools;

        foreach ($pools as $key => $pool)
        {
            $poolName = $key;
            $poolFeed = \Helper\Feeds\Json::GetJsonFeed($pool[0]);
            $poolName_hashrate = number_format($poolFeed[$pool[1]] / 1000, 2);
            $data[] = array('Pool' => $poolName, 'hashrate' => $poolName_hashrate);
        }

        foreach ($data as $key => $row) {
            $_hasrate[$key] = $row['hashrate'];
        }
        array_multisort($_hasrate, SORT_DESC, $data);

        foreach ($data as $key => $row) {
            $echo_string .= $row['Pool'] . " " . $row['hashrate'] . " Mh/s - ";

        }
        $echo_string .= "Network: " . $net_hashrate_new . " Mh/s";

        if ($echo_string) {
            return $echo_string;
        }
        return null;
    }
}