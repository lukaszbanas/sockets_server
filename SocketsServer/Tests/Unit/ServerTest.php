<?php namespace SocketsServer\Tests\Unit;

use Ratchet\ConnectionInterface;
use Ratchet\Mock\Connection;
use SocketsServer\Classes\Server;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    /**
     * @var Server
     */
    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new Server();
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnOpen(): void
    {
        $this->expectOutputRegex('/(.*)New connection!(.*)/');
        $conn1 = $this->getConnectionMock();
        $this->server->onOpen($conn1);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetNumberOfConnectedClients(): void
    {
        $this->assertEquals(0, $this->server->getNumberOfConnectedClients());

        $conn1 = $this->getConnectionMock();
        $conn2 = $this->getConnectionMock();
        $this->server->onOpen($conn1);
        $this->assertEquals(1, $this->server->getNumberOfConnectedClients());

        $this->server->onOpen($conn2);
        $this->assertEquals(2, $this->server->getNumberOfConnectedClients());

        $this->server->onClose($conn1);
        $this->assertEquals(1, $this->server->getNumberOfConnectedClients());

        $this->server->onClose($conn2);
        $this->assertEquals(0, $this->server->getNumberOfConnectedClients());
    }

    public function testOnMessage(): void
    {
        $conn1 = $this->getConnectionMock();
        $conn2 = $this->getConnectionMock();
        $this->server->onOpen($conn1);
        $this->server->onOpen($conn2);

        $this->server->onMessage($conn1, 'test');
        $this->assertEquals('test', $conn2->last['send']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnClose(): void
    {
        $this->server->onOpen($this->getConnectionMock());
        ob_clean();
        $this->expectOutputRegex('/(.*)Connection has disconnected(.*)/');
        $this->server->onClose($this->getConnectionMock());
    }

    private function getConnectionMock(): ConnectionInterface
    {
        return new Connection();
    }
}
