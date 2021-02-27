<?php
#mrpc/SevenDaysToDie.php

namespace mrpc;

/**
 * 7 Days To Die connector
 */
class SevenDaysToDie
{
    protected $socket;
    protected $hostname;
    protected $port;
    protected $password = '';
    

    /**
     * Class constructor
     */
    public function __construct($hostname, $port, $password = '')
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->password = $password;
    }

    /**
     * Connect to server
     */
    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_connect($this->socket, $this->hostname, $this->port) === FALSE) {
            return false;
        }
        $data = $this->read();
        if ($data == 'Please enter password:') {
            $this->send($this->password);
        }

        \mrpc\Logger::log(trim($this->read()), '7daystodie');
        return true;
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