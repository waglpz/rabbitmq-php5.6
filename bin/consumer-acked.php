<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use WAG\RabbitMq\ChannelBuilder;
use WAG\RabbitMq\ConsumerAcked;

$config = include __DIR__ . '/../config/main.php';

/**
 * @param AMQPMessage $message
 */
$callbackWithExplizitAck = static function ($message) {
    echo 'Message received', \PHP_EOL;
    // Do ???
    echo 'Message proceed', \PHP_EOL;
    $message->get('channel')->basic_ack($message->get('delivery_tag'));
};

/**
 * @param AMQPMessage $message
 */
$callbackWithImplizitAck = static function ($message) {
    echo 'Message received', \PHP_EOL;
    // Do ???
    echo 'Message proceed', \PHP_EOL;
    return $message;
};

/**
 * @param AMQPMessage $message
 */
$callback = static function ($message) {
    echo 'Message received', \PHP_EOL;
    // Do ???
    echo 'Message proceed', \PHP_EOL;
};

$channelBuilder = new ChannelBuilder(
    $config['connection']['hostname'],
    $config['connection']['port'],
    $config['connection']['username'],
    $config['connection']['password']
);
$channel = $channelBuilder->channelConsumerAcked();
// todo: prefetch mode in Channel setzen !

$exchangeName         = 'exampleExchangeName';
$exchangeDeclarations = $config['exchangeDeclarations'][$exchangeName];

$consumerAcked = new ConsumerAcked($channel, $exchangeName, \array_keys($exchangeDeclarations['queues']));
$consumerAcked->setCallback($callbackWithExplizitAck);
$consumerAcked->fetchMessage('exampleQueueName1');


register_shutdown_function(
    static function () use ($channel) {
        $channel->close();
        $connection = $channel->getConnection();
        if ($connection === null) {
            return;
        }

        $connection->close();
    }
);
