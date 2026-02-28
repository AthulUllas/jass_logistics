const express = require('express');
const fs = require('fs');
const path = require('path');
const cors = require('cors');
const bodyParser = require('body-parser');
const multer = require('multer');

const app = express();
const PORT = 3000;
const DATA_FILE = path.join(__dirname, 'data.json');
const ADMIN_FILE = path.join(__dirname, 'admin.json');
const UPLOADS_DIR = path.join(__dirname, 'uploads');

// Ensure uploads directory exists
if (!fs.existsSync(UPLOADS_DIR)) {
    fs.mkdirSync(UPLOADS_DIR, { recursive: true });
}

// Multer Config
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, UPLOADS_DIR);
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});
const upload = multer({ storage: storage });

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

// Logging Middleware
app.use((req, res, next) => {
    console.log(`${new Date().toISOString()} - ${req.method} ${req.url}`);
    next();
});

app.use(bodyParser.json());
app.use(express.static('public')); // Serve static files from 'public' directory
app.use('/admin', express.static('admin')); // Serve admin files
app.use('/uploads', express.static('uploads')); // Serve uploaded files

// API Endpoints

// Get all content
app.get('/api/content', (req, res) => {
    fs.readFile(DATA_FILE, 'utf8', (err, data) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ error: 'Failed to read data' });
        }
        res.json(JSON.parse(data));
    });
});

// Update content
app.post('/api/content', (req, res) => {
    const newContent = req.body;

    // Validate (basic)
    if (!newContent) {
        return res.status(400).json({ error: 'No data provided' });
    }

    fs.writeFile(DATA_FILE, JSON.stringify(newContent, null, 2), (err) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ error: 'Failed to save data' });
        }
        res.json({ success: true, message: 'Content updated successfully' });
    });
});

// Upload image
app.post('/api/upload', upload.single('image'), (req, res) => {
    if (!req.file) {
        return res.status(400).json({ error: 'No file uploaded' });
    }
    const filePath = `/uploads/${req.file.filename}`;
    res.json({ success: true, filePath: filePath });
});

// Admin Login (Simple hardcoded for demo purposes)
// Admin Login
app.post('/api/login', (req, res) => {
    try {
        if (!req.body) {
            return res.status(400).json({ success: false, message: 'Request body is missing' });
        }

        const { username, password } = req.body;

        fs.readFile(ADMIN_FILE, 'utf8', (err, data) => {
            if (err) {
                console.error('Error reading admin file:', err);
                return res.status(500).json({ success: false, message: 'Failed to read credentials' });
            }

            const admin = JSON.parse(data);
            if (username === admin.username && password === admin.password) {
                res.json({ success: true, token: 'fake-jwt-token-for-demo' });
            } else {
                res.status(401).json({ success: false, message: 'Invalid credentials' });
            }
        });
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({ success: false, message: 'Internal Server Error' });
    }
});

// Get admin settings
app.get('/api/admin/settings', (req, res) => {
    fs.readFile(ADMIN_FILE, 'utf8', (err, data) => {
        if (err) {
            return res.status(500).json({ error: 'Failed to read admin data' });
        }
        res.json(JSON.parse(data));
    });
});

// Update admin settings
app.post('/api/admin/settings', (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) {
        return res.status(400).json({ error: 'Username and password are required' });
    }

    fs.writeFile(ADMIN_FILE, JSON.stringify({ username, password }, null, 2), (err) => {
        if (err) {
            return res.status(500).json({ error: 'Failed to update admin data' });
        }
        res.json({ success: true, message: 'Admin settings updated successfully' });
    });
});

// Start Server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
