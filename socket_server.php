<?php
require __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
set_time_limit(0);
ob_implicit_flush();

use Symfony\Component\Yaml\Yaml;

try {
    $config = Yaml::parseFile(__DIR__ . '/config/conf.yml');
} catch (Exception $e) {
    echo 'Exception: ',  $e->getMessage(), "\n";
}

define("MAX_CLIENTS", 10);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Couldn't create socket: " . socket_strerror(socket_last_error()) . PHP_EOL);
echo "Socket created \n";

socket_bind($sock, $config['address'] , $config['port']) or die("Couldn't bind socket: " . socket_strerror(socket_last_error()) . PHP_EOL);
echo "Socket bind OK \n";

socket_listen ($sock , MAX_CLIENTS) or die("Couldn't listen socket: " . socket_strerror(socket_last_error()) . PHP_EOL);
echo "Socket listen OK \n";

echo "Waiting for incoming connections... \n";

//array of client sockets
$client_socks = array();

//array of sockets to read
$read = array();

//start loop to listen for incoming connections and process existing connections
while (true) {
    //prepare array of readable client sockets
    $read = array();

    //first socket is the master socket
    $read[0] = $sock;

    //now add the existing client sockets
    for ($i = 0; $i < $max_clients; $i++) {
        if ($client_socks[$i] != null) {
            $read[$i+1] = $client_socks[$i];
        }
    }

    //now call select - blocking call
    socket_select($read , $write , $except , null) or die("Couldn't listen to socket: " . socket_strerror(socket_last_error()) . PHP_EOL);

    //if ready contains the master socket, then a new connection has come in
    if (in_array($sock, $read)) {
        for ($i = 0; $i < $max_clients; $i++) {
            if ($client_socks[$i] == null) {
                $client_socks[$i] = socket_accept($sock);

                if (socket_getpeername($client_socks[$i], $config['address'], $config['port'])) {
                    echo "Client " . $config['address'] . ": " . $config['port'] . " is now connected to us. \n";
                }

                break;
            }
        }
    }

    //check each client if they send any data
    for ($i = 0; $i < $max_clients; $i++) {
        if (in_array($client_socks[$i] , $read)) {
            $input = socket_read($client_socks[$i] , 1024);

            if ($input == null) {
                //zero length string meaning disconnected, remove and close the socket
                unset($client_socks[$i]);
                socket_close($client_socks[$i]);
            }

            try {
                $regex = \src\Parenthesis::hasMatched($input);
                $response = 'The Expression ' . ($regex === true ? 'is' : 'is not') . ' correct' . PHP_EOL;
            } catch (InvalidArgumentException $exception) {
                $response = $exception;
            }

            $output = $client_socks[$i]. $response;

            echo "Sending output to client \n";

            //send response to client
            socket_write($client_socks[$i] , $output);

            // send response to other client
//            foreach (array_diff_key($client_socks, array($i => 0)) as $client_sock) {
//                socket_write($client_sock , $output);
//            }
        }
    }
}