<?php

/**
 * WildPHP
 *
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @license    http://creativecommons.org/licenses/by/3.0/
 *
 * @package WildPHP
 * @subpackage Library
 *
 * @author Daniel Siepmann <coding.layne@me.com>
 */
namespace Library\IRC;

/**
 * A simple IRC Bot with basic features.
 *
 * @package Daftbot
 * @subpackage Library
 *
 * @author Super3 <admin@wildphp.com>
 * @author Daniel Siepmann <coding.layne@me.com>
 * @author Tristan van Bokkem <tristanvanbokkem@gmail.com>
 */
class Bot {

    /**
     * Holds the server connection.
     *
     * @var \Library\IRC\Connection
     */
    private $connection = null;

    /**
     * serverPassword
     *
     * @var string
     */
    private $serverPassword = '';

    /**
     * adminPassword
     *
     * @var string
     */
    private $adminPassword = '';

    /**
     * A list of all channels the bot should connect to.
     *
     * @var array
     */
    private $channel = array ();

    /**
     * The name of the bot.
     *
     * @var string
     */
    private $name = '';

    /**
     * The nick of the bot.
     *
     * @var string
     */
    private $nick = '';

    /**
     * The password of the bot.
     *
     * @var string
     */
    private $password = '';

    /**
     * The number of reconnects before the bot stops running.
     *
     * @var integer
     */
    private $maxReconnects = 0;

    /**
     * Complete file path to the log file.
     * Configure the path, the filename is generated and added.
     *
     * @var string
     */
    private $logFile = '';

    /**
     * The nick of the bot.
     *
     * @var string
     */
    private $nickToUse = '';

    /**
     * Defines the prefix for all commands interacting with the bot.
     *
     * @var String
     */
    private $commandPrefix = '!';

    /**
     * All of the messages both server and client
     *
     * @var array
     */
    private $ex = array ();

    /**
     * The nick counter, used to generate a available nick.
     *
     * @var integer
     */
    private $nickCounter = 0;

    /**
     * Contains the number of reconnects.
     *
     * @var integer
     */
    private $numberOfReconnects = 0;

    /**
     * All available commands.
     * Commands are type of IRCCommand
     *
     * @var array
     */
    private $commands = array ();

    /**
     * All available listeners.
     * Listeners are type of IRCListener
     *
     * @var array
     */
    private $listeners = array ();

    /**
     * All admins will be stored here
     *
     * @var array
     */
    private $admins = array ();

    /**
     * Holds the reference to the file.
     *
     * @var type
     */
    private $logFileHandler = null;
    private static $commandString = 'Command';
    private static $listenerString = 'Listener';
    private static $serialiseStrings = array (
            'Serialise End' => 'successfully serialised!',
            'Serialise End Fail' => 'didn\'t serialise!',
            'Remember End' => 'successfully remebered all it can!',
            'Remember End Fail' => 'could not remember anything!'
    );

    /**
     * Creates a new IRC Bot.
     *
     * @param array $configuration
     *            The whole configuration, you can use the setters, too.
     * @return void
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function __construct( ) {
        $this->connection = new \Library\IRC\Connection\Socket();
    }

    /**
     * Cleanup handlers.
     */
    public function __destruct() {
        if ( $this->logFileHandler ) {
            fclose( $this->logFileHandler );
        }
    }

