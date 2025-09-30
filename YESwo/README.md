# Ice Machine Maintenance System

A mobile-friendly web application for tracking maintenance activities across multiple ice machines on your property.

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin or your MySQL client
2. Make sure the `YESwo` database exists
3. Run the SQL script in `ice_machine_setup.sql` to create the required tables and sample data

### 2. Access the System
- Main Dashboard: `http://localhost/YESwo/ice_dashboard.php`
- Login using your existing YESwo credentials

## Features

### 📱 Mobile-First Design
- Responsive design that works perfectly on phones, tablets, and desktops
- Touch-friendly interface with large buttons and easy navigation
- Optimized for one-handed mobile use

### 🧊 Ice Machine Management
- **Dashboard**: Overview of all machines with status indicators
- **Add Machines**: Register new ice machines in the system
- **Edit Machines**: Update machine details and status
- **Status Tracking**: Visual indicators for operational status

### 🔧 Maintenance Tracking
- **Log Maintenance**: Record all maintenance activities
- **Maintenance Types**: Routine, Cleaning, Inspection, Repair, Emergency
- **Cost Tracking**: Track parts and labor costs
- **Next Service Scheduling**: Set and track upcoming maintenance dates

### 📊 Reporting & Analytics
- **Maintenance History**: Complete service history for each machine
- **Statistics**: Total services, costs, and maintenance type breakdowns
- **Status Overview**: Quick dashboard showing machines needing attention

### 🚨 Alert System
- **Visual Indicators**: Color-coded status badges
- **Overdue Alerts**: Highlights machines past due for maintenance
- **Due Soon Warnings**: Shows upcoming maintenance requirements

## File Structure

```
YESwo/
├── ice_machine_setup.sql      # Database setup script
├── ice_dashboard.php          # Main dashboard
├── add_machine.php           # Add new machine form
├── edit_machine.php          # Edit machine details
├── log_maintenance.php       # Log maintenance activities
├── machine_history.php       # View maintenance history
├── ice_machine_style.css     # Mobile-responsive styling
└── config.php               # Database configuration (existing)
```

## Mobile Features

### Responsive Breakpoints
- **Mobile** (≤480px): Full-width layout, large touch targets
- **Tablet** (481px-768px): Two-column grid, balanced spacing
- **Desktop** (769px+): Multi-column layout, enhanced features

### Touch Optimizations
- Minimum 44px touch targets (Apple/Google guidelines)
- Swipe-friendly card layouts
- Easy thumb navigation on mobile devices
- Prevents iOS zoom on form inputs

### Performance
- Lightweight CSS and minimal JavaScript
- Fast loading on slow mobile connections
- Efficient database queries with proper indexing

## Usage Examples

### Adding a New Machine
1. Go to Dashboard → "Add New Machine"
2. Fill in machine name and location (required)
3. Optionally add model, serial number, installation date
4. Save to add to tracking system

### Logging Maintenance
1. Go to Dashboard → "Log Maintenance" 
2. Select machine from dropdown
3. Choose maintenance type and date
4. Enter description of work performed
5. Add parts used and costs if applicable
6. Set next service date

### Viewing History
1. Click "History" on any machine card
2. View complete maintenance timeline
3. See statistics and cost tracking
4. Quick access to log new maintenance

## Database Schema

### ice_machines table
- Basic machine information (name, location, model, etc.)
- Current status and maintenance dates
- Installation details and notes

### maintenance_records table
- Complete maintenance history
- Links to specific machines
- Tracks costs, parts, and scheduling
- Supports different maintenance types

## Security Features
- User authentication required (uses existing YESwo login)
- SQL injection protection with prepared statements
- Input validation and sanitization
- Session management

## Browser Support
- iOS Safari (mobile optimized)
- Android Chrome (mobile optimized)
- Desktop Chrome, Firefox, Safari, Edge
- Progressive enhancement for older browsers