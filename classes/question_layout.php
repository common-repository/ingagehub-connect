<?php
class INgageHubConnectQuestionLayout
{
    private $_id;
    private $_label;
    private $_template;
    private $_answer_template;

    public function __construct($deserialized_question_layout)
    {
        $_id = 0;
        $_label = null;
        $_template = null;
        $_answer_template = null;

        $reflection_class = new ReflectionClass($this);
        foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
            if (substr($reflected_property->getName(), 0, 1) != '_') continue;

            $property_local = $reflected_property->getName();
            $property = substr($property_local, 1);

            if (property_exists($deserialized_question_layout, $property)) {
                $this->$property_local = $deserialized_question_layout->$property;
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
        $question_layouts_response = null;

        if (isset($_SESSION['ih_connect_cached_question_layouts']) && !$force_load) {
            return $_SESSION['ih_connect_cached_question_layouts'];

        } else {
            $auth = new INgageHubConnectAuth();
            if ($auth->login()) {
                $response = wp_remote_get($auth->site_url() . '/services/question_layouts', array(
                    'redirection' => 0,
                    'cookies' => array(
                        '_session_id' => $auth->session_id()
                    )
                ));

                if (is_array($response) && array_key_exists('body', $response)) {
                    $question_layouts_response = json_decode($response['body']);
                }
            }
        }

        if (is_null($question_layouts_response)) {
            $question_layouts_response = new stdClass();
            $question_layouts_response->status = 'OK';
            $question_layouts_response->question_layouts = array();

        } elseif (!property_exists($question_layouts_response, 'status') || $question_layouts_response->status != 'OK') {
            $question_layouts_response = new stdClass();
            $question_layouts_response->status = 'OK';
            $question_layouts_response->question_layouts = array();
        }

        $question_layouts = array();

        foreach ($question_layouts_response->question_layouts as $question_layout) {
            array_push($question_layouts, new INgageHubConnectQuestionLayout($question_layout));
        }

        $_SESSION['ih_connect_cached_question_layouts'] = $question_layouts;

        return $question_layouts;
    }

}
?>
