<?php

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

final class ConsumerAcked
{
    use ExchangeDeclaration;

    /** @var \Closure|null */
    private $callback;

    /**
     * @param \Closure $callback
     */
    public function setCallback($callback)
    {
        if (! $callback instanceof \Closure) {
            throw new \InvalidArgumentException('Callback of type \Closure expected.');
        }

        $this->callback = $callback;
    }

    /**
     * @param int $count
     */
    public function setPrefetchMessages($count)
    {
        $this->channel->basic_qos(
            0,
            $count,
            false
        );
    }

    public function consume()
    {
        if ($this->queues === null || \count($this->queues) < 1) {
            throw new \InvalidArgumentException('Queues are not yet defined?');
        }

        if (! isset($this->callback)) {
            throw new \InvalidArgumentException('Callback not defined.');
        }

        foreach ($this->queues as $queue) {
            $this->channel->basic_consume($queue, $queue . 'Consumer', false, false, false, false, $this->callback);
        }

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function acknowlegdeMessage()
    {
        $message->get('channel')->basic_ack($message->get('delivery_tag'));
    }

    /**
     * @param string $queueName
     */
    public function fetchMessage($queueName)
    {
        if (! \is_string($queueName) || $queueName === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Queue name not empty string expected given "%s"',
                    \print_r($queueName, true)
                )
            );
        }
        
        if (! isset($this->callback)) {
            throw new \InvalidArgumentException('Callback not defined.');
        }

        $message = $this->channel->basic_get($queueName);
        if ($message === null) {
            return;
        }
        $callback = $this->callback;
        $eventualMessage = $callback($message);

        if ($eventualMessage instanceof  AMQPMessage) {
            $eventualMessage->get('channel')->basic_ack($message->get('delivery_tag'));
        }
    }
}
