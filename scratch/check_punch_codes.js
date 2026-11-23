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

async function check() {
    // 1. Login
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

    const sessionCookie = r2.headers['set-cookie'] ? r2.headers['set-cookie'].map(c => c.split(';')[0]).join('; ') : cookie1;

    // 2. Fetch /employees
    const r3 = await request('https://smart-attendance-hw9c.onrender.com/employees', {
        headers: { 'Cookie': sessionCookie }
    });

    console.log("Employees page status:", r3.statusCode);
    const codes = [...r3.body.matchAll(/\/p\/([^"'\s?]+)/g)].map(m => m[1]);
    const uniqueCodes = [...new Set(codes)];
    console.log("Found punch codes in Employee Directory:", uniqueCodes);

    // 3. Test each punch code
    for (const code of uniqueCodes) {
        const punchUrl = `https://smart-attendance-hw9c.onrender.com/p/${code}`;
        const pr = await request(punchUrl);
        console.log(`Punch URL: /p/${code} -> Status ${pr.statusCode}`);
        if (pr.statusCode !== 200) {
            console.log("  Non-200 Response:", pr.body.substring(0, 300));
        } else if (pr.body.includes("Employee Not Found") || pr.body.includes("Account Inactive")) {
            console.log("  Notice on punch page:", pr.body.match(/<h3>([^<]+)<\/h3>/)?.[1] || "Error text in body");
        } else {
            const empName = pr.body.match(/class="hero-name">([^<]+)<\/div>/)?.[1] || "Name not matched";
            console.log(`  Loaded OK for: ${empName}`);
        }
    }
}

check().catch(console.error);
