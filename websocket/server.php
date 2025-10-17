<?php
// server.php - WebSocket server menggunakan Ratchet + Redis pub/sub

require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Predis\Client as RedisClient;

// WebSocket server class
class AttendanceWS implements MessageComponentInterface {
    protected $clients;
    protected $redis;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Koneksi ke Redis
        $redis_host = getenv('REDIS_HOST') ?: 'redis';
        $this->redis = new RedisClient(['host' => $redis_host]);

        // Jalankan subscribe Redis di background
        $this->subscribeRedis();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Opsional: tidak dipakai, karena kita broadcast dari Redis
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function subscribeRedis() {
        echo "Subscribing to Redis channel 'attendance'...\n";
        $pubsub = $this->redis->pubSubLoop();
        $pubsub->subscribe('attendance');

        foreach ($pubsub as $message) {
            if ($message->kind === 'message') {
                foreach ($this->clients as $client) {
                    $client->send($message->payload);
                }
            }
        }
    }
}

// Jalankan WebSocket server
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(new AttendanceWS())
    ),
    8081
);

echo "WebSocket server running on port 8081...\n";
$server->run();
