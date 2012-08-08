<?php
namespace callnotifier;

class Source_Remote
{
    protected $socket;

    public function __construct($config, $handler)
    {
        $this->config  = $config;
        $this->handler = $handler;
    }

    public function run()
    {
        $this->connect($this->config->host, $this->config->port);
        $this->init();
        $this->loop();
        $this->disconnect();
    }

    public function connect($ip, $port)
    {
        if ($ip == '') {
            throw new \Exception('No remote IP or hostname given.');
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new \Exception(
                'socket_create() failed: reason: '
                . socket_strerror(socket_last_error())
            );
        }
        //echo "Attempting to connect to '$ip' on port '$port'...";
        $result = socket_connect($socket, $ip, $port);
        if ($result === false) {
            throw new \Exception(
                "socket_connect() failed. Reason: "
                . socket_strerror(socket_last_error($socket))
            );
        }

        $this->socket = $socket;
    }

    function init()
    {
        $msg = "\x00\x01DecoderV=1\n";
        socket_write($this->socket, $msg, strlen($msg));
        $res = $this->read_response();
        socket_write($this->socket, "\x00\x02", 2);
    }

    function loop()
    {
        while (true) {
            $dbgmsg = $this->read_response();
            //echo $dbgmsg . "\n";
            $this->handler->handle($dbgmsg);
        }
    }

    function read_response()
    {
        $res = socket_read($this->socket, 2048, PHP_NORMAL_READ);
        return substr($res, 2, -1);
    }

    function disconnect()
    {
        socket_write($this->socket, "\x00\x03", 2);
        socket_close($this->socket);
    }

}

?>
