<?php
class INgageHubConnectCampaignAttachment
{
    private $_needs_save;

    private $_id;

    private $_attachment_type;
    private $_contents;
    private $_div_close;
    private $_div_float;
    private $_div_width;
    private $_properties;
    private $_question_id;
    private $_sort;

    public function __construct($deserialized_attachment = null)
    {
        $this->_needs_save = true;

        $this->_id = 0;

        $this->_attachment_type = 'text';
        $this->_contents = new INgageHubConnectCampaignAttachmentContents();
        $this->_div_close = 1;
        $this->_div_float = 'None';
        $this->_div_width = 'Full';
        $this->_properties = new INgageHubConnectCampaignAttachmentProperties();
        $this->_question_id = 0;
        $this->_sort = 0;

        if (is_null($deserialized_attachment)) {
            $this->_attachment_type = 'adhoc';

            $this->_contents->has_other_box = false;
            $this->_contents->question_layout = 5;
            $this->_contents->question_text = '&lt;&lt; Enter Question Text Here &gt;&gt;';
            $this->_contents->question_type = 'New Question';
            $this->_contents->response_format = 0;
            $this->_contents->responses = array();

        } else {
            $reflection_class = new ReflectionClass($this);
            foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
                if (substr($reflected_property->getName(), 0, 1) != '_') continue;

                $property_local = $reflected_property->getName();
                $property = substr($property_local, 1);

                if ($property == 'contents') {
                    $this->_contents = new INgageHubConnectCampaignAttachmentContents($deserialized_attachment);

                } elseif ($property == 'properties') {
                    $this->_properties = new INgageHubConnectCampaignAttachmentProperties($deserialized_attachment);

                } else {
                    if (property_exists($deserialized_attachment, $property)) {
                        $this->$property_local = $deserialized_attachment->$property;
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

    public function check_needs_save() {
        if ($this->needs_save) {
//            echo '<h1>attachment level</h1>';
            return true;
        } elseif ($this->properties->check_needs_save()) {
//            echo '<h1>attachment properties level</h1>';
            return true;
        } elseif ($this->contents->check_needs_save()) {
//            echo '<h1>attachment contents level</h1>';
            return true;
        } else {
            return false;
        }
    }

    public static function from_template($base_question_id) {
        $attachment = new INgageHubConnectCampaignAttachment();

        $base_question_data = INgageHubConnectBaseQuestion::load_all();
        foreach ($base_question_data['base_questions'] as $base_question) {
            if ((int)$base_question->id === $base_question_id) {
                $attachment->attachment_type = 'adhoc';

                $attachment->contents->question_layout = 5;
                $attachment->contents->response_format = $base_question->response_format;
                $attachment->contents->has_other_box = $base_question->has_other_box;

                if (is_array($base_question->responses)) {
                    foreach ($base_question->responses as $response) {
                        $new_response = new INgageHubConnectCampaignAttachmentContentsResponse();
                        $new_response->label = $response->label;
                        $new_response->value = $response->value;
                        $attachment->contents->save_response($new_response);
                    }
                }

                $attachment->contents->question_text = $base_question->question_type === 0 ? '&lt;&lt; Enter Question Text Here &gt;&gt;' : $base_question->question_text;
                $attachment->contents->question_type = $base_question->question_type === 0 ? $base_question->question_text : 'Question Intelligence';

                break;
            }
        }

        return $attachment;
    }

    public function response_format_description()
    {
        if ($this->_contents->response_format == 0) {
            return 'Select One' . $this->other_box_description();
        } elseif ($this->_contents->response_format == 1) {
            return 'Select Multiple' . $this->other_box_description();
        } elseif ($this->_contents->response_format == 2) {
            return 'Ranking';
        } elseif ($this->_contents->response_format == 3) {
            return 'Free Text';
        }
    }

    private function other_box_description()
    {
        if ($this->_contents->has_other_box) {
            return ' w/Other Box';
        } else {
            return '';
        }
    }

    public function question_text_stripped()
    {
        return strip_tags($this->_contents->question_text);
    }

    public function get_response($response_id)
    {
        $found_response = null;
        if (is_array($this->_contents->responses)) {
            foreach ($this->contents->responses as $response) {
                if ($response->id == (int)$response_id) {
                    $found_response = $response;
                    break;
                }
            }
        }
        return $found_response;
    }

    public function as_array() {
        $attachment_as_array = array();

        foreach (array(
                     'id',
                     'attachment_type',
                     'div_close',
                     'div_float',
                     'div_width',
                     'question_id',
                     'sort'
                 ) as $prop
        ) {
            $attachment_as_array[$prop] = $this->$prop;
        };

        $attachment_as_array['properties'] = $this->properties->as_array();

        $attachment_as_array['contents'] = $this->contents->as_array();

        return $attachment_as_array;
    }
}
?>
