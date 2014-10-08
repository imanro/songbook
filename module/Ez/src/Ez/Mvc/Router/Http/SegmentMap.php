<?php

namespace Ez\Mvc\Router\Http;

use Zend\Mvc\Router\Http\Segment;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\Http\RouteMatch as RouteMatch;

class SegmentMap extends Segment {

    protected $map;

    public function __construct($route, $map, array $constraints = array(), array $defaults = array())
    {
        parent::__construct($route, $constraints, $defaults);
        $this->map = $map;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Segment
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (!isset($options['map'])) {
            $options['map'] = array();
        }

        if (!isset($options['constraints'])) {
            $options['constraints'] = array();
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['route'], $options['map'], $options['constraints'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request     $request
     * @param  string|null $pathOffset
     * @param  array       $options
     * @return RouteMatch|null
     * @throws Exception\RuntimeException
     */
    public function match(Request $request, $pathOffset = null, array $options = array())
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        $regex = $this->regex;

        if ($this->translationKeys) {
            if (!isset($options['translator']) || !$options['translator'] instanceof Translator) {
                throw new Exception\RuntimeException('No translator provided');
            }

            $translator = $options['translator'];
            $textDomain = (isset($options['text_domain']) ? $options['text_domain'] : 'default');
            $locale     = (isset($options['locale']) ? $options['locale'] : null);

            foreach ($this->translationKeys as $key) {
                $regex = str_replace('#' . $key . '#', $translator->translate($key, $textDomain, $locale), $regex);
            }
        }

        if ($pathOffset !== null) {
            $result = preg_match('(\G' . $regex . ')', $path, $matches, null, $pathOffset);
        } else {
            $result = preg_match('(^' . $regex . '$)', $path, $matches);
        }

        if (!$result) {
            return null;
        }

        $matchedLength = strlen($matches[0]);
        $params        = array();

        foreach ($this->paramMap as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $this->decode($matches[$index]);
                $params[$name] = $this->replaceByMap($name, $params[$name]);
            }
        }

        return new RouteMatch(array_merge($this->defaults, $params), $matchedLength);
    }

    protected function replaceByMap($name,$value)
    {
        if(isset($this->map[$name]))
        {
            $rule = $this->map[$name];
            $value = preg_replace($rule[0], $rule[1], $value );
        }

        return $value;
    }
}
