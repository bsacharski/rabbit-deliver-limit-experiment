<?php

declare(strict_types=1);

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use function App\createConnection;
use function App\createExchange;
use function App\createQueue;

require_once __DIR__ . '/vendor/autoload.php';

$waitDuration = 30;
echo "Waiting {$waitDuration} seconds for RabbitMQ to start\n";

$startTime = time();
while (true) {
    try {
        $connection = createConnection();
        $channel = $connection->channel();
        break;
    } catch (Exception $e) {
        $now = time();
        if ($now > ($startTime + $waitDuration)) {
            throw new Exception("Failed to connect to rabbit", $e->getCode(), $e);
        } else {
            usleep(100 * 1000);
        }
    }
}

$queueName = "sample";

createExchange("normalExchange", $channel);
createExchange('dlExchange', $channel);

createQueue($queueName, $channel, 'dlExchange');
createQueue("{$queueName}.dlq", $channel);

$channel->queue_bind($queueName, 'normalExchange');
$channel->queue_bind("{$queueName}.dlq", 'dlExchange');


echo "Pushing message...";
$message = new AMQPMessage("Foo");
$channel->basic_publish($message, 'normalExchange');
echo "Done\n";