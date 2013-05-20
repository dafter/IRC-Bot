# Daftbot - A PHP 5 Litecoin IRC bot (Dafter)
A IRC Bot built in PHP (using sockets) with OOP. Designed to run off a local LAMP, WAMP, or MAMP stack.
Includes a custom [Upstart](http://upstart.ubuntu.com/) script to run as Linux daemon.

Web
-------
* Official Website: [http://daftpool.com](http://daftpool.com), Source Code: [Github](https://github.com/dafter/daftbot)
* Original Source Contributors (thanks!): [Super3](http://super3.org), [Pogosheep](http://layne-obserdia.de), [Matejvelikonja](http://velikonja.si), [ElvenSpellmaker](https://github.com/ElvenSpellmaker/)


## Features and Functions

### Standard Commands

* !say [#channel] [message] - Says message in the specified IRC channel.
* !say [username] [message] - Says message in the specified IRC user.
* !join [#channel] - Joins the specified channel.
* !part [#channel] - Parts the specified channel.
* !timeout [seconds] - Bot leaves for the specified number of seconds.
* !restart - Quits and restarts the script.
* !quit - Quits and stops the script.

### Entended Commands

* !ip - Returns IP of a user.
* !weather [location] - Returns weather data for location.
* !poke [#channel] [username] - Pokes the specified IRC user.
* !joke - Returns random joke. Fetched from [ICNDb.com](http://www.icndb.com/).
* !imdb [movie title] - Searches for movie and returns it's information.


### Listeners

* Joins - Greets users when they join the channel.

## Install & Run

### Dependecy

proctitle (optional) - Changes the process title when running as service.

    pecl install proctitle-alpha

### Config

Copy configuration file and customize its content.

    cp config.php config.local.php

Copy Upstart script to folder and make appropriate changes.

    sudo cp bin/daftbot.conf /etc/init/

### Run

Run as PHP

    php daftbot.php

or Upstart service

    start daftbot

Restart

    restart phpbot404

Stop

    stop phpbot404

### Sample Usage and Output

    <random-user> !say #daftbot hello there
    <daftbot> hello there
    <random-user> !poke #daftbot random-user
    * daftbot pokes random-user

### Community

IRC: [#daftbot@freenode.net](http://webchat.freenode.net/?channels=daftbot)
