<?php
class INgageHubConnectCampaignAttachmentContents
{
    private $_needs_save;

    private $_has_other_box;
    private $_html;
    private $_question_layout;
    private $_question_text;
    private $_question_type;
    private $_response_format;
    private $_responses;

    public function __construct($deserialized_attachment = null)
    {
        $this->_needs_save = true;

        $this->_has_other_box = null;
        $this->_html = null;
        $this->_question_layout = null;
        $this->_question_text = null;
        $this->_question_type = null;
        $this->_response_format = null;
        $this->_responses = null;

        if (!is_null($deserialized_attachment)) {
            if (property_exists($deserialized_attachment, 'contents')) {
                $reflection_class = new ReflectionClass($this);
                foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
                    if (substr($reflected_property->getName(), 0, 1) != '_') continue;

                    $property_local = $reflected_property->getName();
                    $property = substr($property_local, 1);

                    if ($property == 'responses' && property_exists($deserialized_attachment->contents, 'responses')) {
                        $this->_responses = array();
                        if (is_array($deserialized_attachment->contents->responses)) {
                            foreach ($deserialized_attachment->contents->responses as $response) {
                                $this->add_response(new INgageHubConnectCampaignAttachmentContentsResponse($response));
                            }
                        }

                    } else {
                        if (property_exists($deserialized_attachment->contents, $property)) {
                            $this->$property_local = $deserialized_attachment->contents->$property;
                        }
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
//            echo '<h1>contents level</h1>';
            return true;
        }

        if (is_array($this->responses)) {
            foreach($this->responses as $response) {
                if ($response->check_needs_save()) {
//                    echo '<h1>contents responses level</h1>';
                    return true;
                }
            }
        }

        return false;
    }

    private function add_response($new_response) {
        if (is_null($new_response->value)) {
            $new_value = 0;
            foreach($this->_responses as $response) {
                if ($response->value > $new_value) {
                    $new_value =  $response->value;
                }
            }

            $new_value++;
            $new_response->value = $new_value;
        }

        $total = array_push($this->_responses, $new_response);

        $new_response->id = $total;

        $this->_needs_save = true;
    }

    public function save_response($response) {
        if ($response->id == 0) {
            $this->add_response($response);

        } else {
            foreach ($this->responses as $index => $existing_response) {
                if ($response->id == $existing_response->id) {
                    $this->_responses[$index] = $response;
                    $this->_needs_save = true;
                    break;
                }
            }
        }
    }

    public function remove_response($response) {
        $splice_index = -1;
        foreach ($this->_responses as $index => $existing_response) {
            if ($existing_response->id == $response->id) {
                $splice_index = $index;
                break;
            }
        }

        if ($splice_index >= 0) {
            array_splice($this->_responses, $splice_index, 1);
            $this->_needs_save = true;
        }
    }

    public function move_response($response, $direction) {
        $my_id = $response->id;

        $r1 = $r2 = null;

        if ($direction == 'move_response_up') {
            if ($my_id == 1) return;

            $r1 = $this->_responses[$my_id - 1];
            $r2 = $this->_responses[$my_id - 2];

        } elseif ($direction == 'move_response_down') {
            if ($my_id == count($this->_responses)) return;

            $r1 = $this->_responses[$my_id];
            $r2 = $this->_responses[$my_id - 1];
        }

        $r1->id -= 1;
        $r2->id += 1;

        $this->_responses[$r1->id - 1] = $r1;
        $this->_responses[$r2->id - 1] = $r2;

        $this->_needs_save = true;
    }

    public function as_array() {
        $contents_as_array = array();

        foreach (array(
                     'has_other_box',
                     'html',
                     'question_layout',
                     'question_text',
                     'question_type',
                     'response_format',
                 ) as $prop
        ) {
            $contents_as_array[$prop] = $this->$prop;
        };

        $contents_as_array['responses'] = array();
        if (is_array($this->responses)) {
            foreach ($this->responses as $response) {
                array_push($contents_as_array['responses'], $response->as_array());
            }
        }

        return $contents_as_array;
    }
}
?>
