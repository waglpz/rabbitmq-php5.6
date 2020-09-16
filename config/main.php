<?php



return array(
    'connection'=> include 'connection.php',
    'exchangeDeclarations'=> array(
        'exampleExchangeName' =>array(
            'queues'=>array(
                'exampleQueueName1' => array(), // Parametererweiterung in Zukunft wenn
                'exampleQueueName2' => array(),
        ),
    ),
),
);
