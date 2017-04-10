<?php

namespace DynamicYield\Integration\Plugin;

use DynamicYield\Integration\Helper\Data;

class ResponseHttpBefore
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ResponseHttpBefore constructor
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param $subject
     * @param $value
     * @return array
     */
    public function beforeAppendBody($subject, $value)
    {
        if (is_callable([$subject, 'isAjax']) && $subject->isAjax()) {
            return [$value];
        }

        if ($this->_helper->isEnabled() && $this->_helper->getJsIntegration()) {
            preg_match('<head(.*)>', $value, $match);

            if (isset($match[0])) {
                $value = str_ireplace($match[0], $match[0] . "\n" . $this->_helper->getHtmlMarkup(), $value);
            }
        }

        return [$value];
    }
}