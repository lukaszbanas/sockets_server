<?php namespace SocketsServer\Classes;

use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

/**
 * Class Server
 *
 * @package SocketsServer\Classes
 */
class Server implements MessageComponentInterface
{
    /**
     * @var SplObjectStorage
     */
    protected SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);

        echo "New connection!\n";
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $numRecv = $this->getNumberOfConnectedClients() - 1;

        echo sprintf(
            'Connection sending message "%s" to %d other connection%s' . "\n",
            $msg,
            $numRecv,
            $numRecv === 1 ? '' : 's'
        );

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);

        echo "Connection has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param Exception $exception
     */
    public function onError(ConnectionInterface $conn, Exception $exception): void
    {
        echo "An error has occurred: {$exception->getMessage()}\n";

        $conn->close();
    }

    /**
     * @return int
     */
    public function getNumberOfConnectedClients(): int
    {
        return count($this->clients);
    }
}
