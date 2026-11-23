const fs = require('fs');
const file = 'views/auth/login.php';
let content = fs.readFileSync(file, 'utf8');

content = content.replace(
    'placeholder="name@company.com"',
    'placeholder="admin@attendance.local"'
);

content = content.replace(
    'placeholder="Enter your password"',
    'placeholder="Enter password (e.g. admin123)"'
);

const helpBlock = `                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="Enter password (e.g. admin123)" required>
                        </div>
                        <div class="form-text small text-muted mt-1 text-center" style="font-size: 0.75rem;">
                            Default: <strong class="text-primary font-monospace">admin@attendance.local</strong> &bull; Pass: <strong class="text-primary font-monospace">admin123</strong>
                        </div>
                    </div>`;

content = content.replace(
    /<div class="mb-4">[\s\S]*?<input type="password"[\s\S]*?<\/div>[\s\S]*?<\/div>/,
    helpBlock
);

fs.writeFileSync(file, content, 'utf8');
console.log("Updated login.php successfully!");
