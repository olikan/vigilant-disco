<?php

$host = '127.0.0.1';

while (true) {
    echo "Port (q - quit):  ";

    $command = trim(fgets(STDIN));

    if ($command === 'q') {
        exit("Exit." . PHP_EOL);
    } elseif ($command) {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Couldn't create socket: " . socket_strerror(socket_last_error()) . PHP_EOL);
            echo 'Socket created' . PHP_EOL;
            echo 'Trying to ' . $host . PHP_EOL;

            $connect = socket_connect($socket, $host, $command) or die("Couldn't connect: " . socket_strerror(socket_last_error()) . PHP_EOL);
            echo 'Connection established' . PHP_EOL;

            while(true) {
                echo "Expression or 'q': ";
                $message = trim(fgets(STDIN));

                if ($message === 'q') {
                    exit("Exit." . PHP_EOL);
                }

                socket_send( $socket, $message , strlen($message) , 0) or die("Couldn't send: " . socket_strerror(socket_last_error()) . PHP_EOL);
                echo 'Message was sent' . PHP_EOL;

                $response = socket_read($socket, 1024000) or die("Couldn't get response: " . socket_strerror(socket_last_error()) . PHP_EOL);
                echo $response . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "Error was caught: " . $e->getMessage() . PHP_EOL;
        }
    } else {
        echo "Пустое сообщение - не сообщение ) " . PHP_EOL;
    }
}