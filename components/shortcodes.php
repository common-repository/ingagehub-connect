<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

class INgageHubConnectShortcodes {
    private static $js_included = false;
    private static $css_included = false;
    private static $auth = NULL;
    private static $campaigns_on_page = array();

    private $response_data = NULL;
    private $shortcode_number = 0;
    private $ih_token = '';

    public function __construct() {
        add_shortcode('ingagehub_campaign', array( $this, 'com_ingagehub_shortcodes' ) );
        add_shortcode('ingagehub_attachment', array( $this, 'com_ingagehub_shortcodes' ) );
        add_shortcode('ingagehub_field', array( $this, 'com_ingagehub_shortcodes' ) );
        add_shortcode('ingagehub_submit', array( $this, 'com_ingagehub_shortcodes' ) );
        add_shortcode('ingagehub_next_page', array( $this, 'com_ingagehub_shortcodes' ) );
    }

    private function get_response_data($id) {

        $effective_id = '0';
        $query_string = '';

        if (is_preview()) {
            INgageHubConnectShortcodes::$auth = new INgageHubConnectAuth();

            if (INgageHubConnectShortcodes::$auth->login() === false) {
                throw new Exception('Previewing and not logged in!');
            }

            $effective_id = $id;
            $query_string = '?email=preview';

        } else {
            INgageHubConnectShortcodes::$auth = new INgageHubConnectAuth(get_post()->post_author);
            INgageHubConnectShortcodes::$auth->set_session_id('')->set_csrf_token('');

            $effective_id = $this->ih_token;
        }

        $headers = array();

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $headers['X-Original-User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $headers['X-Original-Remote-Addr'] = $_SERVER['REMOTE_ADDR'];
        }

        $response = wp_remote_get(INgageHubConnectShortcodes::$auth->site_url() . '/cr/' . $effective_id . '.json' . $query_string, array(
            'redirection' => 0,
            'headers' => $headers,
            'cookies' => array(
                '_session_id' => INgageHubConnectShortcodes::$auth->session_id()
            )
        ));

        if (!(is_array($response) && array_key_exists('body', $response))) {
            return;
        }

        $this->response_data = NULL;

        $response_data = json_decode($response['body']);

        if (is_null($response_data) || !property_exists($response_data, 'status') || !property_exists($response_data, 'campaign')) {
            return;
        }

        if ($response_data->status !== 'OK') {
            return;
        }

        if (((int) $response_data->campaign->id) != ((int) $id)) {
            throw new Exception('response id ' . $response_data->campaign->id . ' does not match requested id ' . $id);
        }

        $this->response_data = $response_data;

        if (!is_preview()) {
            INgageHubConnectShortcodes::$auth->set_session_id(INgageHubConnectShortcodes::$auth->parse_session_id($response))->set_csrf_token($this->response_data->csrf_token);
        }
    }

