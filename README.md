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

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.