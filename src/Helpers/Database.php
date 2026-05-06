<?php
namespace App\Helpers;

require_once __DIR__ . '../../../vendor/autoload.php';

use App\Convig\EnvParser;

$env = new EnvParser();
$env->load(__DIR__ . '../../../env');


class Database{
    private static ?Database $instance = null;
    private ?\PDO $pdo = null;

    private $config;

    // Private constructor (prevents direct instantiation)
    private function __construct()
    {
        $this->loadConfig();
        $this->connect();
    }
    
    /**
     * Load database configuration from environment
     */
    private function loadConfig()
    {
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'driver' => getenv('DB_DRIVER') ?: 'mysql'
        ];
        
        
        // Validate required fields
        if (!$this->config['name'] || !$this->config['user']) {
            throw new \Exception("Database name and user are required in .env file");
        }
    }
    
    /**
     * Establish database connection
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['name'],
                $this->config['charset']
            );
            
            $this->pdo = new \PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    // Clone prevention
    private function __clone(){}
    
    // Wakeup prevention (for unserialization)
    public function __wakeup(){
        throw new \RuntimeException("Cannot unserialize singleton");
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): \PDO{
        return $this->pdo;
    }
}



?>