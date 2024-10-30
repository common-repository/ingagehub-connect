<?php
class INgageHubConnectCampaignAttachmentContentsResponse
{
    private $_needs_save;

    private $_id;

    private $_conversations;
    private $_label;
    private $_tool_tip;
    private $_value;

    public function __construct($deserialized_response = null)
    {
        $this->_needs_save = true;

        $this->_id = 0;

        $this->_conversations = array();
        $this->_label = null;
        $this->_tool_tip = null;
        $this->_value = null;

        if (!is_null($deserialized_response)) {
            $reflection_class = new ReflectionClass($this);
            foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
                if (substr($reflected_property->getName(), 0, 1) != '_') continue;

                $property_local = $reflected_property->getName();
                $property = substr($property_local, 1);

                if (property_exists($deserialized_response, $property)) {
                    $this->$property_local = $deserialized_response->$property;
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

            if ($property_local != '_id') {
                $this->_needs_save = true;
            }
        }
    }

    public function check_needs_save() {
        if ($this->needs_save) {
//            echo '<h1>response level</h1>';
            return true;
        } else {
            return false;
        }
    }

    public function as_array() {
        $response_as_array = array();

        foreach (array(
                     'conversations',
                     'label',
                     'tool_tip',
                     'value'
                 ) as $prop
        ) {
            $response_as_array[$prop] = $this->$prop;
        };

        $response_as_array['value'] = (string) $response_as_array['value'];

        return $response_as_array;
    }
}
?>
