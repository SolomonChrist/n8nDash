# n8nDash - Simple Dashboard for n8n Integration

A lightweight, PHP-based dashboard application that allows users to create custom dashboards with widgets that can interact with n8n workflows through webhooks.

## Features

- User authentication system
- Multiple dashboard support
- Customizable grid-based widget layout (160x90 grid)
- Widget types:
  - Text input
  - Labels
  - Buttons
- n8n webhook integration
- Dashboard import/export functionality
- Responsive design using Bootstrap

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

### Quick Installation

1. Clone this repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/n8nDash.git
   ```

2. Navigate to the installation page in your web browser:
   ```
   http://your-server/n8nDash/install/install.php
   ```

3. Follow the installation wizard to set up your database connection.

4. After installation completes, you can log in with the default credentials:
   - Username: `admin`
   - Password: `password`

5. **Important**: Change the default password immediately after first login.

### Manual Installation

If you prefer to set up manually:

1. Clone the repository
2. Create a MySQL database
3. Import the database schema from `install/init.sql`
4. Copy `config/database.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database_name');
   ```

## Usage

### Creating a Dashboard

1. Click the "New Dashboard" button in the sidebar
2. Enter a name for your dashboard
3. Click "Create"

### Adding Widgets

1. Select a dashboard from the sidebar
2. Click "Add Widget" button
3. Configure the widget:
   - Set title
   - Choose size (columns x rows)
   - Select input types (text, label, button)
   - Enter n8n webhook URL if needed
4. Click "Create"

### Importing/Exporting Dashboards

- To export: Click "Download Dashboard" button
- To import:
  1. Click "Import Dashboard" button
  2. Paste or upload the dashboard JSON
  3. Click "Import"

## Security

- Change the default admin password on first login
- All passwords are hashed using PHP's password_hash() function
- SQL injection protection through prepared statements
- Session-based authentication

## Integration with n8n

1. Create a webhook node in your n8n workflow
2. Copy the webhook URL
3. Create a widget in n8nDash and paste the webhook URL
4. Configure the widget inputs as needed

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For questions about AI and Automation, visit our Skool website (https://www.skool.com/learn-automation/about).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 