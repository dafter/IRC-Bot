<?php
return array(
    'server'   => 'irc.freenode.org',
    'serverPassword' => '',
	'masterPassword' => '',
    'port'     => 6667,
    'name'     => 'phpbot',
    'nick'     => 'phpbot',
    'channels' => array(
        '#phpbot404' => '',
    ),
    'max_reconnects' => 1,
    'log_file'       => 'log.txt',
    'commands'       => array(
        'Command\Say'     => array(),
        'Command\Weather' => array(
            'yahooKey' => 'a',
        ),
        'Command\Joke'    => array(),
        'Command\Ip'      => array(),
        'Command\Imdb'    => array(),
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
);