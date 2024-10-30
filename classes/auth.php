<?php
class INgageHubConnectAuth
{
    private $_user_id;
    private $_site_url;
    private $_user_name;
    private $_password;
    private $_session_id;
    private $_csrf_token;

    public function __construct($user_id = NULL)
    {
        if ($user_id === NULL) {
            $user_id = get_current_user_id();
        }

        $this->_user_id = $user_id;
        $this->_site_url = get_user_option('ih_site_url', $user_id);
        $this->_user_name = get_user_option('ih_user_name', $user_id);
        $this->_password = get_user_option('ih_password', $user_id);
        $this->_session_id = get_user_option('ih_session_id', $user_id);
        $this->_csrf_token = get_user_option('ih_csrf_token', $user_id);
    }

    public function set_site_url($site_url) {
        $this->_site_url = $site_url;
        return $this;
    }

    public function set_user_name($user_name) {
        $this->_user_name = $user_name;
        return $this;
    }

    public function set_password($password) {
        $this->_password = $password;
        return $this;
    }

    public function set_session_id($session_id) {
        $this->_session_id = $session_id;
        return $this;
    }

    public function set_and_save_session_id($session_id) {
        $this->_session_id = $session_id;
        update_user_option($this->_user_id, 'ih_session_id', $session_id);
        return $this;
    }

    public function set_csrf_token($csrf_token) {
        $this->_csrf_token = $csrf_token;
        return $this;
    }

    public function set_and_save_csrf_token($csrf_token) {
        $this->_csrf_token = $csrf_token;
        update_user_option($this->_user_id, 'ih_csrf_token', $csrf_token);
        return $this;
    }

    public function login() {
        if (strlen($this->_session_id) > 0) {
            if ($this->session_is_valid($this->_session_id)) {
                return true;
            } else {
                $this->logout();
            }
        }

        $response = wp_remote_get($this->_site_url . '/users/sign_in');

        $csrf_token = $this->parse_csrf_token($response);
        if (strlen($csrf_token) == 0) return false;

        $session_id_1 = $this->parse_session_id($response);
        if (strlen($session_id_1) == 0) return false;

        $response = wp_remote_post( $this->_site_url . '/users/sign_in', array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 0,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array( ),
                'body' => array(
                    'authenticity_token' => $csrf_token,
                    'user[email]' => $this->_user_name,
                    'user[password]' => $this->_password
                ),
                'cookies' => array(
                    '_session_id' => $session_id_1
                )
            )
        );

        $session_id_2 = $this->parse_session_id($response);
        if ($session_id_1 == $session_id_2 || strlen($session_id_2) == 0) return false;

        $response = wp_remote_get($this->_site_url . '/profiles/current', array(
            'redirection' => 0,
            'cookies' => array(
                '_session_id' => $session_id_2
            )
        ));

        $csrf_token = $this->parse_csrf_token($response);
        if (strlen($csrf_token) == 0) return false;


        $success = $this->session_is_valid($session_id_2);

        if ($success === false) {
            $session_id_2 = '';
            $csrf_token = '';
        }

        $this->set_and_save_session_id($session_id_2);
        $this->set_and_save_csrf_token($csrf_token);

        return $success;
    }

    private function session_is_valid($session_id) {
        $response = wp_remote_get($this->_site_url . '/profiles/current.json', array(
            'redirection' => 0,
            'cookies' => array(
                '_session_id' => $session_id
            )
        ));

        if (is_array($response) && array_key_exists('body', $response)) {
            $jd = json_decode($response['body']);
            if (is_null($jd) || !property_exists($jd, 'email')) {
                return false;
            } else {
                return ($jd->email === $this->_user_name);
            }
        } else {
            return false;
        }
    }

    public function logout()
    {
        $response = wp_remote_post($this->_site_url . '/users/sign_out', array(
            'method' => 'POST',
            'redirection' => 0,
            'body' => array(
                '_method' => 'delete',
                'authenticity_token' => $this->_csrf_token
            ),
            'cookies' => array(
                '_session_id' => $this->_session_id
            )
        ));

        $this->set_and_save_session_id('');
        $this->set_and_save_csrf_token('');
    }

    public function site_url() {
        return $this->_site_url;
    }

    public function session_id() {
        return $this->_session_id;
    }

    public function parse_session_id($response) {
        $session_id = '';

        if (is_array($response) && array_key_exists('cookies', $response)) {
            foreach ($response['cookies'] as $c) {
                if ($c->name === '_session_id') {
                    $session_id = $c->value;
                    break;
                }
            }
        }

        return $session_id;
    }

    public function csrf_token() {
        return $this->_csrf_token;
    }

    public function parse_csrf_token($response) {
        $csrf_token = '';

        if (is_array($response) && array_key_exists('body', $response)) {
            preg_match('/<meta ([^>]*name="csrf-token"[^>]*)\/>/', $response['body'], $rr);
            if (count($rr) > 0) {
                preg_match('/content="([^"]+)"/', $rr[1], $rr);
                if (count($rr) > 0) {
                    $csrf_token = $rr[1];
                }
            }
        }

        return $csrf_token;
    }
}
?>
