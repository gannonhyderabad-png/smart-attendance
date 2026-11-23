const https = require('https');
const http = require('http');

function checkUrl(url) {
    return new Promise((resolve) => {
        const mod = url.startsWith('https') ? https : http;
        const req = mod.get(url, { timeout: 15000 }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({ status: res.statusCode, headers: res.headers, body: data.substring(0, 500) });
            });
        });
        req.on('error', err => resolve({ error: err.message }));
        req.on('timeout', () => { req.destroy(); resolve({ error: 'Timeout after 15s' }); });
    });
}

async function run() {
    console.log('Testing Render URL: https://smart-attendance-hw9c.onrender.com/health');
    const rHealth = await checkUrl('https://smart-attendance-hw9c.onrender.com/health');
    console.log('Health:', JSON.stringify(rHealth));

    console.log('\nTesting Render URL: https://smart-attendance-hw9c.onrender.com/login');
    const rLogin = await checkUrl('https://smart-attendance-hw9c.onrender.com/login');
    console.log('Login:', JSON.stringify(rLogin));

    console.log('\nTesting Render Punch URL: https://smart-attendance-hw9c.onrender.com/p/EMP980');
    const rPunch = await checkUrl('https://smart-attendance-hw9c.onrender.com/p/EMP980');
    console.log('Punch:', JSON.stringify(rPunch));

    console.log('\nTesting Localhost Port 8000: http://localhost:8000/health');
    const rLocal = await checkUrl('http://localhost:8000/health');
    console.log('Local:', JSON.stringify(rLocal));
}

run();
