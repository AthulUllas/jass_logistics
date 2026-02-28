# 🚀 Deployment Guide — Hostinger Hosting

## What Changed
The website backend has been **completely migrated from Node.js to PHP + MySQL**.  
No Node.js server is needed anymore. Everything runs on PHP (which Hostinger supports natively).

---

## Files to Upload

Upload **all files** to your Hostinger `public_html` folder **except**:
- `node_modules/` ← **Do NOT upload** (very large, not needed)
- `start_server.bat`, `startup.vbs` ← Not needed on Hostinger

Everything else should be uploaded.

---

## Step-by-Step Hostinger Setup

### Step 1 — Create MySQL Database
1. Log in to **Hostinger hPanel**
2. Go to **Databases → MySQL Databases**
3. Create a new database, e.g. `u123456789_logistics`
4. Create a database user with a strong password
5. Assign the user to the database (All Privileges)

### Step 2 — Update `.env` File
Open `.env` in your uploaded files and fill in your Hostinger MySQL credentials:

```
DB_HOST=localhost
DB_NAME=u123456789_logistics
DB_USER=u123456789_admin
DB_PASS=YourSecurePassword
```

> You can find exact values in **hPanel → MySQL Databases**

### Step 3 — Run the Database Setup Script
Once uploaded, open in your browser:
```
https://yourdomain.com/db_setup.php
```

You should see green checkmarks confirming tables were created and data was migrated.

### Step 4 — Delete db_setup.php ⚠️
**IMPORTANT:** After setup is successful, delete `db_setup.php` from your hosting to prevent unauthorized access.

In hPanel → File Manager, find and delete `db_setup.php`.

### Step 5 — Test Your Website
- Visit `https://yourdomain.com/` — the homepage should load with all content
- Visit `https://yourdomain.com/admin/` — login with `admin` / `admin123`
- Change the admin password immediately via **Security Settings** in the admin panel

---

## Folder Structure After Upload

```
public_html/
├── index.php           ← Main website (PHP)
├── database.php        ← MySQL connection
├── db_setup.php        ← DELETE after running once
├── setup.sql           ← Reference schema (not needed at runtime)
├── .htaccess           ← URL routing rules
├── .env                ← MySQL credentials ← UPDATE THIS
├── api/
│   ├── content.php     ← Site content API
│   ├── upload.php      ← Image upload API
│   ├── login.php       ← Admin login API
│   └── admin_settings.php ← Admin credentials API
├── admin/
│   ├── index.html      ← Admin login page
│   └── dashboard.html  ← Admin control panel
├── uploads/            ← Uploaded images stored here
└── public/             ← Static assets (logo, etc.)
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Blank page / 500 error | Check `.env` DB credentials are correct |
| "Database connection failed" | Ensure DB name, user, and password match hPanel |
| Admin login fails | Run `db_setup.php` to seed admin credentials |
| Images not loading | Check `uploads/` folder has 755 permissions in File Manager |
| `.htaccess` not working | Ensure Hostinger has mod_rewrite enabled (it does by default) |

---

## Admin Credentials (Default)
- **Username:** `admin`
- **Password:** `admin123`

> ⚠️ Change these immediately after first login via Admin Panel → Security Settings
