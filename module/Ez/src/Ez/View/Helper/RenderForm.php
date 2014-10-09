<?php
namespace Ez\View\Helper;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\ViewModel;

class RenderForm extends AbstractHelper
{

    const STYLE_DL = 'dl';

    public function __invoke (Form $form, $style = self::STYLE_DL)
    {
        $elements = array();

        if ($style == self::STYLE_DL) {
            $inputTag = 'dd';
            $labelTag = 'dt';
            $errorsTag = 'p';
            $rowTag = null;
            $enclosingTag = 'dl';
        } else {
            $inputTag = null;
            $labelTag = null;
            $errorsTag = null;
            $rowTag = null;
            $enclosingTag = null;
        }

        foreach ($form->getElements() as $element) {
            $elements[] = $this->view->formRowTag($element, null, null, null, $inputTag, $labelTag, $errorsTag, $rowTag ) . PHP_EOL;
        }

        $html = implode(PHP_EOL, $elements);

        if( !is_null( $enclosingTag ) ) {
            $html = $this->view->htmlTag( $html, $enclosingTag );
        }

        $html = $this->view->form()->openTag($form) . $html . $this->view->form()->closeTag($form);
        return $html;
    }
}