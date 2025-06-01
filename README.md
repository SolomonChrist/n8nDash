# n8nDash 🚀

> Your Command Center for n8n Automations

n8nDash is an open-source dashboard platform designed to help individuals, teams, and businesses harness the full power of n8n automations, webhooks, and real-time data — all in one place. Whether you're a solopreneur, social media creator, or C-level executive, n8nDash serves as your command center for automation.

## 🎯 Vision

Our goal is to help people live the greatest lives ever lived — using AI + Automation. n8nDash makes this possible by providing an intuitive, touchscreen-ready interface for managing and triggering your n8n automations.

## ✨ Features

- 🔥 **One-Tap Automation**
  - Trigger n8n workflows instantly via webhook buttons
  - Custom button layouts and configurations
  - Mobile and touchscreen optimized

- 📊 **Live Data Integration**
  - Display real-time data from CRMs
  - Social media account metrics
  - Calendar integrations
  - Custom data sources via n8n webhooks

- 🔄 **Dashboard Sharing**
  - Export dashboards as JSON
  - Import shared dashboard configurations
  - Community sharing support

- 🎨 **Customizable Interface**
  - Grid-based widget layout (160x90)
  - Multiple dashboard support
  - Responsive design for all devices

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML + Bootstrap
- **Server**: Apache/Nginx
- **Integration**: n8n Webhook API

## 🚀 Quick Start

1. Clone this repository:
   ```bash
   git clone https://github.com/SolomonChrist/n8nDash.git
   ```

2. Navigate to the installation page:
   ```
   http://your-server/n8nDash/install/install.php
   ```

3. Follow the installation wizard to set up your database.

4. Log in with default credentials:
   - Username: `admin`
   - Password: `password`

5. **Important**: Change your password immediately after first login!

## 📖 Manual Installation

For advanced users who prefer manual setup:

1. Clone the repository
2. Create a MySQL database
3. Import the schema from `install/init.sql`
4. Configure `config/database.php` with your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database_name');
   ```

## 🎨 Creating Your First Dashboard

1. Log in to your n8nDash installation
2. Click "New Dashboard" in the sidebar
3. Name your dashboard
4. Add widgets:
   - Webhook buttons for automation triggers
   - Data display panels
   - Text inputs for dynamic data
   - Status labels

## 🔗 n8n Integration

1. In n8n:
   - Create a new workflow
   - Add a webhook node as trigger
   - Copy the webhook URL

2. In n8nDash:
   - Create a new button widget
   - Paste the webhook URL
   - Configure any parameters
   - Save and test!

## 🤝 Contributing

We believe in the power of community! Whether you're fixing bugs, adding features, or improving documentation, your contributions are welcome.

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to your branch
5. Open a Pull Request

## 📫 Support

Please join my Skool page and Learn More about AI + Automation: [Learn Automation](https://www.skool.com/learn-automation/about)

n8n Community Link: https://community.n8n.io/t/n8ndash-an-open-source-dashboard-for-n8n/

Add Me To LinkedIn: https://www.linkedin.com/in/solomonchrist0/

Follow Me On YouTube: https://www.youtube.com/@SolomonChristAI

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Setup Instructions

1. **Database Configuration**
   - Copy `config/config.example.php` to `config/config.php`
   - Update the database credentials in `config/config.php`:
     ```php
     define('DB_HOST', 'your_database_host');
     define('DB_USER', 'your_database_username');
     define('DB_PASS', 'your_database_password');
     define('DB_NAME', 'your_database_name');
     ```

2. **Directory Permissions**
   - Ensure these directories are writable:
     ```bash
     chmod 755 uploads/
     chmod 755 uploads/logos/
     ```

3. **Database Setup**
   - Import the database schema:
     ```bash
     mysql -u your_username -p your_database_name < sql/schema.sql
     ```
   - Create your first admin user:
     ```sql
     INSERT INTO users (username, email, password, is_admin) 
     VALUES ('admin', 'admin@example.com', PASSWORD('your_password'), 1);
     ```

4. **Web Server Configuration**
   - Point your web server to the project root
   - Ensure PHP has write permissions to the uploads directory
   - Configure your web server to handle PHP files

5. **Testing**
   - Visit the application URL
   - Log in with your admin credentials
   - Try creating a dashboard and widgets

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in config.php
   - Ensure database server is running
   - Check database user permissions

2. **Upload Issues**
   - Check directory permissions
   - Verify PHP file upload settings
   - Check file size limits

3. **Session Issues**
   - Verify PHP session configuration
   - Check session directory permissions
   - Clear browser cookies

### Debug Mode

To enable debug mode, set in config.php:
```php
define('DEBUG_MODE', true);
```

## Support

For issues or questions, please:
1. Check the troubleshooting guide
2. Open an issue on GitHub
3. Contact your administrator 