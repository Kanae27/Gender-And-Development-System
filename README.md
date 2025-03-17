# GAD Information System

## Overview

The Gender and Development (GAD) Information System is a comprehensive web-based platform designed to streamline and manage gender-related planning, budgeting, and reporting processes for educational institutions. This system facilitates the implementation of gender mainstreaming initiatives by providing tools for tracking targets, managing Gender and Development Budget Plans (GBP), and monitoring Programs, Projects, and Activities (PPAs).

## Features

### 🔹 User Authentication
- Secure login system
- Role-based access control
- Session management

### 🔹 Dashboard
- Interactive analytics dashboard
- Visual representation of key metrics
- Real-time data visualization with charts
- Comparison between targets and actual performance

### 🔹 Staff Management
- Personnel information management
- Academic rank tracking
- Staff directory and search functionality

### 🔹 Forms Management
#### Target Forms
- Create and manage OPCR (Office Performance Commitment and Review) targets
- Track target progress over time
- Year-based target setting and monitoring

#### GBP (Gender and Development Budget Plan) Forms
- Create and manage gender budget plans
- Track budget allocation and utilization
- Generate reports on gender-responsive budgeting

#### PPAs (Programs, Projects, and Activities) Forms
- Main PPAs form for activity tracking
- Proposal submission and management
- Narrative report generation
- Implementation tracking

### 🔹 Reporting
- Generate comprehensive reports
- Export data in various formats
- Quarterly and annual reporting capabilities
- Performance analysis against targets

### 🔹 Responsive Design
- Mobile-friendly interface
- Dark/light theme toggle
- Accessible on various devices and screen sizes

## Technical Details

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: 
  - Chart.js for data visualization
  - Font Awesome for icons
  - SweetAlert2 for enhanced alerts
  - jQuery for DOM manipulation

### System Architecture
The system follows a modular architecture with separate components for:
- Authentication and user management
- Form processing and validation
- Data visualization and reporting
- API endpoints for data retrieval

### Database Structure
The system uses a relational database with tables for:
- User credentials and permissions
- Personnel information
- Academic ranks
- Target metrics
- GBP data
- PPAs information
- Implementation dates and tracking
- Beneficiary information
- Sustainable Development Goals (SDGs) tracking

## Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Setup Instructions
1. Clone this repository to your web server's document root
2. Create a MySQL database named `gad_db`
3. Import the database schema from the SQL files in the `database` directory
4. Configure the database connection in `config.php`
5. Access the system through your web browser
6. Log in with the default credentials (admin/admin)
7. Change the default password immediately after first login

### Configuration
The system can be configured through the `config.php` file:
- Database connection settings
- Error reporting options
- System-wide parameters

## Usage

### Getting Started
1. Log in to the system using your credentials
2. Navigate through the sidebar menu to access different modules
3. Use the dashboard to get an overview of current performance
4. Create and manage forms through their respective modules

### Common Workflows
- **Creating a Target**: Navigate to Forms > Target Form, fill in the required information, and submit
- **Managing GBP**: Access the GBP Form module to create and track gender budget plans
- **Tracking PPAs**: Use the PPAs Form module to manage programs, projects, and activities
- **Generating Reports**: Navigate to the Reports section to generate and export various reports

## Troubleshooting

### Common Issues
- **Database Connection Errors**: Verify your database credentials in `config.php`
- **Missing Tables**: Run the setup scripts in the respective module directories
- **Form Submission Errors**: Check for validation errors and ensure all required fields are filled
- **Display Issues**: Clear browser cache or try a different browser

### Support
For additional support or to report issues, please contact the system administrator or create an issue in this repository.

## Contributing
Contributions to improve the GAD Information System are welcome. Please follow these steps:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License
This project is licensed under the [MIT License](LICENSE).

## Acknowledgments
- Batangas State University for supporting the development of this system
- The Gender and Development Office for providing domain expertise
- All contributors who have helped improve this system 
