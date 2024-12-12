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
        $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
        $user = getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: 'fOOYAKfjHVyudHTltvIwuLKsbTzODiWZ';
        $dbname = getenv('MYSQLDATABASE') ?: 'railway';

        $this->dbConnection = new mysqli($host, $user, $password, $dbname);

        if ($this->dbConnection->connect_error) {
            die("Erro ao conectar com o banco de dados: " . $this->dbConnection->connect_error);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        // Carregar o conteúdo da nota do banco de dados
        $query = "SELECT conteudo FROM notas WHERE id = 1";
        $result = $this->dbConnection->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            $conn->send($row['conteudo']);
        } else {
            $conn->send(""); // Enviar vazio caso não haja nota
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Atualizar o banco de dados
        $stmt = $this->dbConnection->prepare("UPDATE notas SET conteudo = ? WHERE id = 1");
        if (!$stmt) {
            echo "Erro ao preparar a query: " . $this->dbConnection->error . PHP_EOL;
            return;
        }

        $stmt->bind_param("s", $msg);
        if (!$stmt->execute()) {
            echo "Erro ao executar a query: " . $stmt->error . PHP_EOL;
        }

        // Enviar a mensagem para todos os clientes conectados
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: " . $e->getMessage() . PHP_EOL;
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
