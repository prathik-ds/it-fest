# 🚀 FusionVerse Deployment Guide (Hostinger)

This guide explains how to move your **FusionVerse IT-Fest** project from your local computer to your Hostinger production server.

---

## 📋 Pre-Deployment Checklist
1. [ ] **Hostinger Hosting Plan**: Active and domain connected.
2. [ ] **Database Backup**: A `.sql` file of your local database.
3. [ ] **Project Files**: All files and folders in your project directory.

---

## 🛠️ Step 1: Prepare Database on Hostinger
1. Log in to your **Hostinger hPanel**.
2. Go to **Databases** > **MySQL Databases**.
3. Create a new database:
   - **Database Name**: e.g., `u123456789_fusion`
   - **MySQL User**: e.g., `u123456789_admin`
   - **Password**: Create a strong password (copy this!).
4. Click **Create**.
5. Once created, click **Enter phpMyAdmin** for that database.
6. Click **Import** at the top, select your `fusionverse_database.sql` file, and click **Go**.

---

## ⚙️ Step 2: Configure Production Settings
You need to tell the app about your new Hostinger database.
1. Open `config/db.php`.
2. Find the `// PRODUCTION SETTINGS (Hostinger)` section.
3. Fill in your details created in Step 1:
   ```php
   $db   = 'u123456789_fusion'; // Your DB name from Hostinger
   $user = 'u123456789_admin';  // Your DB user from Hostinger
   $pass = 'your_strong_password'; // Your DB password
   ```
4. Save the file.

---

## 📤 Step 3: Upload Files
1. In Hostinger hPanel, go to **Files** > **File Manager**.
2. Navigate to the `public_html` folder.
3. **Upload Method A (Recommended):**
   - ZIP all your project files (except `.git` or `.bat` files).
   - Upload the ZIP to `public_html`.
   - Right-click the ZIP and select **Extract**.
4. **Upload Method B (FTP):**
   - Use FileZilla to connect to your Hostinger account.
   - Upload all files into `public_html`.

---

## 🔒 Step 4: Security & Permissions
1. **.htaccess**: Ensure the `.htaccess` file is uploaded to `public_html`. This protects your sensitive files.
2. **Permissions**: Standard folders should be `755` and files `644` (Hostinger sets this by default).
3. **Assets**: Ensure `assets/img/events/` is writable if you plan to upload event logos from the admin panel.

---

## 🌍 Step 5: Verify Deployment
1. Open your domain (e.g., `https://yourdomain.com`).
2. Test the **Login** and **Registration**.
3. Check **Admin Panel** to ensure data is fetching correctly.

---

## ⚠️ Important Production Tips
- **Disable Errors**: In production, the app is configured to hide detailed errors to keep hackers away. If things don't work, check the `error.log` file in your root directory.
- **SSL**: Hostinger provides free SSL. Ensure it's active so your site uses `https://`.
- **Delete Installation Scripts**: Once the database is set up, you can delete `fusionverse_database.sql` and `install.php` from your server for extra security.

---

### Questions?
If you face any "Database Connection Failed" errors, double-check your credentials in `config/db.php`.
