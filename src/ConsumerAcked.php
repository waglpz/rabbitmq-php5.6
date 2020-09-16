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

    public function acknowledgesMessage(AMQPMessage $message)
    {
        $message->get('channel')->basic_ack($message->get('delivery_tag'));
    }

    /**
     * @param string $queueName
     * @param bool $doAck
     * @return AMQPMessage|null
     */
    public function fetchMessage($queueName, $doAck = true)
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
            return null;
        }
        $callback        = $this->callback;
        $eventualMessage = $callback($message);

        if ($eventualMessage !== null) {
            if ($eventualMessage !== $message) {
                throw new \LogicException('Callback does not returns same message instance or null as expected.');
            }

            if ($doAck) {
                $this->acknowledgesMessage($message);
            }
        }

        return $eventualMessage;
    }
}
