<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use WAG\RabbitMq\ChannelBuilder;
use WAG\RabbitMq\ProducerConfirmed;

$config = include __DIR__ . '/../config/main.php';

$exchangeName         = 'exampleExchangeName';
$exchangeDeclarations = $config['exchangeDeclarations'][$exchangeName];

$channelBuilder = new ChannelBuilder(
    $config['connection']['hostname'],
    $config['connection']['port'],
    $config['connection']['username'],
    $config['connection']['password']
);

$ackFunction = static function () {
    echo 'Message done', \PHP_EOL;
};

$nackFunction = static function () {
    echo 'Message not done ! Error !', \PHP_EOL;
};


$channel = $channelBuilder->channelPublishConfirmed($ackFunction, $nackFunction);

$producer = new ProducerConfirmed(
    $channel,
    $exchangeName,
    \array_keys($exchangeDeclarations['queues'])
);

$message = new AMQPMessage('{"name":"krueger","vorname":"Lutz","properties":{"alter":"58"}');
$producer->publish($message);
