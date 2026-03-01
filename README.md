# Maternal Health Uganda — Setup Guide

## Project Structure
```
MaternalHealthUganda/
├── backend/
│   ├── config.php          ← DB connection class (edit credentials here)
│   ├── connection.php      ← Test your DB connection
│   ├── login.php           ← Handles user login
│   ├── signup.php          ← Handles user registration
│   ├── logout.php          ← Destroys session
│   ├── savetracker.php     ← Saves pregnancy tracking data
│   ├── getreviews.php      ← Returns approved reviews as JSON
│   ├── getuserdata.php     ← Returns logged-in user data as JSON
│   └── submitreview.php    ← Saves a new review
├── frontend/
│   ├── index.html          ← Main homepage
│   ├── login.html          ← Login page
│   ├── signup.html         ← Registration page
│   ├── style.css           ← Main stylesheet
│   ├── login.css           ← Login/signup styles
│   ├── signup.css          ← Signup extra styles
│   ├── slideshow.js        ← Hero image slideshow
│   ├── tracker.js          ← Pregnancy tracker logic
│   └── review.js           ← Review slideshow
├── database/
│   └── maternal_health_uganda.sql  ← Full DB schema + sample data
└── Images/
    └── (all SVG icons and JPEG images)
```

---

## STEP 1 — Install Required Software

You need these installed on your computer:

| Software | Download | Purpose |
|---|---|---|
| XAMPP | https://www.apachefriends.org | Apache + MySQL + PHP |
| VS Code (optional) | https://code.visualstudio.com | Code editor |

---

## STEP 2 — Start XAMPP

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should turn green ✅

---

## STEP 3 — Create the Database

**Option A: Using phpMyAdmin (easiest)**
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Type database name: `maternal_health_uganda`
4. Click **Create**
5. Click on the new database in the left sidebar
6. Click the **"Import"** tab at the top
7. Click **"Choose File"** → select `database/maternal_health_uganda.sql`
8. Scroll down → click **"Import"** button
9. You should see "Import has been successfully finished" ✅

**Option B: Using MySQL command line**
```bash
mysql -u root -p
source /path/to/database/maternal_health_uganda.sql
```

---

## STEP 4 — Place Project in XAMPP

1. Copy the entire **`MaternalHealthUganda`** folder
2. Paste it into XAMPP's web root folder:
   - **Windows:** `C:\xampp\htdocs\MaternalHealthUganda`
   - **Mac/Linux:** `/opt/lampp/htdocs/MaternalHealthUganda`

Your folder structure inside htdocs should look like:
```
htdocs/
└── MaternalHealthUganda/
    ├── backend/
    ├── frontend/
    ├── database/
    └── Images/
```

---

## STEP 5 — Configure Database Password (if needed)

Open `backend/config.php` and update if your MySQL has a password:

```php
private $username = "root";
private $password = "";   // ← Put your MySQL password here if you have one
```

Most XAMPP installations have **no password** by default, so leave it empty `""`.

---

## STEP 6 — Test the Connection

Open your browser and go to:
```
http://localhost/MaternalHealthUganda/backend/connection.php
```

You should see:
```
✅ Connected to maternal_health_uganda successfully!
📊 Tables found: users, pregnancy_tracking, reviews, health_tips, appointments
👤 Users in database: 1
```

If you see ❌ an error, check:
- Is XAMPP MySQL running? (green in Control Panel)
- Is the database name exactly `maternal_health_uganda`?
- Is your password correct in `config.php`?

---

## STEP 7 — Open the Website

Go to:
```
http://localhost/MaternalHealthUganda/frontend/index.html
```

The full website should load! 🎉

---

## STEP 8 — Test Login

A sample admin account has been created:
- **Email:** `admin@maternalhealthuganda.org`
- **Password:** `password`

Or register a new account at:
```
http://localhost/MaternalHealthUganda/frontend/signup.html
```

---

## Page URLs

| Page | URL |
|---|---|
| Homepage | `http://localhost/MaternalHealthUganda/frontend/index.html` |
| Login | `http://localhost/MaternalHealthUganda/frontend/login.html` |
| Sign Up | `http://localhost/MaternalHealthUganda/frontend/signup.html` |
| Test DB | `http://localhost/MaternalHealthUganda/backend/connection.php` |

---

## Features

- ✅ **Pregnancy Tracker** — Enter last period date, get week, trimester, due date
- ✅ **Baby Growth Info** — Week-by-week baby size and development facts
- ✅ **Health Tips** — Weekly personalized health advice (42 weeks of tips in DB)
- ✅ **User Registration** — Secure signup with hashed passwords
- ✅ **User Login** — Session-based authentication
- ✅ **Reviews** — Submit and view community reviews
- ✅ **Hero Slideshow** — Auto-rotating image carousel
- ✅ **Responsive** — Works on mobile and desktop

---

## Common Errors & Fixes

| Error | Cause | Fix |
|---|---|---|
| "Connection failed" | MySQL not running | Start MySQL in XAMPP |
| "Unknown database" | DB not created | Run the SQL file in phpMyAdmin |
| "404 Not Found" | Wrong folder path | Make sure folder is inside `htdocs` |
| Images not showing | Wrong path | Confirm `Images/` folder is at root of project |
| Login not working | Wrong path | Ensure `backend/` folder is one level up from `frontend/` |
