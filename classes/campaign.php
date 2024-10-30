<?php

include com_ingagehub_plugin_dir_path() . 'classes/campaign_attachment_contents_response.php';

include com_ingagehub_plugin_dir_path() . 'classes/campaign_attachment_contents.php';
include com_ingagehub_plugin_dir_path() . 'classes/campaign_attachment_properties.php';

include com_ingagehub_plugin_dir_path() . 'classes/campaign_attachment.php';

class INgageHubConnectCampaign
{
    private $_needs_save;

    private $_id;

    private $_attachments;
    private $_content_policy_id;
    private $_created_by;
    private $_email_subject;
    private $_ga_article;
    private $_name;
    private $_published;
    private $_redirect_url;
    private $_style_set_id;
    private $_updated_by;

    public function __construct($deserialized_campaign = null)
    {
        $this->_needs_save = true;

        $this->_id = 0;

        $this->_attachments = array();
        $this->_content_policy_id = 0;
        $this->_created_by = 0;
        $this->_email_subject = null;
        $this->_ga_article = null;
        $this->_name = null;
        $this->_published = false;
        $this->_redirect_url = null;
        $this->_style_set_id = null;
        $this->_updated_by = 0;

        if (is_null($deserialized_campaign)) {
            $att = new INgageHubConnectCampaignAttachment();
            $this->add_attachment($att);

            $att = new INgageHubConnectCampaignAttachment();
            $att->attachment_type = 'text';
            $att->properties->include_interactive = false;
            $att->properties->include_email = true;
            $att->contents->html = '&lt;&lt; Add Email Here &gt;&gt;';
            $this->add_attachment($att);

            $att = new INgageHubConnectCampaignAttachment();
            $att->attachment_type = 'text';
            $att->properties->include_interactive = false;
            $att->properties->has_sharing_image = true;
            $att->contents->html = '&lt;&lt; Add Social Network Details Here &gt;&gt;';
            $this->add_attachment($att);

        } else {
            $reflection_class = new ReflectionClass($this);
            foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
                if (substr($reflected_property->getName(), 0, 1) != '_') continue;

                $property_local = $reflected_property->getName();
                $property = substr($property_local, 1);

                if ($property != 'attachments') {
                    if (property_exists($deserialized_campaign, $property)) {
                        $this->$property_local = $deserialized_campaign->$property;
                    }
                }
            }

            if (property_exists($deserialized_campaign, 'attachments')) {
                if (is_array($deserialized_campaign->attachments)) {
                    foreach ($deserialized_campaign->attachments as $attachment) {
                        $this->add_attachment(new INgageHubConnectCampaignAttachment($attachment));
                    }
                }
            }

            $this->_needs_save = false;
        }
    }

    public function __get($property)
    {
        $property_local = '_' . $property;
        if (property_exists($this, $property_local)) {
            return $this->$property_local;
        } else {
            return null;
        }
    }

    public function __set($property, $value)
    {
        $property_local = '_' . $property;
        if (property_exists($this, $property_local)) {
            $this->$property_local = $value;
            $this->_needs_save = true;
        }
    }

    public function check_needs_save()
    {
        if ($this->needs_save) {
//            echo '<h1>campaign level</h1>';
            return true;
        }

        foreach ($this->attachments as $attachment) {
            if ($attachment->check_needs_save()) {
//                echo '<h1>campaign attachments level</h1>';
                return true;
            }
        }

        return false;
    }

    public static function load_one($campaign_id, $force_load = false)
    {
        $campaign = null;

        if (isset($_SESSION['ih_connect_cached_campaign']) && (int)$_SESSION['ih_connect_cached_campaign']->id == (int)$campaign_id && !$force_load) {
            $campaign = $_SESSION['ih_connect_cached_campaign'];

        } else {
            $auth = new INgageHubConnectAuth();
            if ($auth->login()) {
                $response = wp_remote_get($auth->site_url() . '/services/get_campaign?id=' . $campaign_id, array(
                    'redirection' => 0,
                    'cookies' => array(
                        '_session_id' => $auth->session_id()
                    )
                ));

                if (is_array($response) && array_key_exists('body', $response)) {
                    $campaign = json_decode($response['body']);

                    if (!is_null($campaign) && property_exists($campaign, 'status') && property_exists($campaign, 'campaign') && $campaign->status == 'OK') {
                        $campaign = new INgageHubConnectCampaign($campaign->campaign);

                    } else {
                        $campaign = null;
                    }
                }
            }
        }

        $_SESSION['ih_connect_cached_campaign'] = $campaign;

        return $campaign;
    }

    public static function cached_present()
    {
        if (isset($_SESSION['ih_connect_cached_campaign'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function cached_id()
    {
        if (isset($_SESSION['ih_connect_cached_campaign'])) {
            return $_SESSION['ih_connect_cached_campaign']->id;
        } else {
            return 0;
        }
    }

    public static function cached_needs_save()
    {
        if (!isset($_SESSION['ih_connect_cached_campaign'])) {
//            echo '<h1>not set</h1>';
            return false;
        }

        $campaign = $_SESSION['ih_connect_cached_campaign'];

        return $campaign->check_needs_save();
    }

    public static function clear_cached()
    {
        if (isset($_SESSION['ih_connect_cached_campaign'])) {
            unset($_SESSION['ih_connect_cached_campaign']);
        }
    }

    private static function compare_by_name($a, $b)
    {
        if ($a->name == $b->name) {
            return $a->id - $b->id;
        } elseif ($a->name > $b->name) {
            return 1;
        } else {
            return -1;
        }
    }

    private static function compare_by_name_desc($a, $b)
    {
        if ($a->name == $b->name) {
            return $a->id - $b->id;
        } elseif ($a->name > $b->name) {
            return -1;
        } else {
            return 1;
        }
    }

    private static function compare_by_updated_at($a, $b)
    {
        $dt_a = new DateTime($a->updated_at);
        $dt_b = new DateTime($b->updated_at);
        if ($dt_a == $dt_b) {
            return $a->id - $b->id;
        } elseif ($dt_a > $dt_b) {
            return 1;
        } else {
            return -1;
        }
    }

    private static function compare_by_updated_at_desc($a, $b)
    {
        $dt_a = new DateTime($a->updated_at);
        $dt_b = new DateTime($b->updated_at);
        if ($dt_a == $dt_b) {
            return $a->id - $b->id;
        } elseif ($dt_a > $dt_b) {
            return -1;
        } else {
            return 1;
        }
    }

    public static function load_all($sort = null, $force_load = false)
    {
        $campaigns = null;

        if (isset($_SESSION['ih_connect_cached_campaigns']) && !$force_load) {
            $campaigns = $_SESSION['ih_connect_cached_campaigns'];

        } else {
            $auth = new INgageHubConnectAuth();
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
        }

        if (is_null($campaigns)) {
            $campaigns = new stdClass();
            $campaigns->status = 'OK';
            $campaigns->campaigns = array();

        } elseif (!property_exists($campaigns, 'status') || $campaigns->status != 'OK') {
            $campaigns = new stdClass();
            $campaigns->status = 'OK';
            $campaigns->campaigns = array();

        } elseif (!is_null($sort)) {
            if ($sort == 'name_desc') {
                usort($campaigns->campaigns, 'INgageHubConnectCampaign::compare_by_name_desc');
            } elseif ($sort == 'updated_at') {
                usort($campaigns->campaigns, 'INgageHubConnectCampaign::compare_by_updated_at');
            } elseif ($sort == 'updated_at_desc') {
                usort($campaigns->campaigns, 'INgageHubConnectCampaign::compare_by_updated_at_desc');
            } else {
                usort($campaigns->campaigns, 'INgageHubConnectCampaign::compare_by_name');
            }
        } else {
            usort($campaigns->campaigns, 'INgageHubConnectCampaign::compare_by_name');
        }

        $_SESSION['ih_connect_cached_campaigns'] = $campaigns;

        return $campaigns;
    }

    public function save($publish = false)
    {
        $new_id = 0;

        $auth = new INgageHubConnectAuth();
        if ($auth->login()) {
            $method = $publish ? 'save_campaign_and_publish' : 'save_campaign';

            $response = wp_remote_post($auth->site_url() . '/services/' . $method, array(
                'body' => json_encode($this->as_array()),
                'redirection' => 0,
                'cookies' => array(
                    '_session_id' => $auth->session_id()
                ),
                'headers' => array(
                    'X-CSRF-Token' => $auth->csrf_token(),
                    'Content-Type' => 'application/json'
                )
            ));

            if (is_array($response) && array_key_exists('body', $response)) {
                $campaign = json_decode($response['body']);

                if (property_exists($campaign, 'status') && property_exists($campaign, 'id') && $campaign->status == 'OK') {
                    $new_id = (int)$campaign->id;
                }
            }

            if ($new_id == 0 && $publish) {
                $new_id = $this->save(false);
            }
        }

        return $new_id;
    }

    public function destroy()
    {
        $auth = new INgageHubConnectAuth();
        if ($auth->login()) {
            wp_remote_post($auth->site_url() . '/campaigns/' . $this->_id . '.json', array(
                'body' => array(
                    'authenticity_token' => $auth->csrf_token(),
                    '_method' => 'DELETE'
                ),
                'redirection' => 0,
                'cookies' => array(
                    '_session_id' => $auth->session_id()
                )
            ));
        }
    }

    private function add_attachment($new_attachment)
    {
        $max_id = 0;
        $max_sort = 0;
        foreach ($this->_attachments as $attachment) {
            $max_id = $attachment->id > $max_id ? $attachment->id : $max_id;
            $max_sort = $attachment->sort > $max_sort ? $attachment->sort : $max_sort;
        }

        $max_id++;

        if ($new_attachment->id == 0) {
            $new_attachment->id = $max_id;
        }

        $max_sort++;

        if ($new_attachment->sort != $max_sort) {
            $new_attachment->sort = $max_sort;
        }

        array_push($this->_attachments, $new_attachment);

        $this->_needs_save = true;
    }

    public function save_attachment($attachment)
    {
        if ($attachment->id == 0) {
            $this->add_attachment($attachment);

        } else {
            foreach ($this->attachments as $index => $existing_attachment) {
                if ($attachment->id == $existing_attachment->id) {
                    $this->_attachments[$index] = $attachment;
                    $this->_needs_save = true;
                    break;
                }
            }
        }
    }

    public function get_attachment($attachment_id)
    {
        $found_attachment = null;
        foreach ($this->attachments as $attachment) {
            if ($attachment->id == (int)$attachment_id) {
                $found_attachment = $attachment;
                break;
            }
        }

        return $found_attachment;
    }

    public function remove_attachment($attachment) {
        $splice_index = -1;
        foreach ($this->_attachments as $index => $existing_attachment) {
            if ($existing_attachment->id == $attachment->id) {
                $splice_index = $index;
                break;
            }
        }

        if ($splice_index >= 0) {
            array_splice($this->_attachments, $splice_index, 1);
            $this->_needs_save = true;
        }
    }

    public function as_array() {
        $campaign_as_array = array();

        foreach (array(
                     'id',
                     'content_policy_id',
                     'email_subject',
                     'ga_article',
                     'name',
                     'published',
                     'redirect_url',
                     'style_set_id'
                 ) as $prop
        ) {
            $campaign_as_array[$prop] = $this->$prop;
        };

        $campaign_as_array['attachments'] = array();

        if (is_array($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                array_push($campaign_as_array['attachments'], $attachment->as_array());
            }
        }

        return $campaign_as_array;
    }
}
?>
