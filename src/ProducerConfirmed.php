<?php

namespace WAG\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

final class ProducerConfirmed
{
    use ExchangeDeclaration;

    /**
     * @param AMQPMessage $message
     */
    public function publish($message)
    {
        if (! $message instanceof AMQPMessage) {
            throw new \InvalidArgumentException(
                'Message of type \PhpAmqpLib\Message\AMQPMessage expected.'
            );
        }
        $message->set('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
        $this->channel->basic_publish($message, $this->exchangeName);
        $this->channel->wait_for_pending_acks();
    }

    public function __destroy()
    {
        $this->channel->close();
        $this->channel->getConnection()->close();
    }
}
