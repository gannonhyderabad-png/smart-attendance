const fs = require('fs');
const path = 'C:/Users/rampr/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.ini-development';
const target = 'C:/Users/rampr/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.ini';

let text = fs.readFileSync(path, 'utf8');
text = text.replace(';extension_dir = "ext"', 'extension_dir = "ext"');

const exts = ['curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo_sqlite', 'pdo_mysql', 'pdo_pgsql', 'sqlite3'];
for (const e of exts) {
    text = text.replace(';extension=' + e, 'extension=' + e);
}

fs.writeFileSync(target, text, 'utf8');
console.log('php.ini created successfully with required extensions enabled!');
