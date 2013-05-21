<?php
// Namespace
namespace Command;

/**
 * Returns the current hashrate of selected LTC pools to the channel.
 *
 * @package Daftbot
 * @subpackage Command
 * @author Tristan van Bokkem <tristanvanbokkem@gmail.com>
 */
class Pools extends \Library\IRC\Command\Base {
    /**
     * The command's help text.
     *
     * @var string
     */
    protected $help = '!pools';

    /**
     * Sends the arguments to the channel.
     * The hashrate of the LTC pools.
     *
     * IRC-Syntax: PRIVMSG [#channel]or[user] : [message]
     */
    public function command() {
        $pool = new \Helper\Litecoin\Pool;
        $poolHash = $pool->getPoolHashrate();

        if ( $poolHash ) {
            $this->say( $poolHash );
        } else {
            $this->say( 'Unable to query pool hashrate.' );
        }
    }
}