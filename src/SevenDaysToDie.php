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
        usleep(100000);
        $data = $this->read();
        \mrpc\Logger::log(trim($data), '7daystodie');
        \mrpc\Logger::log($this->password, '7daystodie');
        $this->send($this->password);
        
        $data = $this->read(512);
        if ($data != '') {
           \mrpc\Logger::log($data, '7daystodie');
        }
        socket_set_option(
            $this->socket, SOL_SOCKET, SO_RCVTIMEO, 
            array("sec" => 1, "usec" => 50000)
        );
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
    public function read($size = 512)
    {
        if (!$this->isConnected()) {
            return false;
        }
        $data = socket_read($this->socket, $size);
        if (!$data) {
            if ($this->getLastError() == 107) {
                $this->close();
                $this->socket = null;
            }
        }
        return $data;
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