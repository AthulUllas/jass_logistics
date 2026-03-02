# 🚀 JASS Logistics — Hostinger Deployment Guide

This guide ensures your website is successfully hosted on Hostinger with **zero data loss** and **real-time updates** from the admin panel.

---

## ✅ Step 1 — Upload Your Files
Upload **all files** from this folder to your Hostinger `public_html` directory via File Manager or FTP.

**This is a clean, optimized structure:**
- `index.html` (Main Website Frontend)
- `database.php` & `.env` (Database Connection)
- `db_setup.php` & `setup.sql` (Database Builders)
- `api/` (Dynamic PHP Backend)
- `admin/` (Content Management Panel)

---

## 💾 Step 2 — Create your MySQL Database
1. Log in to your **Hostinger hPanel**.
2. Go to **Databases → MySQL Databases**.
3. Create a new database (e.g., `u123456789_logistics`).
4. Create a database user and a strong password.
5. **Important:** Note down the **DB Name**, **DB User**, and **DB Password**.

---

## ⚙️ Step 3 — Configure the `.env` File
In your Hostinger File Manager, edit the `.env` file and fill in the details you just created:

```env
DB_HOST=localhost
DB_NAME=u123456789_logistics
DB_USER=u123456789_admin
DB_PASS=YourSecurePassword
```
*(On Hostinger, `DB_HOST` is usually `localhost`)*

---

## ⚡ Step 4 — Run the Migration (Zero Data Loss)
This step moves your existing data from `data.json` into the MySQL database.
1. Open your browser and visit: `https://yourdomain.com/db_setup.php`
2. You should see a list of green checkmarks showing tables were created and content was migrated.
3. **⚠️ Security Warning:** After you see the success message, **DELETE** `db_setup.php` from your File Manager immediately.

---

## 🔐 Step 5 — Access the Admin Panel
- **Login URL:** `https://yourdomain.com/admin/`
- **Default Username:** `admin`
- **Default Password:** `admin123`
*(Change your password immediately in the **Security Settings** tab of the dashboard)*

---

## 🛠️ Troubleshooting
- **500 Error:** Double-check your `.env` file for typos in the database name or password.
- **Images not loading:** Ensure the `uploads/` folder is present and has `755` permissions.
- **Admin login fails:** Ensure you ran `db_setup.php` first.

---

### Why this is the "Best Service" for you:
- **No Node.js needed**: PHP runs natively and faster on Hostinger.
- **Persistent Data**: SQL is industry-standard and won't reset like JSON might if permissions change.
- **Fast Updates**: Changes in the admin panel are saved instantly to the database.
