<?php
/**
 * Plugin Name:         ArrayPress - IPInfo Tester
 * Plugin URI:          https://arraypress.com/plugins/ipinfo-tester
 * Description:         A plugin to test and demonstrate the IPInfo.io API integration.
 * Author:              ArrayPress
 * Author URI:          https://arraypress.com
 * License:             GNU General Public License v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         arraypress-ipinfo
 * Domain Path:         /languages/
 * Requires PHP:        7.4
 * Requires at least:   6.7.1
 * Version:             1.0.0
 */

namespace ArrayPress\IPInfo;

defined( 'ABSPATH' ) || exit;

/**
 * Include required files and initialize the Plugin class if available.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Plugin class to handle all the functionality
 */
class Plugin {

	/**
	 * Instance of IPInfoClient
	 *
	 * @var Client|null
	 */
	private ?Client $client = null;

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		// Initialize client if token is set
		$token = get_option( 'ipinfo_api_token' );
		if ( $token ) {
			$this->client = new Client( $token );
		}

		// Hook into WordPress
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add admin menu pages
	 */
	public function add_admin_menu() {
		add_management_page(
			'IPInfo Tester',
			'IPInfo Tester',
			'manage_options',
			'ipinfo-tester',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting( 'ipinfo_settings', 'ipinfo_api_token' );

		add_settings_section(
			'ipinfo_settings_section',
			'API Settings',
			null,
			'ipinfo-tester'
		);

		add_settings_field(
			'ipinfo_api_token',
			'IPInfo API Token',
			[ $this, 'render_token_field' ],
			'ipinfo-tester',
			'ipinfo_settings_section'
		);
	}

	/**
	 * Render API token field
	 */
	public function render_token_field() {
		$token = get_option( 'ipinfo_api_token' );
		echo '<input type="text" name="ipinfo_api_token" value="' . esc_attr( $token ) . '" class="regular-text">';
	}

	/**
	 * Render admin page
	 *
	 * @return void
	 */
	public function render_admin_page() {
		// Get test parameters
		$test_ip    = isset( $_POST['test_ip'] ) ? sanitize_text_field( $_POST['test_ip'] ) : '';
		$test_type  = isset( $_POST['test_type'] ) ? sanitize_text_field( $_POST['test_type'] ) : 'single';
		$test_field = isset( $_POST['test_field'] ) ? sanitize_text_field( $_POST['test_field'] ) : '';
		$batch_ips  = isset( $_POST['batch_ips'] ) ? sanitize_textarea_field( $_POST['batch_ips'] ) : '';

		$results = null;

		// Process form submission
		if ( $this->client && isset( $_POST['submit'] ) ) {
			switch ( $test_type ) {
				case 'single':
					if ( $test_ip ) {
						if ( $test_field ) {
							$results = $this->client->get_field( $test_ip, $test_field );
						} else {
							$results = $this->client->get_ip_info( $test_ip );
						}
					}
					break;

				case 'batch':
					if ( $batch_ips ) {
						$ips     = array_map( 'trim', explode( "\n", $batch_ips ) );
						$results = $this->client->get_batch_info( $ips );
					}
					break;
			}
		}

		// Start rendering the page
		?>
        <div class="wrap">
            <h1>IPInfo Tester</h1>

            <!-- Settings Form -->
			<?php $this->render_settings_form(); ?>

            <hr>

            <!-- Test Interface -->
			<?php $this->render_test_interface( $test_type, $test_ip, $test_field, $batch_ips ); ?>

            <!-- Results Section -->
			<?php $this->render_results( $results, $test_type, $test_field ); ?>
        </div>

		<?php $this->render_js(); ?>
		<?php
	}

	/**
	 * Render the settings form
	 */
	private function render_settings_form() {
		?>
        <form method="post" action="options.php">
			<?php
			settings_fields( 'ipinfo_settings' );
			do_settings_sections( 'ipinfo-tester' );
			submit_button( 'Save API Token' );
			?>
        </form>
		<?php
	}

	/**
	 * Render the test interface
	 */
	private function render_test_interface( $test_type, $test_ip, $test_field, $batch_ips ) {
		?>
        <h2>Test Options</h2>
        <form method="post">
            <table class="form-table">
                <!-- Test Type Selection -->
                <tr>
                    <th scope="row">Test Type</th>
                    <td>
                        <label>
                            <input type="radio" name="test_type" value="single"
								<?php checked( $test_type, 'single' ); ?>>
                            Single IP
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="test_type" value="batch"
								<?php checked( $test_type, 'batch' ); ?>>
                            Batch Processing
                        </label>
                    </td>
                </tr>

                <!-- Single IP Fields -->
                <tr class="single-ip-fields" style="<?php echo $test_type === 'batch' ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="test_ip">IP Address</label></th>
                    <td>
                        <input type="text" name="test_ip" id="test_ip"
                               value="<?php echo esc_attr( $test_ip ); ?>"
                               class="regular-text">
                        <p class="description">Enter a single IP address</p>
                    </td>
                </tr>

                <tr class="single-ip-fields" style="<?php echo $test_type === 'batch' ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="test_field">Specific Field</label></th>
                    <td>
                        <select name="test_field" id="test_field">
                            <option value="">Full Response</option>
                            <option value="country" <?php selected( $test_field, 'country' ); ?>>Country</option>
                            <option value="city" <?php selected( $test_field, 'city' ); ?>>City</option>
                            <option value="org" <?php selected( $test_field, 'org' ); ?>>Organization</option>
                            <option value="hostname" <?php selected( $test_field, 'hostname' ); ?>>Hostname</option>
                            <option value="loc" <?php selected( $test_field, 'loc' ); ?>>Location</option>
                        </select>
                        <p class="description">Optional: Get a specific field only</p>
                    </td>
                </tr>

                <!-- Batch Processing Fields -->
                <tr class="batch-ip-fields" style="<?php echo $test_type === 'single' ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="batch_ips">IP Addresses</label></th>
                    <td>
                        <textarea name="batch_ips" id="batch_ips" rows="5"
                                  class="large-text code"><?php echo esc_textarea( $batch_ips ); ?></textarea>
                        <p class="description">Enter multiple IP addresses, one per line (max 1000)</p>
                    </td>
                </tr>
            </table>

			<?php submit_button( 'Run Test', 'primary', 'submit', false ); ?>
        </form>
		<?php
	}

	/**
	 * Render the results section
	 */
	private function render_results( $results, $test_type, $test_field ) {
		if ( ! $results ) {
			return;
		}

		?>
        <h2>Results</h2>
		<?php

		if ( is_wp_error( $results ) ) {
			?>
            <div class="notice notice-error">
                <p><?php echo esc_html( $results->get_error_message() ); ?></p>
            </div>
			<?php
			return;
		}

		if ( $test_type === 'single' && $test_field ) {
			// Single field result
			?>
            <div class="card">
                <h3>Field: <?php echo esc_html( $test_field ); ?></h3>
                <p><?php echo esc_html( $results ); ?></p>
            </div>
			<?php
		} elseif ( $test_type === 'single' ) {
			// Single IP full result
			$this->render_single_result( $results );
		} else {
			// Batch results
			foreach ( $results as $ip => $info ) {
				?>
                <h3>Results for IP: <?php echo esc_html( $ip ); ?></h3>
				<?php
				$this->render_single_result( $info );
			}
		}

		// Debug information
		if ( ! is_string( $results ) ) {
			$this->render_debug_info( $results );
		}
	}

	/**
	 * Render a single result table
	 *
	 * @param Response $results Response object to render
	 *
	 * @return void
	 */
	private function render_single_result( $results ) {
		?>
        <table class="widefat striped">
            <tbody>
            <!-- Plan Information -->
            <tr>
                <th>IPInfo Plan</th>
                <td><?php echo esc_html( $results->get_plan() ); ?></td>
            </tr>

            <!-- Basic Information -->
            <tr>
                <th>IP Address</th>
                <td>
					<?php
					echo esc_html( $results->get_ip() );
					if ( $hostname = $results->get_hostname() ) {
						echo ' (' . esc_html( $hostname ) . ')';
					}
					if ( $results->is_anycast() ) {
						echo ' <span class="description">(Anycast IP)</span>';
					}
					?>
                </td>
            </tr>

            <!-- Country Information -->
            <tr>
                <th>Country</th>
                <td>
					<?php
					$country_parts = [];

					if ( $results->get_country_name() ) {
						$country_parts[] = esc_html( $results->get_country_name() );
						if ( $results->get_country() ) {
							$country_parts[] = '(' . esc_html( $results->get_country() ) . ')';
						}
					}

					if ( $flag = $results->get_country_flag() ) {
						$country_parts[] = $flag->get_emoji();
					}

					if ( $results->is_eu() ) {
						$country_parts[] = '🇪🇺 EU Member';
					}

					echo implode( ' ', $country_parts );

					if ( $currency = $results->get_country_currency() ) {
						echo '<br>Currency: ' . esc_html( $currency->get_symbol() ) . ' ' .
						     esc_html( $currency->get_code() );
					}
					?>
                </td>
            </tr>

            <!-- Continent Information -->
			<?php if ( $continent = $results->get_continent() ): ?>
                <tr>
                    <th>Continent</th>
                    <td>
						<?php
						echo esc_html( $continent->get_name() );
						if ( $code = $continent->get_code() ) {
							echo ' (' . esc_html( $code ) . ')';
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- Location Information -->
            <tr>
                <th>Location</th>
                <td>
					<?php
					$parts = [];
					if ( $results->get_city() ) {
						$parts[] = esc_html( $results->get_city() );
					}
					if ( $results->get_region() ) {
						$parts[] = esc_html( $results->get_region() );
					}
					if ( $results->get_postal() ) {
						$parts[] = 'Postal: ' . esc_html( $results->get_postal() );
					}
					echo implode( ', ', $parts );
					?>
                </td>
            </tr>

            <!-- Coordinates -->
			<?php if ( $coords = $results->get_coordinates() ): ?>
                <tr>
                    <th>Coordinates</th>
                    <td>
						<?php
						echo esc_html( "Lat: {$coords['latitude']}, Long: {$coords['longitude']}" );
						if ( $results->get_latitude() ) {
							echo '<br>Latitude: ' . esc_html( $results->get_latitude() );
						}
						if ( $results->get_longitude() ) {
							echo '<br>Longitude: ' . esc_html( $results->get_longitude() );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- Timezone -->
			<?php if ( $results->get_timezone() ): ?>
                <tr>
                    <th>Timezone</th>
                    <td><?php echo esc_html( $results->get_timezone() ); ?></td>
                </tr>
			<?php endif; ?>

            <!-- Organization Information -->
			<?php if ( $results->get_company() || $results->get_org() ): ?>
                <tr>
                    <th>Organization</th>
                    <td>
						<?php
						if ( $company = $results->get_company() ) {
							if ( $company->get_name() ) {
								echo 'Name: ' . esc_html( $company->get_name() ) . '<br>';
							}
							if ( $company->get_domain() ) {
								echo 'Domain: ' . esc_html( $company->get_domain() ) . '<br>';
							}
							if ( $company->get_type() ) {
								echo 'Type: ' . esc_html( $company->get_type() );
							}
						} elseif ( $org = $results->get_org() ) {
							echo esc_html( $org );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- ASN Information -->
			<?php if ( $asn = $results->get_asn() ): ?>
                <tr>
                    <th>ASN Information</th>
                    <td>
						<?php
						if ( $asn->get_asn() ) {
							echo 'ASN: ' . esc_html( $asn->get_asn() ) . '<br>';
						}
						if ( $asn->get_name() ) {
							echo 'Name: ' . esc_html( $asn->get_name() ) . '<br>';
						}
						if ( $asn->get_domain() ) {
							echo 'Domain: ' . esc_html( $asn->get_domain() ) . '<br>';
						}
						if ( $asn->get_route() ) {
							echo 'Route: ' . esc_html( $asn->get_route() ) . '<br>';
						}
						if ( $asn->get_type() ) {
							echo 'Type: ' . esc_html( $asn->get_type() );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- Privacy Information -->
			<?php if ( $privacy = $results->get_privacy() ): ?>
                <tr>
                    <th>Privacy Detection</th>
                    <td>
						<?php
						echo 'VPN: ' . ( $privacy->is_vpn() ? 'Yes' : 'No' ) . '<br>';
						echo 'Proxy: ' . ( $privacy->is_proxy() ? 'Yes' : 'No' ) . '<br>';
						echo 'Tor: ' . ( $privacy->is_tor() ? 'Yes' : 'No' ) . '<br>';
						echo 'Relay: ' . ( $privacy->is_relay() ? 'Yes' : 'No' ) . '<br>';
						echo 'Hosting: ' . ( $privacy->is_hosting() ? 'Yes' : 'No' );
						if ( $service = $privacy->get_service() ) {
							echo '<br>Service: ' . esc_html( $service );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- Abuse Information -->
			<?php if ( $abuse = $results->get_abuse() ): ?>
                <tr>
                    <th>Abuse Contact</th>
                    <td>
						<?php
						if ( $abuse->get_name() ) {
							echo 'Name: ' . esc_html( $abuse->get_name() ) . '<br>';
						}
						if ( $abuse->get_email() ) {
							echo 'Email: ' . esc_html( $abuse->get_email() ) . '<br>';
						}
						if ( $abuse->get_phone() ) {
							echo 'Phone: ' . esc_html( $abuse->get_phone() ) . '<br>';
						}
						if ( $abuse->get_network() ) {
							echo 'Network: ' . esc_html( $abuse->get_network() ) . '<br>';
						}
						if ( $abuse->get_address() ) {
							echo 'Address: ' . esc_html( $abuse->get_address() ) . '<br>';
						}
						if ( $abuse->get_country() ) {
							echo 'Country: ' . esc_html( $abuse->get_country() );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>

            <!-- Domains Information -->
			<?php if ( $domains = $results->get_domains() ): ?>
                <tr>
                    <th>Associated Domains</th>
                    <td>
						<?php
						echo 'Total Domains: ' . number_format( $domains->get_total() ) . '<br>';
						if ( $domain_list = $domains->get_domains() ) {
							echo 'Sample Domains:<br>';
							foreach ( $domain_list as $domain ) {
								echo '- ' . esc_html( $domain ) . '<br>';
							}
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Render JavaScript for the page
	 */
	private function render_js() {
		?>
        <script>
            jQuery(document).ready(function ($) {
                $('input[name="test_type"]').change(function () {
                    if ($(this).val() === 'single') {
                        $('.single-ip-fields').show();
                        $('.batch-ip-fields').hide();
                    } else {
                        $('.single-ip-fields').hide();
                        $('.batch-ip-fields').show();
                    }
                });
            });
        </script>
		<?php
	}

	/**
	 * Render debug information
	 */
	private function render_debug_info( $results ) {
		if ( $results instanceof Response || ( is_array( $results ) && current( $results ) instanceof Response ) ) {
			?>
            <div class="debug-info" style="background: #f5f5f5; padding: 15px; margin-top: 20px;">
                <h3>Raw Response Data:</h3>
                <pre style="background: #fff; padding: 10px; overflow: auto;">
                    <?php
                    if ( is_array( $results ) ) {
	                    foreach ( $results as $ip => $info ) {
		                    echo esc_html( $ip ) . ":\n";
		                    print_r( $info->get_all() );
		                    echo "\n";
	                    }
                    } else {
	                    print_r( $results->get_all() );
                    }
                    ?>
                </pre>

                <h3>Plan Features Available:</h3>
                <ul>
					<?php
					$result = is_array( $results ) ? current( $results ) : $results;
					?>
                    <li>ASN Data: <?php echo $result->has_feature( 'asn' ) ? 'Yes' : 'No'; ?></li>
                    <li>Privacy Data: <?php echo $result->has_feature( 'privacy' ) ? 'Yes' : 'No'; ?></li>
                    <li>Company Data: <?php echo $result->has_feature( 'company' ) ? 'Yes' : 'No'; ?></li>
                    <li>Abuse Data: <?php echo $result->has_feature( 'abuse' ) ? 'Yes' : 'No'; ?></li>
                    <li>Domains Data: <?php echo $result->has_feature( 'domains' ) ? 'Yes' : 'No'; ?></li>
                </ul>
            </div>
			<?php
		}
	}

}

new Plugin();