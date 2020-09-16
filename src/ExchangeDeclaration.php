<?php

namespace WAG\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exchange\AMQPExchangeType;

trait ExchangeDeclaration
{
    /** @var AMQPChannel */
    private $channel;
    /** @var string */
    private $exchangeName;
    /** @var array|null */
    private $queues;

    /**
     * @param AMQPChannel $channel
     * @param string $exchangeName
     * @param array|null $queues
     * @param string $exchangeTyp
     */
    public function __construct(
        $channel,
        $exchangeName,
        array $queues = null,
        $exchangeTyp = AMQPExchangeType::DIRECT
    ) {
        if (! $channel instanceof AMQPChannel) {
            throw new \InvalidArgumentException(
                'Channel of type \PhpAmqpLib\Channel\AMQPChannel expected.'
            );
        }

        if (! \is_string($exchangeName) || $exchangeName === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'ExchangeName name not empty string expected given "%s"',
                    \print_r($exchangeName, true)
                )
            );
        }

        $this->channel      = $channel;
        $this->exchangeName = $exchangeName;
        $this->queues       = $queues;

        $persistent = true;
        $this->channel->exchange_declare($exchangeName, $exchangeTyp, false, $persistent, false);
        if ($queues === null) {
            return;
        }

        foreach ($queues as $queueName) {
            $this->channel->queue_declare($queueName, false, $persistent, false, false);
            $this->channel->queue_bind($queueName, $exchangeName);
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $connection = $this->channel->getConnection();
        if ($connection !== null) {
            $connection->close();
        }
    }
}
