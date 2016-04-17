<?php

/**
 * The MPD client.
 * To learn more about the MPD protocol, see https://www.musicpd.org/doc/protocol
 */
class Client
{
    /** @var string */
    private $host = "localhost";

    /** @var  int  */
    private $port = 6600;

    private $socket;

    /** @var int  */
    private $volume = 100;

    /** @var bool Whether playback is paused */
    private $isPaused = false;

    public function __construct()
    {
        $this->socket = $this->connect();
    }

    /**
     * Returns a socket.
     *
     * @return resource     A socket resource
     *
     * @throws Exception
     */
    private function connect()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            throw new Exception(
                "Could not create a socket. Error: " . socket_strerror(socket_last_error())
            );
        }

        $connection = socket_connect($socket, $this->host, $this->port);
        if ($connection === false) {
            throw new Exception(
                "Could not connect to MPD. Error: $connection " . socket_strerror(socket_last_error($socket))
            );
        }

        return $socket;
    }

    /**
     * Play music.
     */
    public function play($songNumber)
    {
        socket_write($this->socket, "play $songNumber\n");
    }

    /**
     * Stop playback.
     */
    public function stop()
    {
        socket_write($this->socket, "stop\n");
    }

    /**
     * Plays the next song in the current playlist.
     */
    public function next()
    {
        socket_write($this->socket, "next\n");
    }

    /**
     * Pauses/resumes playback
     */
    public function pause()
    {
        if ($this->isPaused) {
            socket_write($this->socket, "pause 0\n");
            $this->isPaused = false;
        } else {
            socket_write($this->socket, "pause 1\n");
            $this->isPaused = true;
        }
    }

    /**
     * Returns all files in the given path of your music library.
     *
     * @param   string      $path   The path to look in
     *
     * @return  string[]       An array of file paths
     */
    public function listAll($path = "/")
    {
        $socket = $this->connect();
        socket_write($socket, "listall $path\n");
        $files = [];
        while ($response = socket_read($socket, 1024, PHP_NORMAL_READ)) {
            if ($response == "OK\n") {
                return $files;
            }
            if (substr($response, 0, 6) != "OK MPD") {
                if (substr($response, 0, 6) == "file: ") {
                    $files[] = trim(substr($response, 6));
                }
            }
        }
        return $files;
    }

    /**
     * Lists information about the songs in the music database.
     *
     * @param string    $column     The column to list (e.g. Album, Artist,...)
     *
     * @return string[]
     *
     * @throws LogicException
     */
    public function listColumn($column)
    {
        $availableColumns = ["artist", "album", "title", "track", "genre", "date"];

        if (!in_array(strtolower(str_replace(" ", "", $column)), $availableColumns)) {
            throw new LogicException(
                "Unsupported column $column.\nSupported columns: Artist, Album, Title, Track, Genre, Date"
            );
        }

        $socket = $this->connect();
        socket_write($socket, "list $column\n");
        $return = [];

        while ($response = socket_read($socket, 1024, PHP_NORMAL_READ)) {
            if ($response == "OK\n") {
                return $return;
            }
            if (substr($response, 0, 6) != "OK MPD") {
                $return[] = trim(substr($response, 7));
            }
        }
        return $return;
    }

    /**
     * Returns MPD's current status (e.g. volume, repeat, shuffle,...)
     *
     * @return array        Key-value array of MPD's current status
     */
    public function status()
    {
        $socket = $this->connect();
        socket_write($socket, "status\n");

        $status = [];

        while ($response = socket_read($socket, 1024, PHP_NORMAL_READ)) {
            if ($response == "OK\n") {
                return $status;
            }

            $line = explode(": ", $response);

            if (isset($line[0]) && isset($line[1])) {
                $status[$line[0]] = trim($line[1]);
            }
        }

        return $status;
    }

    /**
     * Adds a file to the current playlist.
     *
     * @param $file     string      A filename.
     */
    public function add($file)
    {
        socket_write($this->socket, "findadd file $file \n");
    }

    /**
     * Update the client's music database. This is helpful when you add or remove files to MPD's music library folder.
     */
    public function update()
    {
        socket_write($this->socket, "update\n");
    }

    public function volumeUp()
    {
        if ($this->volume < 100) {
            if ($this->volume > 95) {
                $this->setVolume(100);
            } else {
                $this->setVolume(($this->volume + 5));
            }
        }
    }

    public function volumeDown()
    {
        if ($this->volume > 0) {
            if ($this->volume < 5) {
                $this->setVolume(0);
            } else {
                $this->setVolume($this->volume - 5);
            }
        }
    }

    /**
     * @param int   $volume       The volume to set.
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
        socket_write($this->socket, "setvol $volume \n");
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }
}