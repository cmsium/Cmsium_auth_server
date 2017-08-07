<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

$address = 'localhost';
$port = 8888;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() fail: " . socket_strerror(socket_last_error()) . "\n";
}
if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() fail: " . socket_strerror(socket_last_error($sock)) . "\n";
}
if (socket_listen($sock, 5) === false) {
    echo "socket_listen() fail: " . socket_strerror(socket_last_error($sock)) . "\n";
}

//clients array
$clients = array();

if (pcntl_fork()) {
    print "Daemon running.".PHP_EOL;
} else {
    $sid = posix_setsid();
    if ($sid < 0)
        exit;
    while (true) {
            $read = array();
            $read[] = $sock;
            $read = array_merge($read,$clients);
            $write = $except = null;
            if(socket_select($read,$write, $except, $tv_sec = 5) < 1)
            {
                continue;
            }
            if (in_array($sock, $read)) {

                if (($msgsock = socket_accept($sock)) === false) {
                    echo "socket_accept() fail: " . socket_strerror(socket_last_error($sock)) . "\n";
                    break;
                }
                $clients[] = $msgsock;
                $key = array_keys($clients, $msgsock);

            }
            foreach ($clients as $key => $client) {
                if (in_array($client, $read)) {
                    if (false === ($buf = socket_read($client, 2048, PHP_NORMAL_READ))) {
                        echo "socket_read() fail: " . socket_strerror(socket_last_error($client)) . "\n";
                        break 2;
                    }
                    if (!$buf = trim($buf)) {
                        continue;
                    }
                    switch ($buf) {
                        case 'quit':
                            unset($clients[$key]);
                            socket_close($client);
                            break 2;
                        case 'shutdown':
                            socket_close($client);
                            break 3;
                        default:
                            $buf = urlencode($buf);
                            $talkback = `php tcpcontroller.php $buf`;
                            var_dump($talkback);
                            socket_write($client, $talkback, strlen($talkback));
                    }
                }

            }
    }
}
socket_close($sock);