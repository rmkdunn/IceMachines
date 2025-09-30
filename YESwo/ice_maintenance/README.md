# Ice Machine Maintenance System

A complete mobile-friendly web application for tracking maintenance activities across multiple ice machines on your property.

## 🗂️ Organized File Structure

```
ice_maintenance/
├── index.php                 # Redirects to main dashboard
├── ice_dashboard.php         # Main dashboard with machine overview
├── add_machine.php           # Add new ice machines
├── edit_machine.php          # Edit existing machine details
├── log_maintenance.php       # Log maintenance activities
├── machine_history.php       # View maintenance history per machine
├── ice_machine_style.css     # Mobile-responsive styling
├── ice_machine_setup.sql     # Database setup script
└── README.md                 # This file
```

## 🚀 Quick Start

### 1. Database Setup
Run the SQL script in phpMyAdmin:
```sql
-- Copy and paste contents of ice_machine_setup.sql
```

### 2. Access the System
- **Main URL**: `http://localhost/YESwo/ice_maintenance/`
- **Direct Dashboard**: `http://localhost/YESwo/ice_maintenance/ice_dashboard.php`

### 3. Login Required
Use your existing YESwo login credentials to access the system.

## 📱 Features

### ✅ **Complete Ice Machine Management**
- **Dashboard Overview**: Status cards, machine grid, quick statistics
- **Add/Edit Machines**: Full machine profile management
- **Status Tracking**: Operational, Needs Maintenance, Out of Order, Scheduled

### 🔧 **Comprehensive Maintenance Logging**
- **Multiple Types**: Routine, Cleaning, Inspection, Repair, Emergency
- **Cost Tracking**: Parts, labor, and total maintenance costs
- **Scheduling**: Set and track next maintenance dates
- **History**: Complete timeline for each machine

### 📊 **Analytics & Reporting**
- **Statistics Dashboard**: Service counts, costs, status overview
- **Maintenance History**: Detailed records with filtering
- **Visual Indicators**: Color-coded status and alerts
- **Cost Analysis**: Track maintenance expenses over time

### 📱 **Mobile-First Design**
- **Responsive Layout**: Works on phones, tablets, and desktops
- **Touch Optimized**: Large buttons, easy navigation
- **Fast Loading**: Optimized for mobile connections
- **Offline-Ready**: Essential data cached for reliability

## 🎯 **Navigation Flow**

```
Dashboard → View all machines with status
    ├── Add Machine → Register new equipment
    ├── Edit Machine → Update details/status
    ├── Log Maintenance → Record service work
    └── Machine History → View detailed records
            ├── Log New Service → Quick maintenance entry
            └── Edit Machine → Update machine info
```

## 🛠️ **Usage Examples**

### Adding Your First Machine
1. Go to Dashboard → "Add New Machine"
2. Enter: Name, Location (required)
3. Optional: Model, Serial, Install Date, Notes
4. Save → Machine appears on dashboard

### Logging Maintenance
1. Dashboard → "Log Maintenance" or click machine "Log Service"
2. Select machine and maintenance type
3. Enter work description and costs
4. Set next service date
5. Save → Updates machine status and history

### Viewing History
1. Click "History" on any machine card
2. See complete service timeline
3. View statistics and cost totals
4. Quick access to log new service

## 🔒 **Security Features**
- **Authentication**: Integrated with YESwo login system
- **SQL Protection**: Prepared statements prevent injection
- **Input Validation**: Server-side validation on all forms
- **Session Management**: Secure user sessions

## 📋 **Database Schema**

### ice_machines
- Machine details, status, and maintenance dates
- Links to maintenance records
- Status tracking and notes

### maintenance_records
- Complete service history
- Cost tracking and parts used
- Technician details and scheduling
- Foreign key links to machines

## 🔄 **File Dependencies**

All files are self-contained within this folder except:
- `../config.php` - Database configuration (shared)
- `../login.php` - Authentication system (shared)
- `../logout.php` - Session management (shared)

## 📞 **Support**

For database issues, ensure:
1. YESwo database exists
2. Tables created via ice_machine_setup.sql
3. Proper MySQL permissions configured
4. WAMP server running correctly