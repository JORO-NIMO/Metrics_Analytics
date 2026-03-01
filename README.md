# Maternal Health Awareness Platform

## Project Overview

**Maternal Health Awareness** is a web-based platform developed by **UNISEN Charity Uganda** to provide comprehensive maternal health information, emergency guidance, and support services. The platform aims to improve maternal health awareness and provide accessible healthcare resources to communities in need.

## Features

- **Home Page**: Welcome and overview of maternal health services with an interactive slideshow
- **Services Page**: Detailed information about available maternal health services including antenatal care, skilled delivery, and postnatal care
- **Emergency Page**: Critical emergency information with severity levels and immediate action guidance
- **Contact Page**: Easy-to-use contact form for users to reach out for support and assistance
- **Responsive Design**: Mobile-friendly interface for accessibility across devices

## Technology Stack

- **Frontend**: HTML5, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: PHP-compatible web server

## Project Structure

```
MH/
├── api/                      # Backend API endpoints
│   ├── db.php               # Database connection configuration
│   ├── get_services.php     # Retrieve maternal health services
│   ├── get_emergency.php    # Retrieve emergency information
│   └── submit_contact.php   # Handle contact form submissions
│
├── frontend/                 # Frontend files
│   ├── index.html           # Home page
│   ├── services.html        # Services page
│   ├── emergency.html       # Emergency information page
│   ├── contact.html         # Contact form page
│   ├── main.js              # Main JavaScript logic
│   └── style.css            # Styling
│
├── database/                 # Database files
│   └── maternal_health.sql  # Database schema and initial data
│
├── Assets/
│   └── Images/              # Project images and media
│
└── README.md                # This file
```

## Installation & Setup

### Prerequisites
- Web server with PHP 7.0+ support
- MySQL database server
- Modern web browser

### Steps

1. **Clone/Download the Project**
   ```bash
   git clone <repository-url>
   cd MH
   ```

2. **Create Database**
   - Open MySQL command line or phpMyAdmin
   - Import the database schema:
   ```sql
   mysql -u [username] -p < database/maternal_health.sql
   ```

3. **Configure Database Connection**
   - Edit `api/db.php` to add your database credentials:
   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $dbname = "maternal_health";
   ```

4. **Deploy Files**
   - Upload all files to your web server's document root (e.g., `htdocs/` for Apache)

5. **Access the Application**
   - Open your browser and navigate to: `http://localhost/MH/frontend/`

## Usage

### For Users
1. Navigate to the **Home** page to learn about maternal health
2. Visit **Services** to explore available maternal health programs
3. Check **Emergency** section for critical health information and guidance
4. Use **Contact** page to reach out with questions or requests

### For Administrators
- View contact form submissions in the `contact_messages` table
- Manage services in the `services` table
- Update emergency information in the `emergency_info` table

## Database Tables

### services
- `id`: Service ID (Primary Key)
- `service_name`: Name of the service
- `description`: Service description

### emergency_info
- `id`: Emergency ID (Primary Key)
- `title`: Emergency title
- `short_description`: Brief description
- `detailed_description`: Detailed information
- `severity`: Emergency level (Low, Medium, High)
- `advice`: Recommended action

### contact_messages
- `id`: Message ID (Primary Key)
- `name`: Sender's name
- `phone`: Sender's phone number
- `message`: Message content
- `created_at`: Timestamp of submission

## API Endpoints

- `GET /api/get_services.php` - Retrieve all services
- `GET /api/get_emergency.php` - Retrieve emergency information
- `POST /api/submit_contact.php` - Submit contact form

## Contributing

To contribute to this project:
1. Identify improvements or bug fixes
2. Make changes to the appropriate files
3. Test thoroughly on your local environment
4. Submit your contributions

## License

This project is maintained by UNISEN Charity Uganda.

## Support

For questions or support regarding this platform, please:
- Use the Contact page to submit inquiries
- Reach out to 0704753591. 

## Acknowledgments

This platform is developed by UNISEN Charity Uganda to support maternal health awareness and emergency preparedness in communities.

---

**Last Updated**: March 1, 2026
