<?php
// db_connect.php - Connexion à la base de données
$host = '127.0.0.1';
$db_name = 'stagematch';
$username = 'root';
$password = ''; // Par défaut vide sous Laragon

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (\PDOException $e) {
    // En production, ne pas afficher l'erreur exacte
    // die('Erreur de connexion à la base de données');
    
    // Pour le développement
    die("Erreur de connexion : " . $e->getMessage());
}

// Classe wrapper pour compatibilité avec l'ancien code si nécessaire
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'stagematch';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
