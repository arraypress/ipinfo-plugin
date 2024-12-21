# IPInfo Demo Plugin

This is a demonstration WordPress plugin showing how to implement the [IPInfo Library](https://github.com/arraypress/ipinfo) in a WordPress environment. The plugin provides a testing interface in the WordPress admin area to explore IPInfo.io API capabilities.

## About

This plugin demonstrates the integration of the [IPInfo Library](https://github.com/arraypress/ipinfo) with WordPress, showing developers how to implement IP intelligence features including geolocation, ASN data, privacy detection, and more in their own plugins.

## Installation

1. Download or fork this repository
2. Place it in your WordPress plugins directory
3. Run `composer install` in the plugin directory
4. Activate the plugin in WordPress
5. Navigate to Tools > IPInfo Tester to configure your API token

## Features Demonstrated

The admin interface allows testing of:
- Single IP lookups
- Batch IP processing
- Individual field queries
- Full response data display including:
    - Geolocation data
    - ASN information
    - Privacy detection (VPN/Proxy/Tor)
    - Company details
    - Abuse contacts
    - Associated domains

## Requirements

- PHP 7.4 or later
- WordPress 6.7.1 or later
- IPInfo.io API token

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/ipinfo-plugin)
- [Issue Tracker](https://github.com/arraypress/ipinfo-plugin/issues)