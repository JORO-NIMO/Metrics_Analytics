# Maternal Health Uganda

A web-based maternal healthcare platform designed to support expectant mothers across Uganda with pregnancy tracking, personalised weekly health tips, and community reviews

---

## Tech Stack

- **Backend:** PHP 8+
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Server:** Apache (XAMPP)

---

## Getting Started

### Requirements
- XAMPP (Apache + MySQL)
- A modern web browser

### Installation

1. Clone or download this repository into your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\MaternalHealthUganda\
   ```

2. Start **Apache** and **MySQL** from the XAMPP Control Panel.

3. Open phpMyAdmin at `http://localhost/phpmyadmin`, create a database named `maternal_health_uganda`, then import:
   ```
   database/maternal_health_uganda.sql
   ```

4. Open the application in your browser:
   ```
   http://localhost/MaternalHealthUganda/frontend/index.html
   ```

### Admin Access
| Field | Value |
|---|---|
| URL | `http://localhost/MaternalHealthUganda/frontend/metrics_dashboard.html` |
| Email | `admin@maternalhealthuganda.org` |
| Password | `Admin@1234` |

---

## Features

- **Pregnancy Tracker** — calculates current week, due date, and displays a week-specific health tip
- **Health Tips** — 42 curated tips covering all 40 weeks of pregnancy
- **User Reviews** — mothers can submit and read community reviews
- **Feedback System** — users can report inaccurate health information
- **User Survey** — collects satisfaction ratings across five platform dimensions
- **Admin Dashboard** — monitors platform quality indicators in real time

---


## Project Structure

```
MaternalHealthUganda/
├── backend/
│   ├── config.php                 — Database connection
│   ├── metrics_logger.php         — Central logging class
│   ├── login.php                  — User authentication
│   ├── signup.php                 — User registration
│   ├── savetracker.php            — Pregnancy tracker logic
│   ├── submitreview.php           — Review submission
│   ├── submitfeedback.php         — Content inaccuracy reports
│   ├── submitsurvey.php           — Survey responses
│   ├── logpageview.php            — Page view logging
│   ├── getmetrics.php             — Dashboard metrics API
│   ├── getexperiments.php         — Investigation data API
│   └── computeresults.php         — Statistical computation
├── frontend/
│   ├── index.html                 — Main platform page
│   ├── login.html                 — Login page
│   ├── signup.html                — Registration page
│   ├── survey.html                — User satisfaction survey
│   ├── metrics_dashboard.html     — Admin dashboard
│   ├── style.css                  — Main stylesheet
│   ├── auth.css                   — Login and signup styles
│   ├── tracker.js                 — Tracker UI logic
│   ├── slideshow.js               — Hero slideshow
│   └── review.js                  — Reviews slider
├── database/
│   └── maternal_health_uganda.sql — Full database schema and seed data
└── README.md
```

---

## Contributing

Pull requests are welcome. For major changes please open an issue first to discuss what you would like to change.

---

## License

This project was developed for academic purposes at the Mbarara  University  Of Science and Technology 
