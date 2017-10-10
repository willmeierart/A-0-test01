<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Modified by Joomunited
 */

// Exit if accessed directly

if (!defined('ABSPATH'))
    exit();

if (!class_exists('WPMS_GAPI_Controller')) {
    require_once ( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-admin.php' );
    class WPMS_GAPI_Controller extends MetaSeo_Admin {

        public $service;
        public $timeshift;
        private $managequota;
        private $wpmsga;

        public function __construct() {
            $google_alanytics = get_option('wpms_google_alanytics');
            $this->wpmsga = WPMSGA();
            include_once ( WPMETASEO_PLUGIN_DIR . 'inc/autoload.php' );
            $config = new Google_Config();
            $config->setCacheClass('Google_Cache_Null');
            if (function_exists('curl_version')) {
                $curlversion = curl_version();
                if (isset($curlversion['version']) && ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) && version_compare($curlversion['version'], '7.10.8') >= 0 && defined('GADWP_IP_VERSION') && GADWP_IP_VERSION) {
                    $config->setClassConfig('Google_IO_Curl', array('options' => array(CURLOPT_IPRESOLVE => GADWP_IP_VERSION))); // Force CURL_IPRESOLVE_V4 or CURL_IPRESOLVE_V6
                }
            }
            
            $this->client = new Google_Client($config);
            $this->client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
            $this->client->setAccessType('offline');
            $this->client->setApplicationName('WP Meta SEO');
            $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $this->set_error_timeout();
            $this->managequota = 'u' . get_current_user_id() . 's' . get_current_blog_id();
            $this->client = WPMSGA_Tools::setClient($this->client , $google_alanytics , $this->access);
            $this->service = new Google_Service_Analytics($this->client);
            if (!empty($google_alanytics['googleCredentials'])) {
                $token = $google_alanytics['googleCredentials'];
                if ($token) {
                    try {
                        $this->client->setAccessToken($token);
                    } catch (Google_IO_Exception $e) {
                        WPMSGA_Tools::set_cache('wpmsga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                    } catch (Google_Service_Exception $e) {
                        WPMSGA_Tools::set_cache('wpmsga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                        WPMSGA_Tools::set_cache('wpmsga_dash_gapi_errors', array($e->getCode(), (array) $e->getErrors()), $this->error_timeout);
                        $this->reset_token();
                    } catch (Exception $e) {
                        WPMSGA_Tools::set_cache('wpmsga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                        $this->reset_token();
                    }
                }
            }
        }

        /**
         * Handles errors returned by GAPI Library
         *
         * @return boolean
         */
        public function gapi_errors_handler() {
            $errors = WPMSGA_Tools::get_cache('gapi_errors');
            if ($errors === false || !isset($errors[0])) { // invalid error
                return false;
            }
            if (isset($errors[1][0]['reason']) && ( $errors[1][0]['reason'] == 'invalidCredentials' || $errors[1][0]['reason'] == 'authError' || $errors[1][0]['reason'] == 'insufficientPermissions' || $errors[1][0]['reason'] == 'required' || $errors[1][0]['reason'] == 'keyExpired' )) {
                $this->reset_token(false);
                return true;
            }
            if (isset($errors[1][0]['reason']) && ( $errors[1][0]['reason'] == 'userRateLimitExceeded' || $errors[1][0]['reason'] == 'quotaExceeded' )) {
                if ($this->wpmsga->config->options['api_backoff'] <= 5) {
                    usleep(rand(100000, 1500000));
                    return false;
                } else {
                    return true;
                }
            }
            if ($errors[0] == 400 || $errors[0] == 401 || $errors[0] == 403) {
                return true;
            }
            return false;
        }

        /**
         * Calculates proper timeouts for each GAPI query
         *
         * @param
         *            $daily
         * @return number
         */
        public function get_timeouts($daily) {
            $local_time = time() + $this->timeshift;
            if ($daily) {
                $nextday = explode('-', date('n-j-Y', strtotime(' +1 day', $local_time)));
                $midnight = mktime(0, 0, 0, $nextday[0], $nextday[1], $nextday[2]);
                return $midnight - $local_time;
            } else {
                $nexthour = explode('-', date('H-n-j-Y', strtotime(' +1 hour', $local_time)));
                $newhour = mktime($nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3]);
                return $newhour - $local_time;
            }
        }

        /**
         * Generates and retrieves the Access Code
         */
        public function token_request() {
            $authUrl = $this->client->createAuthUrl();
            ?>
            <form name="input" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
                <table class="wpmsga-settings-options">
                    <tr>
                        <td colspan="2" class="wpmsga-settings-info">
            <?php echo __("Use this link to get your access code:", 'wp-meta-seo') . ' <a href="' . $authUrl . '" id="gapi-access-code" target="_blank">' . __("Get Access Code", 'wp-meta-seo') . '</a>.'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="wpmsga-settings-title"><label for="ga_dash_code" title="<?php _e("Use the red link to get your access code!", 'wp-meta-seo') ?>"><?php echo _e("Access Code:", 'wp-meta-seo'); ?></label></td>
                        <td><input type="text" id="ga_dash_code" name="ga_dash_code" value="" size="61" required="required" title="<?php _e("Use the red link to get your access code!", 'wp-meta-seo') ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" class="button button-secondary" name="ga_dash_authorize" value="<?php _e("Save Access Code", 'wp-meta-seo'); ?>" /></td>
                    </tr>
                </table>
            </form>
            <?php
        }

        /**
         * Retrieves all Google Analytics Views with details
         *
         * @return array
         */
        public function refresh_profiles() {
            try {

                $ga_dash_profile_list = array();
                $startindex = 1;
                $totalresults = 65535; // use something big

                while ($startindex < $totalresults) {

                    $profiles = $this->service->management_profiles->listManagementProfiles('~all', '~all', array('start-index' => $startindex));

                    $items = $profiles->getItems();

                    $totalresults = $profiles->getTotalResults();

                    if ($totalresults > 0) {

                        foreach ($items as $profile) {
                            $timetz = new DateTimeZone($profile->getTimezone());
                            $localtime = new DateTime('now', $timetz);
                            $timeshift = strtotime($localtime->format('Y-m-d H:i:s')) - time();
                            $ga_dash_profile_list[] = array($profile->getName(), $profile->getId(), $profile->getwebPropertyId(), $profile->getwebsiteUrl(), $timeshift, $profile->getTimezone(), $profile->getDefaultPage());
                            $startindex++;
                        }
                    }
                }

                if (empty($ga_dash_profile_list)) {
                    WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': No properties were found in this account!', $this->error_timeout);
                } else {
                    WPMSGA_Tools::delete_cache('last_error');
                }
                return $ga_dash_profile_list;
            } catch (Google_IO_Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $ga_dash_profile_list;
            } catch (Google_Service_Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                WPMSGA_Tools::set_cache('gapi_errors', array($e->getCode(), (array) $e->getErrors()), $this->error_timeout);
            } catch (Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $ga_dash_profile_list;
            }
        }

        /**
         * Handles the token reset process
         *
         * @param
         *            $all
         */
        public function reset_token($all = true) {
            update_option('wpms_google_alanytics', array());
        }

        /**
         * Get and cache Core Reports
         *
         * @param
         *            $projecId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $metrics
         * @param
         *            $options
         * @param
         *            $serial
         * @return int|Google_Service_Analytics_GaData
         */
        private function handle_corereports($projectId, $from, $to, $metrics, $options, $serial) {
            try {
                if ($from == "today") {
                    $timeouts = 0;
                } else {
                    $timeouts = 1;
                }
                $transient = WPMSGA_Tools::get_cache($serial);
                if ($transient === false) {
                    if ($this->gapi_errors_handler()) {
                        return - 23;
                    }
                    $data = $this->service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, $options);
                    WPMSGA_Tools::set_cache($serial, $data, $this->get_timeouts($timeouts));
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                WPMSGA_Tools::set_cache('gapi_errors', array($e->getCode(), (array) $e->getErrors()), $this->error_timeout);
                return $e->getCode();
            } catch (Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $e->getCode();
            }
            if ($data->getRows() > 0) {
                return $data;
            } else {
                return - 21;
            }
        }

        /**
         * Generates serials for transients
         *
         * @param
         *            $serial
         * @return string
         */
        public function get_serial($serial) {
            return sprintf("%u", crc32($serial));
        }

        /**
         * Analytics data for Area Charts (Admin Dashboard Widget report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_areachart_data($projectId, $from, $to, $query, $filter = '') {
            switch ($query) {
                case 'users' :
                    $title = __("Users", 'wp-meta-seo');
                    break;
                case 'pageviews' :
                    $title = __("Page Views", 'wp-meta-seo');
                    break;
                case 'visitBounceRate' :
                    $title = __("Bounce Rate", 'wp-meta-seo');
                    break;
                case 'organicSearches' :
                    $title = __("Organic Searches", 'wp-meta-seo');
                    break;
                case 'uniquePageviews' :
                    $title = __("Unique Page Views", 'wp-meta-seo');
                    break;
                default :
                    $title = __("Sessions", 'wp-meta-seo');
            }
            $metrics = 'ga:' . $query;
            if ($from == "today" || $from == "yesterday") {
                $dimensions = 'ga:hour';
                $dayorhour = __("Hour", 'wp-meta-seo');
            } else if ($from == "365daysAgo" || $from == "1095daysAgo") {
                $dimensions = 'ga:yearMonth, ga:month';
                $dayorhour = __("Date", 'wp-meta-seo');
            } else {
                $dimensions = 'ga:date,ga:dayOfWeekName';
                $dayorhour = __("Date", 'wp-meta-seo');
            }
            $options = array('dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr2_' . $this->get_serial($projectId . $from . $metrics . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array($dayorhour, $title));
            if ($from == "today" || $from == "yesterday") {
                foreach ($data->getRows() as $row) {
                    $wpmsga_data[] = array((int) $row[0] . ':00', round($row[1], 2));
                }
            } else if ($from == "365daysAgo" || $from == "1095daysAgo") {
                foreach ($data->getRows() as $row) {
                    /*
                     * translators:
                     * Example: 'F, Y' will become 'November, 2015'
                     * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
                     */
                    $wpmsga_data[] = array(date_i18n(__('F, Y', 'wp-meta-seo'), strtotime($row[0] . '01')), round($row[2], 2));
                }
            } else {
                foreach ($data->getRows() as $row) {
                    /*
                     * translators:
                     * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
                     * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
                     */
                    $wpmsga_data[] = array(date_i18n(__('l, F j, Y', 'wp-meta-seo'), strtotime($row[0])), round($row[2], 2));
                }
            }

            return $wpmsga_data;
        }

        /**
         * Analytics data for Bottom Stats (bottom stats on main report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_bottomstats($projectId, $from, $to, $filter = '') {
            $options = array('dimensions' => null, 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
                $metrics = 'ga:uniquePageviews,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession';
            } else {
                $metrics = 'ga:sessions,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession';
            }
            $serial = 'qr3_' . $this->get_serial($projectId . $from . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                if ($data == - 21) {
                    return array_fill(0, 6, 0);
                } else {
                    return $data;
                }
            }
            $wpmsga_data = array();
            foreach ($data->getRows() as $row) {
                $wpmsga_data = array_map('floatval', $row);
            }

            // i18n support
            $wpmsga_data[0] = number_format_i18n($wpmsga_data[0]);
            $wpmsga_data[1] = number_format_i18n($wpmsga_data[1]);
            $wpmsga_data[2] = number_format_i18n($wpmsga_data[2]);
            $wpmsga_data[3] = number_format_i18n($wpmsga_data[3], 2);
            $wpmsga_data[4] = number_format_i18n($wpmsga_data[4]);
            $wpmsga_data[5] = number_format_i18n($wpmsga_data[5], 2);

            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (content pages)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_contentpages($projectId, $from, $to, $filter = '') {
            $metrics = 'ga:pageviews';
            $dimensions = 'ga:pageTitle';
            $options = array('dimensions' => $dimensions, 'sort' => '-ga:pageviews', 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr4_' . $this->get_serial($projectId . $from . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Pages", 'wp-meta-seo'), __("Views", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int) $row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (referrers)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_referrers($projectId, $from, $to, $filter = '') {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:source';
            $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:medium==referral;ga:pagePath==' . $filter;
            } else {
                $options['filters'] = 'ga:medium==referral';
            }
            $serial = 'qr5_' . $this->get_serial($projectId . $from . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Referrers", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int) $row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (searches)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_searches($projectId, $from, $to, $filter = '') {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:keyword';
            $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:keyword!=(not set);ga:pagePath==' . $filter;
            } else {
                $options['filters'] = 'ga:keyword!=(not set)';
            }
            $serial = 'qr6_' . $this->get_serial($projectId . $from . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }

            $wpmsga_data = array(array(__("Searches", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int) $row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (location reports)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_locations($projectId, $from, $to, $filter = '') {
            $metrics = 'ga:sessions';
            $options = "";
            $title = __("Countries", 'wp-meta-seo');
            $serial = 'qr7_' . $this->get_serial($projectId . $from . $filter);
            $dimensions = 'ga:country';
            $local_filter = '';
            $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
                if ($local_filter) {
                    $options['filters'] .= ';' . $local_filter;
                }
            } else {
                if ($local_filter) {
                    $options['filters'] = $local_filter;
                }
            }
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array($title, __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                if (isset($row[2])) {
                    $wpmsga_data[] = array(esc_html($row[0]) . ', ' . esc_html($row[1]), (int) $row[2]);
                } else {
                    $wpmsga_data[] = array(esc_html($row[0]), (int) $row[1]);
                }
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts (traffic channels, device categories)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_orgchart_data($projectId, $from, $to, $query, $filter = '') {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:' . $query;
            $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr8_' . $this->get_serial($projectId . $from . $query . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $block = ( $query == 'channelGrouping' ) ? __("Channels", 'wp-meta-seo') : __("Devices", 'wp-meta-seo');
            $wpmsga_data = array(array('<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totalsForAllResults']["ga:sessions"] . '</div>', ""));
            foreach ($data->getRows() as $row) {
                $shrink = explode(" ", $row[0]);
                $wpmsga_data[] = array('<div style="color:black; font-size:1.1em">' . esc_html($shrink[0]) . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $row[1] . '</div>', '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totalsForAllResults']["ga:sessions"] . '</div>');
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Pie Charts (traffic mediums, serach engines, social networks, browsers, screen rsolutions, etc.)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_piechart_data($projectId, $from, $to, $query, $filter = '') {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:' . $query;

            if ($query == 'source') {
                $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
                if ($filter) {
                    $options['filters'] = 'ga:medium==organic;ga:keyword!=(not set);ga:pagePath==' . $filter;
                } else {
                    $options['filters'] = 'ga:medium==organic;ga:keyword!=(not set)';
                }
            } else {
                $options = array('dimensions' => $dimensions, 'sort' => '-ga:sessions', 'quotaUser' => $this->managequota . 'p' . $projectId);
                if ($filter) {
                    $options['filters'] = 'ga:' . $query . '!=(not set);ga:pagePath==' . $filter;
                } else {
                    $options['filters'] = 'ga:' . $query . '!=(not set)';
                }
            }
            $serial = 'qr10_' . $this->get_serial($projectId . $from . $query . $filter);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Type", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            $i = 0;
            $included = 0;
            foreach ($data->getRows() as $row) {
                if ($i < 20) {
                    $wpmsga_data[] = array(str_replace("(none)", "direct", esc_html($row[0])), (int) $row[1]);
                    $included += $row[1];
                    $i++;
                } else {
                    break;
                }
            }
            $totals = $data->getTotalsForAllResults();
            $others = $totals['ga:sessions'] - $included;
            if ($others > 0) {
                $wpmsga_data[] = array(__('Other', 'wp-meta-seo'), $others);
            }

            return $wpmsga_data;
        }

        /**
         * Analytics data for Frontend Widget (chart data and totals)
         *
         * @param
         *            $projectId
         * @param
         *            $period
         * @param
         *            $anonim
         * @return array|int
         */
        public function frontend_widget_stats($projectId, $from, $anonim) {
            $content = '';
            $to = 'yesterday';
            $metrics = 'ga:sessions';
            $dimensions = 'ga:date,ga:dayOfWeekName';
            $options = array('dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId);
            $serial = 'qr2_' . $this->get_serial($projectId . $from . $metrics);
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Date", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            if ($anonim) {
                $max_array = array();
                foreach ($data->getRows() as $item) {
                    $max_array[] = $item[2];
                }
                $max = max($max_array) ? max($max_array) : 1;
            }
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(date_i18n(__('l, F j, Y', 'wp-meta-seo'), strtotime($row[0])), ( $anonim ? round($row[2] * 100 / $max, 2) : (int) $row[2] ));
            }
            $totals = $data->getTotalsForAllResults();
            return array($wpmsga_data, $anonim ? 0 : number_format_i18n($totals['ga:sessions']));
        }

        /**
         * Analytics data for Realtime component (the real-time report)
         *
         * @param
         *            $projectId
         * @return array|int
         */
        private function get_realtime($projectId) {
            $metrics = 'rt:activeUsers';
            $dimensions = 'rt:pagePath,rt:source,rt:keyword,rt:trafficType,rt:visitorType,rt:pageTitle';
            try {
                $serial = 'qr_realtimecache_' . $this->get_serial($projectId);
                $transient = WPMSGA_Tools::get_cache($serial);
                if ($transient === false) {
                    if ($this->gapi_errors_handler()) {
                        return - 23;
                    }
                    $data = $this->service->data_realtime->get('ga:' . $projectId, $metrics, array('dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId));
                    WPMSGA_Tools::set_cache($serial, $data, 55);
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                WPMSGA_Tools::set_cache('gapi_errors', array($e->getCode(), (array) $e->getErrors()), $this->error_timeout);
                return $e->getCode();
            } catch (Exception $e) {
                WPMSGA_Tools::set_cache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $e->getCode();
            }
            if ($data->getRows() < 1) {
                return - 21;
            }
            $i = 0;
            $wpmsga_data = $data;
            foreach ($data->getRows() as $row) {
                $wpmsga_data->rows[$i] = array_map('esc_html', $row);
                $i++;
            }
            return array($wpmsga_data);
        }

        private function map($map) {
            $map = explode('.', $map);
            if (isset($map[1])) {
                $map[0] += ord('map');
                return implode('.', $map);
            } else {
                return str_ireplace('map', chr(112), $map[0]);
            }
        }

        /**
         * Handles ajax requests and calls the needed methods
         * @param
         * 		$projectId
         * @param
         * 		$query
         * @param
         * 		$from
         * @param
         * 		$to
         * @param
         * 		$filter
         * @return number|Google_Service_Analytics_GaData
         */
        public function get($projectId, $query, $from = false, $to = false, $filter = '') {
            if (empty($projectId) || !is_numeric($projectId)) {
                wp_die(- 26);
            }
            if (in_array($query, array('sessions', 'users', 'organicSearches', 'visitBounceRate', 'pageviews', 'uniquePageviews'))) {
                return $this->get_areachart_data($projectId, $from, $to, $query, $filter);
            }
            if ($query == 'bottomstats') {
                return $this->get_bottomstats($projectId, $from, $to, $filter);
            }
            if ($query == 'locations') {
                return $this->get_locations($projectId, $from, $to, $filter);
            }
            if ($query == 'referrers') {
                return $this->get_referrers($projectId, $from, $to, $filter);
            }
            if ($query == 'contentpages') {
                return $this->get_contentpages($projectId, $from, $to, $filter);
            }
            if ($query == 'searches') {
                return $this->get_searches($projectId, $from, $to, $filter);
            }
            if ($query == 'realtime') {
                return $this->get_realtime($projectId);
            }
            if ($query == 'channelGrouping' || $query == 'deviceCategory') {
                return $this->get_orgchart_data($projectId, $from, $to, $query, $filter);
            }
            if (in_array($query, array('medium', 'visitorType', 'socialNetwork', 'source', 'browser', 'operatingSystem', 'screenResolution', 'mobileDeviceBranding'))) {
                return $this->get_piechart_data($projectId, $from, $to, $query, $filter);
            }
            wp_die(- 27);
        }

    }

}
