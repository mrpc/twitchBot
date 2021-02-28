<?php
#mrpc/TwitchChatClient.php

namespace mrpc;

/**
 * Twitch IRC chat client
 */
class TwitchChatClient
{
    protected $socket;
    /**
     * Chatbot Nickname
     * @var string
     */
    protected $nick;
    /**
     * Channel to join (streamer)
     * @var string
     */
    protected $channel;
    /**
     * oAuth token
     * @var string
     */
    protected $oauth;
    /**
     * Twitch IRC server hostname
     * @var string
     */
    static $host = "irc.chat.twitch.tv";
    /**
     * Twitch IRC server port
     * @var string
     */
    static $port = "6667";

    /**
     * Class constructor
     */
    public function __construct($nick, $oauth, $channel)
    {
        $this->nick = $nick;
        $this->oauth = $oauth;
        $this->channel = $channel;
    }

    /**
     * Connect to server
     */
    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_connect($this->socket, self::$host, self::$port) === FALSE) {
            return null;
        }
        $this->send(sprintf("PASS %s", $this->oauth));
        $this->send(sprintf("NICK %s", $this->nick));
        $this->send('CAP REQ :twitch.tv/membership');
        $this->send('CAP REQ :twitch.tv/tags');
        $this->send('CAP REQ :twitch.tv/commands');
        $this->send(sprintf("JOIN #%s", $this->channel));

        socket_set_option(
            $this->socket, SOL_SOCKET, SO_RCVTIMEO, 
            array("sec" => 1, "usec" => 500000)
        );
    }

   
    /**
     * Returns the last error on the socket
     * @return string
     */
    public function getLastError()
    {
        return socket_last_error($this->socket);
    }

    /**
     * Check if the socket is connected
     * @return bool
     */
    public function isConnected()
    {
        return !is_null($this->socket);
    }


    /**
     * Reads a maximum of length bytes from the socket
     * @return string
     */
    public function read($size = 256)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return socket_read($this->socket, $size);
    }

    /**
     * Send a command to the socket
     * @return int|false the number of bytes successfully written to the 
     *                   socket or FALSE on failure. The error code can be 
     *                   retrieved with socket_last_error. This code may be 
     *                   passed to socket_strerror to get a textual 
     *                   explanation of the error.
     */
    public function send($message)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return socket_write($this->socket, $message . "\n");
    }

    /**
     * Close the socket connection
     */
    public function close()
    {
        socket_close($this->socket);
    }
}