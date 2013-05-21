<?php

/**
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @license http://creativecommons.org/licenses/by/3.0/
 *
 * @package Wildbot
 * @subpackage Library
 * @author Daniel Siepmann <coding.layne@me.com>
 *
 * @filesource
 */
namespace Library\IRC\Command;

/**
 * An IRC command.
 *
 * @package Daftbot
 * @subpackage Library
 * @author Daniel Siepmann <daniel.siepmann@me.com>
 */
abstract class Base extends \Library\IRC\Base {
    /**
     * The number of arguments the command needs.
     * By default no arguments are to be given.
     * You have to define this in the command.
     *
     * @var integer
     */
    protected $numberOfArguments = 0;

    /**
     * The help string, shown to the user if he calls the command with wrong
     * parameters.
     *
     * You have to define this in the command.
     *
     * @var string
     */
    protected $help = '';

    /**
     * Require admin, set to true if only admin may execute this.
     * @var boolean
     */
    protected $requireAdmin = false;

    /**
     * Returns whether admin is required for this command or not
     * @var boolean
     */
    public function requiresAdmin() {
        return $this->requireAdmin;
    }

    /**
     * Executes the command.
     *
     * @param array $arguments
     * @param string $source
     * @param string $data
     */
    public function executeCommand() {
        // If a number of arguments is incorrect then run the command, if
        // not then show the relevant help text.
        if ( $this->requireAdmin && !$this->getInfo()->is_admin ) {
            return;
        } elseif ( $this->numberOfArguments != -1 && count( $this->arguments ) != $this->numberOfArguments ) {
            // Show help text.
            $this->say( ' Incorrect Arguments. Usage: ' . $this->getHelp() );
        } else {
            // Execute the command.
            $this->command();
        }
    }

    /**
     * Get the help string
     *
     * @return string
     */
    private function getHelp() {
        return $this->help;
    }

    /**
     * Returns requesting user IP
     *
     * @return string
     */
    protected function getUserIp() {
        // catches from @ to first space
        if ( preg_match( '/@([a-z0-9.-]*) /i', $this->data, $match ) === 1 ) {
            $hostname = $match[1];
            $ip = gethostbyname( $hostname );
            // did we really get an IP
            if ( preg_match( '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $ip ) === 1 ) {
                return $ip;
            }
        }
        return null;
    }

    protected function getPoolHashrate() {

        if (class_exists('BitcoinClient'))
        {
            $this->lcd = new BitcoinClient("http", "user", "pass", "localhost", 9332);
        }
        else
        {
            require ('BitcoinClient.php');
            $this->lcd = new BitcoinClient("http", "user", "pass", "localhost", 9332);
        }

        $hash = trim(str_replace(",", ".", $args["arg1"]));
        $net_hashrate = $this->lcd->getnetworkhashps() / 1000000;
        $net_hashrate_new = number_format($net_hashrate, 2, '.', '');
        $Pool_X = GetJsonFeed("http://pool-x.eu/api");
        $Pool_X_hashrate = number_format($Pool_X["hashrate"] / 1000, 2);
        $data[] = array('Pool' => 'PooL-X', 'hashrate' => $Pool_X_hashrate);

        $notroll = GetJsonFeed("http://www.notroll.in/api.php");
        $notroll_hashrate = number_format($notroll["hashrate"] / 1000, 2);
        $data[] = array('Pool' => 'Notroll.in', 'hashrate' => $notroll_hashrate);

        $kattare = GetJsonFeed("https://ltc.kattare.com/api.php?api_key=da6e3c588a8b29e3f31517030cdf38031ed24ace77ef18dccc9bcc0043f32dd9");
        $kattare_hashrate = number_format($kattare["pool_hashrate"] / 1000, 2);
        $data[] = array('Pool' => 'kattare', 'hashrate' => $kattare_hashrate);

        $litecoinpool = GetJsonFeed("http://www.litecoinpool.org/api");
        $litecoinpool_hashrate = number_format($litecoinpool["pool"]["hash_rate"] / 1000, 2);
        $data[] = array('Pool' => 'Litecoinpool', 'hashrate' => $litecoinpool_hashrate);

        $ozco = GetJsonFeed("https://lc.ozco.in/api.php");
        $ozco_hashrate = number_format($ozco["hashrate"] / 1000, 2);
        $data[] = array('Pool' => 'Ozco', 'hashrate' => $ozco_hashrate);

        $p2p = GetJsonFeed("http://ltcfaucet.com:9327/global_stats");
        $p2p_hashrate = number_format($p2p["pool_hash_rate"] / 1000000, 2);
        $data[] = array('Pool' => 'P2Pool', 'hashrate' => $p2p_hashrate);

        $xurious = GetJsonFeed("http://ltc.xurious.com/api");
        $xurious_hashrate = number_format($xurious["pool"]["hash_rate"] / 1000000, 2);
        $data[] = array('Pool' => 'Xurious', 'hashrate' => $xurious_hashrate);

        $nushor = GetJsonFeed("http://ltc.nushor.net/api.php");
        $nushor_hashrate = number_format($nushor["hashrate"] / 1000, 2);
        $data[] = array('Pool' => 'Nushor', 'hashrate' => $nushor_hashrate);

        $Coinotron = GetJsonFeed("https://www.coinotron.com/coinotron/AccountServlet?action=api");
        $Coinotron_hashrate = number_format($Coinotron[2]["hashrate"] / 1000000, 2);
        $data[] = array('Pool' => 'Coinotron', 'hashrate' => $Coinotron_hashrate);

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
    /**
     * Overwrite this method for your needs.
     * This method is called if the command get's executed.
     */
    abstract public function command();
}
