const { spawn } = require('child_process');
const http = require('http');

const phpBin = 'C:\\Users\\rampr\\AppData\\Local\\Microsoft\\WinGet\\Packages\\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\\php.exe';
const server = spawn(phpBin, ['-S', '127.0.0.1:8000', 'index.php'], {
    cwd: 'c:\\Users\\rampr\\.gemini\\antigravity\\scratch\\Helpdesk',
    env: { ...process.env, PHP_CLI_SERVER_WORKERS: '4' }
});

let serverLogs = '';
server.stderr.on('data', d => serverLogs += d.toString());
server.stdout.on('data', d => serverLogs += d.toString());

function test(path) {
    return new Promise(resolve => {
        http.get('http://127.0.0.1:8000' + path, res => {
            let body = '';
            res.on('data', c => body += c);
            res.on('end', () => resolve({ path, status: res.statusCode, body: body.substring(0, 200) }));
        }).on('error', err => resolve({ path, error: err.message }));
    });
}

setTimeout(async () => {
    console.log('--- TESTING LOCAL ROUTES ---');
    console.log(await test('/health'));
    console.log(await test('/login'));
    console.log(await test('/p/EMP980'));
    console.log(await test('/p/EMP001'));
    console.log(await test('/p/EMP-481'));
    console.log(await test('/p/EMP-005'));
    server.kill();
    console.log('--- SERVER LOGS ---');
    console.log(serverLogs.substring(0, 500));
    process.exit(0);
}, 2000);
