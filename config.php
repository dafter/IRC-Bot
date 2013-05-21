<?php
return array(
    'server'   => 'irc.freenode.org',
    'serverPassword' => '',
    'port'     => 6667,
    'name'     => 'MyDaftbot',
    'nick'     => 'MyDaftbot',
    'adminPassword' => '',
    'commandPrefix' => '!',
    'channels' => array(
        '#mypoolchannel' => '',
    ),
    'max_reconnects' => 1,
    'log_file'       => 'daftbot.',
    'commands'       => array(
        'Command\Say'     => array(),
        'Command\Joke'    => array(),
        'Command\Ip'      => array(),
        'Command\Poke'    => array(),
        'Command\Join'    => array(),
        'Command\Part'    => array(),
        'Command\Timeout' => array(),
        'Command\Quit'    => array(),
        'Command\Restart' => array(),
        'Command\Serialise' => array(),
        'Command\Remember'  => array(),
    ),
    'listeners' => array(
        'Listener\Joins' => array(),
    ),
    'rpc' => array(
        'protocol' => 'litecoind_protocol',     // http or https.
        'user' => 'litecoind_rpcuser',          // As defined in .litecoin/litecoin.conf (rpcuser).
        'password' => 'litecoind_rpcpassword',  // As defined in .litecoin/litecoin.conf (rpcpassword).
        'host' => 'litecoind_host/ip',          // The hostname or IP of the server litecoind is running at.
        'port' => 'litecoind_rpcport',          // As defined in .litecoin/litecoin.conf (rpcport).
    ),
    'pools' => array(
        /*'Pool Name' => array (
            'Pool API URL',
            'JSON key for hashrate'), */
        // For example:
        'daftpool.com' => array(
            'http://daftpool.com/api',
            'hashrate'),
    ),
);
