<?php

declare(strict_types=1);

namespace App;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * @throws Exception
 */
function createConnection(): AMQPStreamConnection {
    return new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
}

function createExchange(string $name, AMQPChannel $channel): void {
    $channel->exchange_declare(
        $name,
        'fanout',
        false,
        false,
        false
    );
}

function createQueue(string $name, AMQPChannel $channel, ?string $deadLetterExchangeName = null): void
{
    if ($deadLetterExchangeName) {
        $args = new AMQPTable([
            'x-delivery-limit' => 4,
            'x-dead-letter-exchange' => $deadLetterExchangeName,
            'x-queue-type' => 'quorum',
        ]);
    } else {
        $args = new AMQPTable();
    }

    $channel->queue_declare(
        $name,
        false,
        true,
        false,
        false,
        false,
        $args
    );
}