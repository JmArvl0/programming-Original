# Quick Start Guide

## Getting Started in 3 Steps

### Step 1: Start PHP Server

Open PowerShell or Command Prompt in this directory and run:

```bash
php -S localhost:8000
```

### Step 2: Open in Browser

Navigate to: **http://localhost:8000**

### Step 3: Explore the Pages

Click through the navigation menu to see all pages:
- Dashboard (index.php)
- Account Executive
- Passport & Visa
- Schedule & Rates
- CRM
- Facilities Reservation

## What's Clickable?

✅ **Navigation Menu** - Click any menu item to navigate between pages
✅ **Search Boxes** - Type to filter table rows
✅ **Filter Buttons** - Click to see filter options (alerts for now)
✅ **Action Links** - "View", "Review Document", "Remind" buttons
✅ **Checkboxes** - Select table rows (click row to select)
✅ **Calendar Days** - Click dates to select them
✅ **Notification Icons** - Click to see notifications
✅ **Profile Picture** - Click to access profile menu
✅ **Action Buttons** - "Request all Information", "Send Payment Reminder"

## Testing Features

1. **Search**: Type a customer name in any search box
2. **Select Rows**: Click checkboxes or click anywhere on a table row
3. **Select All**: Click the checkbox in table header
4. **Calendar**: Click different dates in Schedule & Rates page
5. **Navigation**: Click different menu items in sidebar

## Next Steps

- Connect to a database (see `config/database.php`)
- Add authentication/login system
- Implement real data fetching from database
- Add file upload for documents
- Integrate real chart libraries (Chart.js, etc.)

## Troubleshooting

**Page not loading?**
- Make sure PHP is installed: `php -v`
- Check if port 8000 is available
- Try a different port: `php -S localhost:8080`

**Styles not showing?**
- Make sure all files are in the correct folders
- Check browser console for 404 errors
- Verify CSS file path: `css/style.css`

**JavaScript not working?**
- Open browser console (F12) to check for errors
- Verify JS file path: `js/main.js`
- Make sure JavaScript is enabled in browser

