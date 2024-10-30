<?php

class INgageHubConnectCampaignAttachmentProperties
{
    private $_needs_save;

    private $_below_submit;
    private $_content_tag;
    private $_explicit_classes;
    private $_has_sharing_image;
    private $_include_email;
    private $_include_interactive;

    public function __construct($deserialized_attachment = null) {
        $this->_needs_save = true;

        $this->_below_submit = false;
        $this->_content_tag = '';
        $this->_explicit_classes = '';
        $this->_has_sharing_image = false;
        $this->_include_email = false;
        $this->_include_interactive = true;

        if (!is_null($deserialized_attachment)) {
            if (property_exists($deserialized_attachment, 'properties')) {
                $reflection_class = new ReflectionClass($this);
                foreach ($reflection_class->getProperties(ReflectionProperty::IS_PRIVATE) as $reflected_property) {
                    if (substr($reflected_property->getName(), 0, 1) != '_') continue;

                    $property_local = $reflected_property->getName();
                    $property = substr($property_local, 1);

                    if (property_exists($deserialized_attachment->properties, $property)) {
                        $this->$property_local = $deserialized_attachment->properties->$property;
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
//            echo '<h1>properties level</h1>';
            return true;
        } else {
            return false;
        }
    }

    public function as_array() {
        $properties_as_array = array();

        foreach (array(
                     'below_submit',
                     'content_tag',
                     'explicit_classes',
                     'has_sharing_image',
                     'include_email',
                     'include_interactive',
                 ) as $prop
        ) {
            $properties_as_array[$prop] = $this->$prop;
        };

        return $properties_as_array;
    }
}
?>