    private function attachment_markup($campaign, $campaign_attachment, $question_layouts) {
        $template = $question_layouts->{$campaign_attachment->contents->question_layout}->template;
        $answer_template = $question_layouts->{$campaign_attachment->contents->question_layout}->answer_template;
        $response_format = property_exists($campaign_attachment->contents, 'response_format') ? $campaign_attachment->contents->response_format : 0;
        $has_other_box = property_exists($campaign_attachment->contents, 'has_other_box') ? $campaign_attachment->contents->has_other_box : false;

        $return = str_replace('{q}', $campaign_attachment->contents->question_text, $template);

        $answers = '';

        if ($response_format === '3') {
            $answers = '<input type="hidden" '.
                'id="ingagehub_attachment_1_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'name="ingagehub_attachment_1_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'value="1" ' .
                '/>';

            $answers .= '<textarea  ' .
                'id="ingagehub_attachment_free_text_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'name="ingagehub_attachment_free_text_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'class="ihFreeTextBox" ' .
                '></textarea>';

            $answers .= '<input type="hidden" ' .
                'id="ingagehub_attachment_free_text_value_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'name="ingagehub_attachment_free_text_value_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                'value="1" ' .
                '/>';

        } else {
            $answer_num = 0;
            foreach($campaign_attachment->contents->responses as $response) {
                $answer_num++;

                $input_id = 'ingagehub_attachment_' . $answer_num . '_' . $campaign_attachment->id . '_' . $campaign->id;

                $answer = '';
                $other_box = '';
                $class_name = 'ihAnswerInput';

                if ($response_format === '0' || $response_format === '1') {

                    if ($response_format === '0') {
                        $input_type = 'radio';
                        $input_name = 'ingagehub_attachment_' . $campaign_attachment->id . '_' . $campaign->id;
                        $class_name .= ' ihAnswerInputRadio';
                    } else {
                        $input_type = 'checkbox';
                        $input_name = $input_id;
                        $class_name .= ' ihAnswerInputCheckbox';
                    }

                    if (($has_other_box === '1' || $has_other_box === true) && $answer_num === sizeof($campaign_attachment->contents->responses)) {
                        $other_box = '<input ' .
                            'type="text" ' .
                            'id="ingagehub_attachment_free_text_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                            'name="ingagehub_attachment_free_text_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                            'class="ihOtherBox" ' .
                            '/>';

                        $other_box .= '<input ' .
                            'type="hidden" ' .
                            'id="ingagehub_attachment_free_text_value_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                            'name="ingagehub_attachment_free_text_value_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                            'value="' . htmlspecialchars($response->value) . '" ' .
                            '/>';

                        $class_name .= ' ihOtherBoxTrigger';
                    }

                    $answer = '<input ' .
                        'type="' . $input_type . '" ' .
                        'id="' . $input_id . '" ' .
                        'name="' . $input_name . '" ' .
                        'class="' . $class_name . '" ' .
                        'value="' . htmlspecialchars($response->value) . '" ' .
                        ' />';

                } else if ($response_format === '2') {
                    $class_name .= ' ihAnswerInputText ihAnswerInputRank';

                    $answer .= '<input ' .
                        'type="hidden" ' .
                        'id="' . $input_id . '" ' .
                        'name="' . $input_id . '" ' .
                        'value="' . htmlspecialchars($response->value) . '" ' .
                        '/>';

                    $answer .= '<input ' .
                        'type="text" ' .
                        'id="ingagehub_attachment_rank_' . $answer_num . '_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
                        'name="ingagehub_attachment_rank_' . $answer_num . '_' . $campaign_attachment->id . '_' . $campaign->id . '" '.
                        'class="' . $class_name . '" ' .
                        'maxlength="' . strlen((string)sizeof($campaign_attachment->contents->responses)) . '" ' .
                        'value="' . $answer_num . '" ' .
                        '/>';
                }

                $answer .= htmlspecialchars($response->label);
                $answer .= '<br/>' . $other_box;

                $answer = str_replace('{a}', $answer, $answer_template);
                $answer = str_replace('{i}', $answer_num, $answer);

                $answers .= $answer;
            }
        }

        $return = str_replace('{a}', $answers, $return);

        $return = '<form name="ingagehub_attachment_form_' . $campaign_attachment->id . '_' . $campaign->id . '">' .
            '<input ' .
            'type="hidden" ' .
            'id="ingagehub_attachment_response_format_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
            'name="ingagehub_attachment_response_format_' . $campaign_attachment->id . '_' . $campaign->id . '" ' .
            'value="' . $response_format . '" ' .
            '/>' .
            '<div ' .
            'id="ihCampaignAttachment' . $campaign_attachment->id . '" ' .
            'class="ihCampaignAttachment ihCampaignAttachmentQuestion"' .
            '>' .
            $return .
            '</div>' .
            '</form>';

        return $return;
    }

