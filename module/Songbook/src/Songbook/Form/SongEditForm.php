<?php

namespace Songbook\Form;

use Zend\Form\Form;

class SongEditForm extends Form
{
    public function __construct($name = null)
    {
        $this->wrapElements(TRUE);
        // we want to ignore the name passed
        parent::__construct('song_edit');

        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'options' => array(
                'label' => 'Title',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
            ),
        ));
    }
}