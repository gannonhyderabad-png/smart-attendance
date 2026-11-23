const https = require('https');

function request(url, options = {}, postData = null) {
    return new Promise((resolve, reject) => {
        const u = new URL(url);
        const opt = {
            hostname: u.hostname,
            port: 443,
            path: u.pathname + u.search,
            method: options.method || 'GET',
            headers: options.headers || {}
        };
        const req = https.request(opt, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve({ statusCode: res.statusCode, headers: res.headers, body: data }));
        });
        req.on('error', reject);
        if (postData) req.write(postData);
        req.end();
    });
}

async function main() {
    const r1 = await request('https://smart-attendance-hw9c.onrender.com/login');
    const cookie1 = r1.headers['set-cookie'] ? r1.headers['set-cookie'].map(c => c.split(';')[0]).join('; ') : '';
    const csrfMatch = r1.body.match(/name="_csrf_token" value="([^"]+)"/);
    const csrfToken = csrfMatch ? csrfMatch[1] : '';

    const postBody = `email=admin%40attendance.local&password=admin123&_csrf_token=${csrfToken}`;
    const r2 = await request('https://smart-attendance-hw9c.onrender.com/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Content-Length': Buffer.byteLength(postBody),
            'Cookie': cookie1
        }
    }, postBody);

    console.log("Login Post ->", r2.statusCode, r2.headers['location']);
    const sessionCookie = r2.headers['set-cookie'] ? r2.headers['set-cookie'].map(c => c.split(';')[0]).join('; ') : cookie1;

    const endpoints = ['/dashboard', '/employees', '/attendance', '/leaves', '/reports'];
    for (const ep of endpoints) {
        const r = await request(`https://smart-attendance-hw9c.onrender.com${ep}`, {
            headers: { 'Cookie': sessionCookie }
        });
        console.log(`${ep} -> Status ${r.statusCode}, Content-Length: ${r.body.length}`);
        if (r.body.includes("Application Notice") || r.body.includes("Fatal error") || r.body.includes("error")) {
            const errSnippet = r.body.match(/Application Notice[\s\S]{0,300}/) || r.body.substring(0, 300);
            console.log(`  ERROR in ${ep}:`, errSnippet);
        }
    }
}

main().catch(err => console.error(err));