    /**
     * Connects the bot to the server.
     *
     * @author Super3 <admin@wildphp.com>
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function connectToServer() {
        if ( empty( $this->nickToUse ) ) {
            $this->nickToUse = $this->nick;
        }

        if (empty( $this->passwordToUse )) {
            $this->passwordToUse = $this->password;
        }

        if ( $this->connection->isConnected() ) {
            $this->connection->disconnect();
        }

        $this->log( 'The following commands are known by the bot: "' . implode( ',', array_keys( $this->commands ) ) . '".', 'INFO' );
        $this->log( 'The following listeners are known by the bot: "' . implode( ',', array_keys( $this->listeners ) ) . '".', 'INFO' );

        $this->connection->connect();
        $this->sendDataToServer( 'PASS ' . $this->serverPassword );
        $this->sendDataToServer( 'NICK ' . $this->nickToUse );
        $this->sendDataToServer( 'USER ' . $this->nickToUse . ' daftpool.com ' . $this->nickToUse . ' :' . $this->name );
        $this->sendDataToServer( 'PRIVMSG NickServ :IDENTIFY ' . $this->passwordToUse );

        // Create Socket for Slave Process - NOT YET IMPLEMENTED BUT HERE NONE-THE-LESS //
        $botSocket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $socketBind = socket_bind($botSocket, "/tmp/bot.socket");
        socket_listen($botSocket);
        //////////////////////////////////////////////////////////////////////////////////


        $this->main();
    }

    /**
     * This is the workhorse function, grabs the data from the server and
     * displays on the browser
     *
     * @author Super3 <admin@wildphp.com>
     * @author Daniel Siepmann <coding.layne@me.com>
     * @author Hoshang Sadiq <superaktieboy@gmail.com>
     */
    private function main() {
        do {
            $command = '';
            $arguments = array ();
            $data = $this->connection->getData();

            // Get the response from irc:
            $args = explode( ' ', $data );
            $this->log( $data );

            if ( stripos( $data, 'PRIVMSG' ) === false ) {
                // Check for some special situations and react:
                if ( stripos( $data, 'Nickname is already in use.' ) !== false ) {
                    // The nickname is in use, create a now one using a counter
                    // and try again.
                    $this->nickToUse = $this->nick . ( ++$this->nickCounter );
                    $this->nick = $this->nickToUse;
                    $this->sendDataToServer( 'NICK ' . $this->nickToUse );
                }

                // We're welcome. Let's join the configured channel/-s.
                if ( stripos( $data, 'End of /MOTD command' ) !== false ) {
                    $this->join_channel( $this->channel );
                }

                // Something realy went wrong.
                if ( stripos( $data, 'Registration Timeout' ) !== false || stripos( $data, 'Erroneous Nickname' ) !== false || stripos( $data, 'Closing Link' ) !== false ) {
                    // If the error occurs to often, create a log entry and
                    // exit.
                    if ( $this->numberOfReconnects >= (int) $this->maxReconnects ) {
                        $this->log( 'Closing Link after "' . $this->numberOfReconnects . '" reconnects.', 'EXIT' );
                        exit();
                    }

                    // Notice the error.
                    $this->log( $data, 'CONNECTION LOST' );
                    // Wait before reconnect ...
                    sleep( 60 * 1 );
                    ++$this->numberOfReconnects;
                    // ... and reconnect.
                    $this->connection->connect();
                    return;
                }
            }

            // Play ping pong with server, to stay connected:
            if ( $args[0] == 'PING' ) {
                $this->sendDataToServer( 'PONG ' . $args[1] );
            }

            // Nothing new from the server, step over.
            if ( $args[0] == 'PING' || !isset( $args[1] ) ) {
                continue;
            }

            $this->args = $args;

            if($this->args[1] === 'PRIVMSG' && $this->args[2] == $this->getNick() && (count($this->args) == 4 || count($this->args) == 5)) {
                if($this->args[3] == ':'.$this->getCommandPrefix().'admin') {
                    if(isset($this->args[4]) && $this->adminPassword == trim($this->args[4])) {
                        $this->setAdmin($this->args[0]);
                    } else {
                        $this->removeAdmin($this->args[0]);
                    }
                }
            }
            if($this->args[1] === 'NICK') {
                $this->adminChange($this->args[0], $this->args[2]);
            }
            if($this->args[1] === 'QUIT' || $this->args[1] === 'PART') {
                $this->removeAdmin($this->args[0]);
            }

            /* @var $listener \Library\IRC\Listener\Base */
            foreach ( $this->listeners as $listener ) {
                if ( is_array( $listener->getKeywords() ) ) {
                    foreach ( $listener->getKeywords() as $keyword ) {
                        // compare listeners keyword and 1st arguments of server
                        // response
                        if ( $keyword === $args[1] ) {
                            $listener->setArgs( $args )->execute( $data );
                        }
                    }
                }
            }

            if ( isset( $args[3] ) ) {
                // Explode the server response and get the command.
                // $source finds the channel or user that the command
                // originated.
                $command = substr( trim( \Library\FunctionCollection::removeLineBreaks( $args[3] ) ), 1 );

                // Check if the response was a command.
                if ( stripos( $command, $this->commandPrefix ) === 0 ) {
                    $command = strtolower( substr( $command, 1 ) );
                    // Command does not exist:
                    if ( !array_key_exists( $command, $this->commands ) ) {
                        $this->log( 'The following, not existing, command was called: "' . $command . '".', 'MISSING' );
                        $this->log( 'The following commands are known by the bot: "' . implode( ',', array_keys( $this->commands ) ) . '".', 'MISSING' );
                        continue;
                    }

                    $this->executeCommand( $command, $args );
                    unset( $args );
                }
            }
        } while ( true );
    }

