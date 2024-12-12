<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class NoteServer implements MessageComponentInterface {
    protected $clients;
    private $dbConnection;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Conexão com o banco de dados
        $this->dbConnection = new mysqli(
            getenv('MYSQLHOST'), // Host do banco de dados fornecido pelo Railway
            getenv('MYSQLUSER'), // Usuário do banco
            getenv('MYSQLPASSWORD'), // Senha do banco
            getenv('MYSQLDATABASE') // Nome do banco
        );

        if ($this->dbConnection->connect_error) {
            die("Erro ao conectar com o banco de dados: " . $this->dbConnection->connect_error);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        // Carregar o conteúdo da nota do banco de dados
        $result = $this->dbConnection->query("SELECT conteudo FROM notas WHERE id = 1");
        $row = $result->fetch_assoc();
        $conn->send($row['conteudo']);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Atualizar o banco de dados
        $stmt = $this->dbConnection->prepare("UPDATE notas SET conteudo = ? WHERE id = 1");
        $stmt->bind_param("s", $msg);
        $stmt->execute();

        // Enviar a mensagem para todos os clientes conectados
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NoteServer()
        )
    ),
    8080
);

$server->run();
?>
