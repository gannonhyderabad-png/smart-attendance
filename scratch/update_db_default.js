const fs = require('fs');
let c = fs.readFileSync('database/Database.php', 'utf8');
c = c.replace("$default = self::$config['default'] ?? 'mysql';", "$default = self::$config['default'] ?? 'sqlite';");
fs.writeFileSync('database/Database.php', c, 'utf8');
console.log('Successfully updated Database.php default driver to sqlite');
