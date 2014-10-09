<?php
namespace Ez\View\Helper;
use Zend\View\Helper\AbstractHtmlElement;
/**
 * Helper for ordered and unordered lists
 */
class HtmlTag extends AbstractHtmlElement
{

    /**
     * Generates a 'List' element.
     *
     * @param array|string $items
     *            Array with the elements of the list
     * @return string The list XHTML.
     */
    public function __invoke ($items, $tag, array $tagAttributes = null)
    {
        if (! is_array($items)) {
            $items = array(
                $items
            );
        }

        $array = array();

        if (! is_null($tagAttributes) && count($tagAttributes)) {
            $attributesString = ' ' . $this->assembleAttributes($tagAttributes);
        } else {
            $attributesString = '';
        }

        foreach ($items as $item) {
            $array[] = sprintf('<%s%s>%s</%s>', $tag, $attributesString, $item, $tag);
        }

        return implode(PHP_EOL, $array);
    }

    protected function assembleAttributes ($tagAttributes)
    {
        array_map(function  ($key, &$value)
        {
            $value = $key . '="' . $value . '"';
        }, $tagAttributes);

        return implode(' ', $tagAttributes);
    }
}
