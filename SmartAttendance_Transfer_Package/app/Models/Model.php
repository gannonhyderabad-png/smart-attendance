<?php

namespace App\Models;

use Database\Database;
use PDO;

abstract class Model {
    protected static function db(): PDO {
        return Database::getConnection();
    }
}
