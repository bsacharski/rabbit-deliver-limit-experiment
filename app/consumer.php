<?php

declare(strict_types=1);

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use function App\createConnection;

require_once __DIR__ . '/vendor/autoload.php';

$waitDuration = 30;
echo "Waiting {$waitDuration} seconds for RabbitMQ to start\n";

$consumerId = $argv[1] ?? 0;

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

echo "Waiting for messages...\n";

$channel->basic_consume('sample', "myConsumer_{$consumerId}", false, false, false, false, function (AMQPMessage $message) {
    $props = $message->get_properties();
    if (isset($props['application_headers'])) {
        /* @var AMQPTable $headers */
        $headers = $props['application_headers'];
        $deliveryCount = $headers->getNativeData()['x-delivery-count'] ?? 0;
    } else {
        $deliveryCount = 0;
    }


    echo "Message: '{$message->body}' - delivered {$deliveryCount} time(s).\n";
    die(1);
});

while ($channel->is_open()) {
    $channel->wait();
}
