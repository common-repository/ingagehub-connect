<?php
class INgageHubConnectBaseQuestion
{
    private $_id;
    private $_question_text;
    private $_question_type;
    private $_response_format;
    private $_has_other_box;
    private $_industry;
    private $_audience;
    private $_objective;
    private $_responses;

    public function __construct($deserialized_base_question)
    {
        $this->_question_type = 0;
        $this->_question_text = '<< Enter Question Here >>';
        $this->_responses = array();

        $reflection_class = new ReflectionClass($this);
        foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
            if (substr($reflected_property->getName(), 0, 1) != '_') continue;

            $property_local = $reflected_property->getName();
            $property = substr($property_local, 1);

            if ($property === 'responses' && property_exists($deserialized_base_question, $property)) {
                foreach ($deserialized_base_question->responses as $response) {
                    array_push($this->_responses, new INgageHubConnectCampaignAttachmentContentsResponse($response));
                }
            } elseif (property_exists($deserialized_base_question, $property)) {
                $this->$property_local = $deserialized_base_question->$property;
            }
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

    public static function load_all($force_load = false)
    {
        $base_questions_response = null;

        if (isset($_SESSION['ih_connect_cached_base_questions']) && !$force_load) {
            return $_SESSION['ih_connect_cached_base_questions'];

        } else {
            $auth = new INgageHubConnectAuth();
            if ($auth->login()) {
                $response = wp_remote_get($auth->site_url() . '/services/base_questions', array(
                    'redirection' => 0,
                    'cookies' => array(
                        '_session_id' => $auth->session_id()
                    )
                ));

                if (is_array($response) && array_key_exists('body', $response)) {
                    $base_questions_response = json_decode($response['body']);
                }
            }
        }

        if (is_null($base_questions_response)) {
            $base_questions_response = new stdClass();
            $base_questions_response->status = 'OK';
            $base_questions_response->base_questions = array();
            $base_questions_response->industries = array();
            $base_questions_response->audiences = array();
            $base_questions_response->objectives = array();

        } elseif (!property_exists($base_questions_response, 'status') || $base_questions_response->status != 'OK') {
            $base_questions_response = new stdClass();
            $base_questions_response->status = 'OK';
            $base_questions_response->base_questions = array();
            $base_questions_response->industries = array();
            $base_questions_response->audiences = array();
            $base_questions_response->objectives = array();
        }

        $base_questions = array();

        foreach ($base_questions_response->base_questions as $base_question) {
            array_push($base_questions, new INgageHubConnectBaseQuestion($base_question));
        }

        $industries = array();

        foreach ($base_questions_response->industries as $industry) {
            array_push($industries, $industry);
        }

        $audiences = array();

        foreach ($base_questions_response->audiences as $audience) {
            array_push($audiences, $audience);
        }

        $objectives = array();

        foreach ($base_questions_response->objectives as $objective) {
            array_push($objectives, $objective);
        }

        $result = array(
            'base_questions' => $base_questions,
            'industries' => $industries,
            'audiences' => $audiences,
            'objectives' => $objectives
        );

        $_SESSION['ih_connect_cached_base_questions'] = $result;

        return $result;
    }

    public function response_format_description()
    {
        if ($this->response_format == 0) {
            return 'Select One' . $this->other_box_description();
        } elseif ($this->response_format == 1) {
            return 'Select Multiple' . $this->other_box_description();
        } elseif ($this->response_format == 2) {
            return 'Ranking';
        } elseif ($this->response_format == 3) {
            return 'Free Text';
        }
    }

    private function other_box_description()
    {
        if ($this->has_other_box) {
            return ' w/Other Box';
        } else {
            return '';
        }
    }
}
?>