    /**
     * Adds a single command to the bot.
     *
     * @param IRCCommand $command
     *            The command to add.
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function addCommand(\Library\IRC\Command\Base $command ) {
        $commandName = strtolower( $this->getClassName( $command ) );
        $command->setIRCConnection( $this->connection );
        $command->setIRCBot( $this );
        $this->commands[$commandName] = $command;
        $this->log( 'The following Command was added to the Bot: "' . $commandName . '".', 'INFO' );
    }
    protected function executeCommand( $commandName, $args ) {
        // Execute command:
        $command = $this->commands[$commandName];
        /**
         *
         * @var $command IRCCommand
         */
        $command->setArgs( $args )->executeCommand();
    }
    public function addListener(\Library\IRC\Listener\Base $listener ) {
        $listenerName = $this->getClassName( $listener );
        $listener->setIRCConnection( $this->connection );
        $listener->setIRCBot( $this );
        $this->listeners[$listenerName] = $listener;
        $this->log( 'The following Listener was added to the Bot: "' . $listenerName . '".', 'INFO' );
    }

    /**
     * Returns class name of $object without namespace
     *
     * @param mixed $object
     * @author Matej Velikonja <matej@velikonja.si>
     * @return string
     */
    private function getClassName( $object ) {
        $objectName = explode( '\\', get_class( $object ) );
        $objectName = $objectName[count( $objectName ) - 1];

        return $objectName;
    }

    /**
     * Displays stuff to the broswer and sends data to the server.
     *
     * @param string $cmd
     *            The command to execute.
     *
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function sendDataToServer( $cmd ) {
        $this->log( $cmd, 'COMMAND' );
        $this->connection->sendData( $cmd );
    }

    /**
     * Joins one or multiple channel/-s.
     *
     * @param mixed $channel
     *            An string or an array containing the name/-s of the channel.
     *
     * @author Super3 <admin@wildphp.com>
     */
    private function join_channel( $channel, $key = '' ) {
        if ( is_array( $channel ) ) {
            foreach ( $channel as $chan => $key ) {
                $this->sendDataToServer( 'JOIN ' . $chan . ' ' . $key );
            }
        } else {
            $this->sendDataToServer( 'JOIN ' . $channel . ' ' . $key );
        }
    }

    /**
     * Adds a log entry to the log file.
     *
     * @param string $log
     *            The log entry to add.
     * @param string $status
     *            The status, used to prefix the log entry.
     *
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function log( $log, $status = '' ) {
        if ( empty( $status ) ) {
            $status = 'LOG';
        }

        $msg = date( 'd.m.Y - H:i:s' ) . "\t  [ " . $status . " ] \t" . \Library\FunctionCollection::removeLineBreaks( $log ) . "\r\n";

        echo $msg;

        if ( !is_null( $this->logFileHandler ) ) {
            fwrite( $this->logFileHandler, $msg );
        }
    }

    // Setters

    /**
     * Sets the whole configuration.
     *
     * @param array $configuration
     *            The whole configuration, you can use the setters, too.
     * @author Daniel Siepmann <coding.layne@me.com>
     */
    public function configure() {
        $this->setServer( \Daftbot::get('config')->server );
        $this->setServerPassword( \Daftbot::get('config')->serverPassword );
        $this->setAdminPassword( \Daftbot::get('config')->adminPassword );
        $this->setCommandPrefix( \Daftbot::get('config')->commandPrefix );
        $this->setPort( \Daftbot::get('config')->port );
        $this->setChannel( \Daftbot::get('config')->channels );
        $this->setName( \Daftbot::get('config')->name );
        $this->setNick( \Daftbot::get('config')->nick );
        $this->setPassword( \Daftbot::get('config')->password );
        $this->setMaxReconnects( \Daftbot::get('config')->max_reconnects );
        $this->setLogFile( \Daftbot::get('config')->log_file );
    }

    /**
     * Sets the server.
     * E.g. irc.quakenet.org or irc.freenode.org
     *
     * @param string $server
     *            The server to set.
     */
    public function setServer( $server ) {
        $this->connection->setServer( $server );
    }

    /**
     * Sets the server password for connecting to the server.
     *
     * @param string $server
     *            The server to set.
     */
    public function setServerPassword( $password ) {
        $this->serverPassword = $password;
    }

    /**
     * Sets the admin password for connecting to the server.
     *
     * @param string $password
     */
    public function setAdminPassword( $password ) {
        $this->adminPassword = $password;
    }

    /**
     * Sets the port.
     * E.g. 6667
     *
     * @param integer $port
     *            The port to set.
     */
    public function setPort( $port ) {
        $this->connection->setPort( $port );
    }

    /**
     * Sets the channel.
     * E.g. '#testchannel' or array('#testchannel','#helloWorldChannel')
     *
     * @param string|array $channel
     *            The channel as string, or a set of channels as array.
     */
    public function setChannel( $channel ) {
        $this->channel = (array) $channel;
    }

    /**
     * Sets the name of the bot.
     * "Yes give me a name!"
     *
     * @param string $name
     *            The name of the bot.
     */
    public function setName( $name ) {
        $this->name = (string) $name;
    }

    /**
     * Sets the nick of the bot.
     * "Yes give me a nick too. I love nicks."
     *
     * @param string $nick
     *            The nick of the bot.
     */
    public function setNick( $nick ) {
        $this->nick = (string) $nick;
    }

    /**
     * Sets the password of the bot.
     *
     * @param string $password
     *            The password of the bot.
     */
    public function setPassword( $password ) {
        $this->password = (string) $password;
    }

    /**
     * Set a user as an admin
     *
     * @param string $user
     *            The user to set as admin.
     */
    public function setAdmin( $user ) {
        if(!isset($this->admins[(string) $user])) {
            $this->admins[(string) $user] = true;
            $nick = ltrim(substr($user, 0, strpos($user, '!')), ':');
            $this->log( 'User '.$user.' was added to admin list.' );
            $this->connection->sendData( 'PRIVMSG '.$nick.' :You are now admin. For security reasons, do not close this window.' );
        }
        return $this;
    }

    public function adminChange($user, $newnick) {
        if(isset($this->admins[(string) $user])) {
            $newuser = trim($newnick).substr($user, strpos($user, '!'));
            $this->removeAdmin($user)
                ->setAdmin($newuser);
        }
        return $this;
    }

    public function removeAdmin( $user ) {
        if(isset($this->admins[(string) $user])) {
            unset($this->admins[(string) $user]);
            $this->log( 'User '.$user.' was removed from admin list.' );
        }
        return $this;
    }

    public function getAdmins( $user = null ) {
        return $user == null ? $this->admins : (isset($this->admins[$user]) ? $this->admins[$user] : false);
    }

    /**
     * Sets the limit of reconnects, before the bot exits.
     *
     * @param integer $maxReconnects
     *            The number of reconnects before the bot exits.
     */
    public function setMaxReconnects( $maxReconnects ) {
        $this->maxReconnects = (int) $maxReconnects;
    }

    /**
     * Sets the filepath to the log.
     * Specify the folder and a prefix.
     * E.g. /Users/yourname/logs/daftbot- That will result in a logfile like the
     * following:
     * /Users/yourname/logs/daftbot-11-12-2012.log
     *
     * @param string $logFile
     *            The filepath and prefix for a logfile.
     */
    public function setLogFile( $logFile ) {
        $this->logFile = (string) $logFile;
        if ( !empty( $this->logFile ) ) {
            $logFilePath = dirname( $this->logFile );
            if ( !is_dir( $logFilePath ) ) {
                mkdir( $logFilePath, 0777, true );
            }
            $this->logFile .= date( 'd-m-Y' ) . '.log';
            $this->logFileHandler = fopen( $this->logFile, 'w+' );
        }
    }

    /**
     * Allows plug-ins to serliase to files, if they don't have a serialise
     * method then they won't serialise.
     *
     * @author Jack Blower <Jack@elvenspellmaker.co.uk>
     */
    public function serialise() {
        $this->serialRemMain( "serialise" );
    }

    /**
     * Allows plug-ins to remember data, if they don't have a remember method
     * then they won't try to load anything.
     *
     * @author Jack Blower <Jack@elvenspellmaker.co.uk>
     */
    public function remember() {
        $this->serialRemMain( "remember" );
    }

    /**
     * Performs serialisation or remembering across all listeners and commands
     * if they have implemented this.
     *
     * @param string $serialOrRem
     *            serialise or remember.
     * @author Jack Blower <Jack@elvenspellmaker.co.uk>
     */
    private function serialRemMain( $serialOrRem ) {
        foreach ( $this->listeners as $listener )
            $this->serialRemLoop( $serialOrRem, self::$listenerString, $listener );

        foreach ( $this->commands as $command )
            $this->serialRemLoop( $serialOrRem, self::$commandString, $command );
    }

    /**
     * Helper function for serialise/remember to actually perform the
     * serialisation or remembering.
     * Logs whether the command was successful or not.
     *
     * @author Jack Blower <Jack@elvenspellmaker.co.uk>
     */
    private function serialRemLoop( $methodName, $beginningString, $object ) {
        if ( method_exists( $object, $methodName ) )
            if ( $object->{$methodName}() !== FALSE )
                $this->serialRemLog( $beginningString, $object, $methodName, "INFO" );
            else
                $this->serialRemLog( $beginningString, $object, $methodName, "WARNING" );
    }

    /**
     * The log cop
     *
     * @author Jack Blower <Jack@elvenspellmaker.co.uk>
     */
    private function serialRemLog( $beginningString, $object, $methodName, $logType ) {
        $fail = ( $logType == "WARNING" ) ? " Fail" : "";
        $this->log( $beginningString . " '" . $this->getClassName( $object ) . "' " . self::$serialiseStrings[ucfirst( $methodName ) . " End" . $fail], $logType );
    }

    /**
     * Returns a specific requested command.
     * Please inspect \Library\IRC\Bot::$commands to find out the name
     *
     * @param string $commands
     * @return \Library\IRC\Command\Base
     */
    public function getCommand( $command ) {
        return $this->commands[$command];
    }

    /**
     * Return a list of all the commands registered
     * @return array
     */
    public function getCommands() {
        return $this->commands;
    }

    /**
     * Get the command prefix
     * @return string
     */
    public function getCommandPrefix() {
        return $this->commandPrefix;
    }
    public function setCommandPrefix($prefix) {
        $this->commandPrefix = $prefix;
        return $this;
    }

    /**
     * get the nick of the bot
     * @return string
     */
    public function getNick() {
        return $this->nick;
    }

    /**
     * get the password of the bot
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * get the connection
     * @return \Library\IRC\Connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Return a list of loaded listeners
     *
     * @return array
     */
    public function getListeners() {
        return $this->listeners;
    }

    /**
     * Returns a specific requested listener.
     * Please inspect \Library\IRC\Bot::$listeners to find out the name
     *
     * @param string $listener
     * @return \Library\IRC\Listener\Base
     */
    public function getListener( $listener ) {
        //var_dump( $this->listeners, $listener );
        return $this->listeners[$listener];
    }
}
