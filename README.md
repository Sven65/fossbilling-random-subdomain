# Random Subdomain Generator Module for FOSSBilling

This module automatically generates random subdomains for hosting orders in FOSSBilling.

## Features

- Automatically generates random subdomains when customers order hosting
- Checks for subdomain availability to ensure uniqueness
- Configurable default domain and subdomain length
- Admin panel for configuration
- Client API for generating subdomains on-demand
- Test generator in admin panel

## Installation

1. Download/clone this module
2. Upload the entire `Randomsubdomain` folder to your FOSSBilling installation at:
   ```
   /modules/Randomsubdomain/
   ```

3. Log in to your FOSSBilling admin panel
4. Navigate to Extensions → Overview
5. Find "Random Subdomain Generator" and click "Activate"

## File Structure

```
/modules/Randomsubdomain/
├── Api/
│   ├── Admin.php                              # Admin API endpoints
│   └── Client.php                             # Client API endpoints
├── Controller/
│   └── Admin.php                              # Admin controller
├── html_admin/
│   └── mod_Randomsubdomain_settings.html.twig # Settings page template
├── Service.php                                # Main service logic
├── manifest.json                              # Module metadata
└── README.md                                  # This file
```

## Configuration

After installation, go to:
**Extensions → Random Subdomain Generator → Settings**

Configure:
- **Default Domain**: The main domain to use for subdomains (e.g., `yourhosting.com`)
- **Subdomain Length**: Number of characters for generated subdomains (4-20)
- **Enable/Disable**: Toggle automatic subdomain generation

## Usage

### Automatic Generation
When enabled, the module automatically generates a random subdomain when:
- A customer orders a hosting product
- No subdomain is specified in the order

### Manual Generation (Admin)
Administrators can:
- Test the generator in the settings page
- Use the API: `admin.randomsubdomain_generate()`

### Manual Generation (Client)
Clients can request a random subdomain via API:
```javascript
client.randomsubdomain_generate()
```

## API Endpoints

### Admin API
- `admin.randomsubdomain_generate()` - Generate a random subdomain
- `admin.randomsubdomain_check_availability()` - Check if subdomain is available
- `admin.randomsubdomain_update_config()` - Update module configuration
- `admin.randomsubdomain_get_config()` - Get current configuration

### Client API
- `client.randomsubdomain_generate()` - Generate a random subdomain
- `client.randomsubdomain_check_availability()` - Check subdomain availability

## How It Works

1. The module hooks into `onBeforeClientOrderCreate` event
2. When a hosting order is created, it checks if a subdomain is provided
3. If no subdomain exists, it generates a random 8-character string
4. It verifies the subdomain is unique in the database
5. The subdomain is automatically added to the order

## Requirements

- FOSSBilling 0.1.0 or higher
- PHP 7.4 or higher

## Support

For issues or questions, please visit the project repository.

## License

Apache-2.0

## Author

Your Name - https://yourwebsite.com