# Beyond The Map Travel & Tours - Management System

A comprehensive web-based management system for travel and tours operations, built with HTML, CSS, JavaScript, and PHP.

## Features

- **Dashboard**: Overall operational overview with key metrics and urgent action queue
- **Account Executive**: Customer processing and payment management
- **Passport & Visa**: Application tracking and document management
- **Schedule & Rates**: Tour scheduling, availability, and pricing management
- **CRM**: Customer relationship management with loyalty tiers
- **Facilities Reservation**: Asset and facility management

## Project Structure

```
programming/
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── main.js            # JavaScript for interactivity
├── includes/
│   ├── sidebar.php        # Navigation sidebar component
│   └── header.php         # Page header component
├── config/
│   └── database.php       # Database configuration
├── api/
│   ├── get_customers.php  # Customer data API
│   └── get_applications.php # Applications data API
├── index.php              # Dashboard page
├── account_executive.php  # Account Executive page
├── passport_visa.php      # Passport & Visa page
├── schedule_rates.php     # Schedule & Rates page
├── crm.php                # CRM page
└── facilities.php         # Facilities Reservation page
```

## Setup Instructions

### 1. Web Server Setup

You need a web server with PHP support. Options:
- **XAMPP** (Windows/Mac/Linux)
- **WAMP** (Windows)
- **MAMP** (Mac)
- **PHP Built-in Server** (for development)

### 2. Using PHP Built-in Server (Development)

```bash
# Navigate to the project directory
cd "C:\Users\areva\OneDrive\Desktop\BPM 101\programming"

# Start PHP server
php -S localhost:8000
```

Then open your browser and go to: `http://localhost:8000`

### 3. Using XAMPP

1. Install XAMPP
2. Copy the project folder to `C:\xampp\htdocs\`
3. Start Apache from XAMPP Control Panel
4. Open browser: `http://localhost/programming/`

### 4. Database Setup (Optional)

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `beyond_the_map`
3. Update `config/database.php` with your database credentials
4. Create necessary tables (you can add this later)

## Features Implemented

### Interactive Elements

- ✅ Clickable navigation sidebar with active page highlighting
- ✅ Search functionality in tables
- ✅ Filter buttons (ready for backend integration)
- ✅ Action links (View, Review Document, Remind)
- ✅ Checkbox selection for table rows
- ✅ Calendar date selection
- ✅ Notification icons
- ✅ Profile menu access
- ✅ Main action buttons (Request Information, Send Payment Reminder)

### Responsive Design

- ✅ Mobile-friendly sidebar (collapses on small screens)
- ✅ Responsive grid layouts
- ✅ Flexible table displays

## Customization

### Colors

Edit CSS variables in `css/style.css`:

```css
:root {
    --primary-teal: #2d5a5a;
    --light-teal: #3d7a7a;
    --accent-blue: #3b82f6;
    /* ... more colors ... */
}
```

### Adding New Pages

1. Create a new PHP file (e.g., `new_page.php`)
2. Include sidebar and header:
```php
<?php
$pageTitle = "Page Title";
$pageSubtitle = "Page Subtitle";
include 'includes/sidebar.php';
include 'includes/header.php';
?>
```
3. Add navigation link in `includes/sidebar.php`

## Next Steps

1. **Database Integration**: Connect to MySQL/MariaDB and create tables
2. **Authentication**: Add login system
3. **Data Management**: Implement CRUD operations
4. **Real Charts**: Integrate Chart.js or similar library
5. **File Uploads**: Add document upload functionality
6. **Email Notifications**: Implement email reminders
7. **Reports**: Add reporting and export features

## Browser Support

- Chrome (recommended)
- Firefox
- Edge
- Safari

## Notes

- This is a frontend-focused implementation
- Backend API endpoints are placeholders
- Replace sample data with actual database queries
- Add proper error handling and validation
- Implement security measures (SQL injection prevention, XSS protection)

## License

This project is created for educational and business purposes.

