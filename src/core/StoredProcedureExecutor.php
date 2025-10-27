<?php

namespace App\core;

use App\config\Database;
use PDO;
use PDOException;

class StoredProcedureExecutor {
    private static ?self $instance = null;
    private PDO $db;

    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function execute(
        string $procedure,
        array $params = [],
        bool $fetchAll = false,
        ?string $class = null,
        bool $isNonQuery = false
    ) {
        try {
            $stmt = $this->db->prepare($procedure);

            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue(":$key", $value, $paramType);
            }

            $executionResult = $stmt->execute();

            if ($isNonQuery) {
                return $executionResult;
            }

            if ($class) {
                $data = $fetchAll
                    ? $stmt->fetchAll(PDO::FETCH_CLASS, $class)
                    : $stmt->fetchObject($class);
            } else {
                $data = $fetchAll
                    ? $stmt->fetchAll(PDO::FETCH_ASSOC)
                    : $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $stmt->closeCursor();
            return $data ?: ($fetchAll ? [] : null);
        } catch (PDOException $e) {
            error_log("StoredProcedureExecutor error: " . $e->getMessage());
            return $isNonQuery ? false : null;
        }
    }
}
