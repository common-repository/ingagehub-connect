<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

class INgageHubConnectServices
{
    public function __construct() {
        add_action( 'wp_ajax_com_ingagehub_clist', array( $this, 'com_ingagehub_ajax_clist' ) );
        add_action( 'wp_ajax_com_ingagehub_ctoken', array( $this, 'com_ingagehub_ajax_ctoken' ) );
        add_action( 'wp_ajax_com_ingagehub_cr', array( $this, 'com_ingagehub_ajax_cr' ) );
        add_action( 'wp_ajax_nopriv_com_ingagehub_cr', array( $this, 'com_ingagehub_ajax_cr' ) );
    }

    public function com_ingagehub_ajax_clist() {
        $campaigns = null;

        $auth = new IngageHubConnectAuth();
        if ($auth->login()) {
            $response = wp_remote_get($auth->site_url() . '/campaigns.json?with_attachments', array(
                'redirection' => 0,
                'cookies' => array(
                    '_session_id' => $auth->session_id()
                )
            ));

            if (is_array($response) && array_key_exists('body', $response)) {
                $campaigns = json_decode($response['body']);
            }
        }

        if (is_null($campaigns)) {
            wp_send_json(array('status' => 'ERROR'));
        } else {
            wp_send_json($campaigns);
        }
    }

    public function com_ingagehub_ajax_ctoken() {
        $return_value = null;

        $auth = new IngageHubConnectAuth();
        if ($auth->login()) {
            $response = wp_remote_get($auth->site_url() . '/campaigns/' . $_GET['id'] . '/share.json?shared_via=w', array(
                'redirection' => 0,
                'cookies' => array(
                    '_session_id' => $auth->session_id()
                )
            ));

            if (is_array($response) && array_key_exists('body', $response)) {
                $return_value = json_decode($response['body']);
            }
        }

        if (is_null($return_value)) {
            wp_send_json(array());
        } else {
            wp_send_json($return_value);
        }
    }

    public function com_ingagehub_ajax_cr() {
        $req = $_POST;

        if (is_null($req['id'])) {
            $req = $_GET;
        }

        if (is_null($req['id'])) {
            $req = $_REQUEST;
        }

        $campaign_id = $req['id'];
        $csrf_token = $req['ingagehub_csrf_token_' . $campaign_id]['value'];
        $token = $req['ingagehub_token_' . $campaign_id]['value'];
        $session_id = $req['ingagehub_session_id_' . $campaign_id]['value'];
        $site_url = $req['ingagehub_site_url_' . $campaign_id]['value'];

        if (isset($req['ingagehub_next_page_' . $campaign_id])) {
            $next_page = $req['ingagehub_next_page_' . $campaign_id]['value'];
        } else {
            $next_page = '';
        }

        $submit_url = $site_url . '/cr/' . $token . '.json';
        $submit_user_details = array();
        $submit_response_details = array();
        foreach(array_keys($req) as $post_var) {
            if (strripos($post_var, '_' . $campaign_id) === strlen($post_var) - strlen($campaign_id) - 1) {
                if (stripos($post_var, 'ingagehub_collect_') !== FALSE && stripos($post_var, 'ingagehub_collect_email_') === FALSE) {
                    $field_name = substr($post_var, 18, strlen($post_var) - 18 - strlen($campaign_id) - 1);
                    $submit_user_details[$field_name] = $req[$post_var]['value'];
                }

                if (stripos($post_var, 'ingagehub_attachment_') !== FALSE && $req[$post_var]['checked'] === 'true') {
                    preg_match('/ingagehub_attachment_([0-9]+)_([0-9]+)_([0-9]+)$/i', $post_var, $matches);
                    if (count($matches) > 0) {
                        if (!array_key_exists($matches[2], $submit_response_details) || !is_array($submit_response_details[$matches[2]])) {
                            $submit_response_details[$matches[2]] = array();
                        }
                        $submit_response_details[$matches[2]][$matches[1]] = $req[$post_var]['value'];
                        if (array_key_exists('freeText', $req[$post_var]) && !empty($req[$post_var]['freeText'])) {
                            $submit_response_details[$matches[2]]['free_text'] = $req[$post_var]['freeText'];
                            $submit_response_details[$matches[2]]['free_text_value'] = $req[$post_var]['value'];
                        }
                        if (array_key_exists('rank', $req[$post_var]) && !empty($req[$post_var]['rank'])) {
                            if (!array_key_exists('rank', $submit_response_details[$matches[2]]) || !is_array($submit_response_details[$matches[2]]['rank'])) {
                                $submit_response_details[$matches[2]]['rank'] = array();
                            }
                            $submit_response_details[$matches[2]]['rank'][$matches[1]] = $req[$post_var]['rank'];
                        }
                    }
                }
            }
        }

        $submit_data = array(
            'authenticity_token' => $csrf_token,
            'user' => array(
                'email' => $req['ingagehub_collect_email_' . $campaign_id]['value']
            ),
            'details' => $submit_user_details,
            'r' => $submit_response_details
        );

        $headers = array();

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $headers['X-Original-User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $headers['X-Original-Remote-Addr'] = $_SERVER['REMOTE_ADDR'];
        }

        $response = wp_remote_post($submit_url, array(
            'body' => $submit_data,
            'redirection' => 0,
            'headers' => $headers,
            'cookies' => array(
                '_session_id' => $session_id
            )
        ));

        $response_data = null;

        if (is_array($response) && array_key_exists('body', $response)) {
            $response_data = json_decode($response['body']);
        }

        if (is_null($response_data)) {
            wp_send_json(array(
                'next_page' => $next_page,
                'response' => array('status' => 'ERROR')
            ));
        } else {
            wp_send_json(array(
                'next_page' => $next_page,
                'response' => $response_data
            ));
        }
    }
}

new INgageHubConnectServices();
?>
