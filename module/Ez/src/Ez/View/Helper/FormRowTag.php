<?php
namespace Ez\View\Helper;
use Zend\Form\View\Helper;
use Zend\Form\View\Helper\FormRow;
use Zend\Form\ElementInterface;

class FormRowTag extends FormRow
{
    protected $labelTag;

    protected $inputTag;

    protected $errorsTag;

    protected $rowTag;

    public function __invoke(ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $partial = null, $inputTag = null, $labelTag = null, $errorsTag = null, $rowTag = null )
    {
        $this->setLabelTag($labelTag);

        $this->setInputTag($inputTag);

        $this->setErrorsTag($errorsTag);

        $this->setRowTag($rowTag);

        return parent::__invoke($element, $labelPosition, $renderErrors, $partial );
    }

    public function __call ($method, $params)
    {
        if (strpos($method, 'set') === 0) {
            $name = lcfirst(substr($method, 3));

            if (property_exists($this, $name)) {
                $this->$name = $params[0];
            } else {
                throw new \Exception(sprintf('property "%s" is not exists in class "%s"', $method, get_class($this)));
            }
        } else {
            throw new \Exception(sprintf('callend unknown method "%s"', $method));
        }
    }

    public function render (ElementInterface $element)
    {
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $labelHelper = $this->getLabelHelper();
        $elementHelper = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }
        }

        // Does this element have errors ?
        if (count($element->getMessages()) > 0 && ! empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttributes = $classAttributes . $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        if ($this->partial) {
            $vars = array(
                'element' => $element,
                'label' => $label,
                'labelAttributes' => $this->labelAttributes,
                'labelPosition' => $this->labelPosition,
                'renderErrors' => $this->renderErrors
            );

            return $this->view->render($this->partial, $vars);
        }

        if ($this->renderErrors) {
            $elementErrors = $elementErrorsHelper->render($element);
            if( $this->errorsTag ) {
                $this->view->htmlTag( $elementErrors, $this->errorsTag );
            }
        }

        $elementString = $elementHelper->render($element);

        if( !is_null($this->inputTag ))
        {
            $elementString = $this->view->htmlTag( $elementString, $this->inputTag );
        }

        // hidden elements do not need a <label>
        // -https://github.com/zendframework/zf2/issues/5607
        $type = $element->getAttribute('type');
        if (isset($label) && '' !== $label && $type !== 'hidden') {

            $labelAttributes = array();

            if ($element instanceof LabelAwareInterface) {
                $labelAttributes = $element->getLabelAttributes();
            }

            if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently as the HTML
            // standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            if ($type === 'multi_checkbox' || $type === 'radio' || $element instanceof MonthSelect) {
                $markup = sprintf('<fieldset><legend>%s</legend>%s</fieldset>', $label, $elementString);

            } else {
                // Ensure element and label will be separated if element has an
                // `id`-attribute.
                // If element has label option `always_wrap` it will be nested
                // in any case.
                if ($element->hasAttribute('id') &&
                         ($element instanceof LabelAwareInterface && ! $element->getLabelOption('always_wrap'))) {
                    $labelOpen = '';
                    $labelClose = '';
                    $label = $labelHelper($element);
                } else {
                    $labelOpen = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                if ($label !== '' && (! $element->hasAttribute('id')) ||
                         ($element instanceof LabelAwareInterface && $element->getLabelOption('always_wrap'))) {

                    if (! is_null($this->labelTag)) {
                        $label = $this->view->htmlTag( $labelOpen . $label . $labelClose, $this->labelTag);
                    } else {
                        $label = $labelOpen . $label . $labelClose;
                    }
                }

                // Button element is a special case, because label is always
                // rendered inside it
                if ($element instanceof Button) {
                    $labelOpen = $labelClose = $label = '';
                }

                switch ($this->labelPosition) {
                    case self::LABEL_PREPEND:
                        $markup = $label . $elementString;
                        break;
                    case self::LABEL_APPEND:
                    default:
                        $markup = $elementString . $label;
                        break;
                }
            }

            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }

        if (! is_null($this->rowTag)) {
            $markup = $this->view->htmlTag($markup, $this->rowTag);
        }

        return $markup;
    }
}
