<?php


class PDB
{

    private $connection = false;

    public function __construct($options = [])
    {


        $host = DB::getGlobalSettings()->dbSettigns['host'];
        $db = DB::getGlobalSettings()->dbSettigns['database'];
        $port = DB::getGlobalSettings()->dbSettigns['port'] | 3306;
        $username = DB::getGlobalSettings()->dbSettigns['user'];
        $password = DB::getGlobalSettings()->dbSettigns['password'];


        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($default_options, $options);
        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

        //parent::__construct($dsn,$username,$password,$options);


        try {
            $this->connection = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }


    }

    public function lastID() {
        return $this->connection->lastInsertId();
    }

    public function run($sql = false, $args = NULL)
    {
        if (!$sql) {
            return false;
        }

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

        if (!$args) {
            try {
                return $this->connection->query($sql);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }

        } else {

            try {
                $stmt = $this->connection->prepare($sql);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            try {
                $stmt->execute($args);
            } catch (\PDOException $e) {
                print_r($stmt->errorInfo());
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            
            $stmt->lastID = $this->connection->lastInsertId();

            return $stmt;
        }


        /*
        $handle = $link->prepare('select Username from Users where UserId = ? or Username = ? limit ?');

        $handle->bindValue(1, 100);
        $handle->bindValue(2, 'Bilbo Baggins');
        $handle->bindValue(3, 5);

        $handle->execute();

        // Using the fetchAll() method might be too resource-heavy if you're selecting a truly massive amount of rows.
        // If that's the case, you can use the fetch() method and loop through each result row one by one.
        // You can also return arrays and other things instead of objects.  See the PDO documentation for details.
        $result = $handle->fetchAll(PDO::FETCH_OBJ);

        foreach ($result as $row) {
            print($row->Username);
        }
        */

    }

}


?>