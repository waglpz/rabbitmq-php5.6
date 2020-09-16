<?php

namespace WAG\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ChannelBuilder
{
    /** @var string */
    private $port;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var string */
    private $hostname;

    /** @var AMQPStreamConnection */
    private $connection;

    /**
     * ChannelBuilder constructor.
     * @param string $hostname
     * @param string $port
     * @param string $username
     * @param string $password
     */
    public function __construct($hostname, $port, $username, $password)
    {
        if (! \is_string($hostname) || $hostname === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Hostname not empty string expected given "%s"',
                    \print_r($hostname, true)
                )
            );
        }

        if (! \is_string($port) || $port === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Port not empty string expected given "%s"',
                    \print_r($port, true)
                )
            );
        }

        if (! \is_string($username) || $username === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Username not empty string expected given "%s"',
                    \print_r($username, true)
                )
            );
        }

        if (! \is_string($password) || $password === '') {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Username not empty string expected given "%s"',
                    \print_r($password, true)
                )
            );
        }
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getConnection()
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $this->connection = new AMQPStreamConnection($this->hostname, $this->port, $this->username, $this->password);

        return $this->connection;
    }

    /**
     * @param callable $ack
     * @param callable $nack
     * @return AMQPChannel
     */
    public function channelPublishConfirmed($ack, $nack)
    {
        $channel = $this->getConnection()->channel();
        $channel->set_ack_handler($ack);
        $channel->set_nack_handler($nack);
        $channel->confirm_select();

        return $channel;
    }

    /**
     * @return AMQPChannel
     */
    public function channelConsumerAcked()
    {
        return $this->getConnection()->channel();
    }
}