    public function com_ingagehub_shortcodes($atts, $content, $tag) {
        $this->shortcode_number++;
        $return = '(the content for this shortcode failed to render [IHC ' . $tag . $this->shortcode_number . '])';

        // anything but ingagehub_campaign must be in preview or have a value ih_token; otherwise, return blank
        // ...ingagehub_campaign makes the same determination after trying to set ih_token.
        if ($tag !== 'ingagehub_campaign' && !is_preview() && strlen($this->ih_token) === 0) {
            return NULL;
        }

        switch($tag) {
            case 'ingagehub_campaign':
                $this->response_data = NULL;
                $this->ih_token = '';

                if (isset($atts['id'])) {
                    if (in_array((int)$atts['id'], INgageHubConnectShortcodes::$campaigns_on_page)) {
                        return NULL;
                    } else {
                        INgageHubConnectShortcodes::$campaigns_on_page[] = (int)$atts['id'];
                    }

                    if (isset($_GET['ih_token'])) {
                        $this->ih_token = $_GET['ih_token'];
                    } elseif (isset($atts['default_token'])) {
                        $this->ih_token = $atts['default_token'];
                    } else {
                        $this->ih_token = '';
                    }

                    if (!is_preview() && strlen($this->ih_token) === 0) {
                        return NULL;
                    }

                    $this->get_response_data($atts['id']);

                    if (!is_null($this->response_data)) {
                        $return = '<form id="ingagehub_campaign_form_' . $this->response_data->campaign->id . '">' .
                            '<input type="hidden" id="ingagehub_csrf_token_' . $this->response_data->campaign->id . '" name="ingagehub_csrf_token_' . $this->response_data->campaign->id . '" value="' . htmlspecialchars(INgageHubConnectShortcodes::$auth->csrf_token()) . '" />' .
                            '<input type="hidden" id="ingagehub_token_' . $this->response_data->campaign->id . '" name="ingagehub_token_' . $this->response_data->campaign->id . '" value="' . htmlspecialchars($this->ih_token) . '" />' .
                            '<input type="hidden" id="ingagehub_session_id_' . $this->response_data->campaign->id . '" name="ingagehub_session_id_' . $this->response_data->campaign->id . '" value="' . htmlspecialchars(INgageHubConnectShortcodes::$auth->session_id()) . '" />' .
                            '<input type="hidden" id="ingagehub_site_url_' . $this->response_data->campaign->id . '" name="ingagehub_site_url_' . $this->response_data->campaign->id . '" value="' . htmlspecialchars(INgageHubConnectShortcodes::$auth->site_url()) . '" />' .
                            '</form>';
                    }
                }
                break;

            case 'ingagehub_attachment':
                if (!is_null($this->response_data)) {
                    if (!INgageHubConnectShortcodes::$css_included) {
                        INgageHubConnectShortcodes::$css_included = true;
                        wp_enqueue_style('ingagehub_attachment_css', com_ingagehub_plugin_dir_url() . 'css/style.min.css');
                    }

                    foreach($this->response_data->campaign->campaign_attachments as $campaign_attachment) {
                        if ($campaign_attachment->id === ((int)$atts['id'])) {
                            $return = $this->attachment_markup($this->response_data->campaign, $campaign_attachment, $this->response_data->question_layouts);
                            break;
                        }
                    }
                }
                break;

            case 'ingagehub_field':
                if (!is_null($this->response_data)) {
                    $return = '';
                }
                break;

            case 'ingagehub_submit':
                if (!is_null($this->response_data)) {
                    if (!INgageHubConnectShortcodes::$js_included) {
                        INgageHubConnectShortcodes::$js_included = true;
                        wp_enqueue_script('ingagehub_responder_js', com_ingagehub_plugin_dir_url() . 'js/responder.min.js');
                    }

                    $email = $this->response_data->user->email;
                    if (is_null($email) || strlen($email) == 0) {
                        $user = wp_get_current_user();
                        if (property_exists($user, 'data') && property_exists($user->data, 'ID') && $user->data->ID != 0 && property_exists($user->data, 'user_email')) {
                            $email = $user->data->user_email;
                        }
                    }

                    $return = '<form id="ingagehub_submit_form_' . $this->response_data->campaign->id . '">';
                    $return .= '<div class="ihCampaignSubmitForm">';

                    $return .= '<label for="ingagehub_collect_email_' . $this->response_data->campaign->id . '">Email</label>';
                    $return .= '<input type="text" id="ingagehub_collect_email_' . $this->response_data->campaign->id . '" value="' . $email . '" /><br/>';
                    foreach($this->response_data->user->user_detail_fields as $udf) {
                        $return .= '<label for="ingagehub_collect_' . $udf->name . '_' . $this->response_data->campaign->id . '">' . $udf->display_name . '</label>';
                        $return .= '<input type="text" id="ingagehub_collect_' . $udf->name . '_' . $this->response_data->campaign->id . '" value="' . $udf->value . '" /><br/>';
                    };

                    $return .= '<button type="button" id="ingagehub_submit_' . $this->response_data->campaign->id . '">';
                    if (isset($atts['button_text'])) {
                        $return .= $atts['button_text'];
                    } else {
                        $return .= 'Submit Your Responses';
                    }
                    $return .= '</button>';

                    $return .= '</div>';
                    $return .= '</form>';
                }
                break;

            case 'ingagehub_next_page':
                if (!is_null($this->response_data)) {
                    $return = '<form id="ingagehub_next_page_form_' . $this->response_data->campaign->id . '">';
                    $return .= '<input type="hidden" id="ingagehub_next_page_' . $this->response_data->campaign->id . '" value="';
                    $return .= (strlen($content) == 0 ? get_site_url() : $content);
                    $return .= '" />';
                    $return .= '</form>';
                }

            default:
                break;
        }

        return $return;
    }
}

new INgageHubConnectShortcodes();
?>
