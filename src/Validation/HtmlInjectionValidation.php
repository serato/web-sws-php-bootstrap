<?php

namespace Serato\SwsApp\Validation;

use DOMDocument;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

trait HtmlInjectionValidation
{

    // /**
    //  * Checks if a value contains HTML injection
    //  * 
    //  * @param string $value
    //  * @return boolean
    //Sameera's , Sameera &amp; Roshan's home
    //  */ 
    
    // function hasHtmlInjection(&$value)
    // {
    //     return preg_match('/<\S[^>]*>/', $value);
    // }

    /**
     * Checks if a value contains HTML injection
     * 
     * @param string $value
     * @return boolean
     */
    function hasHtmlInjection(&$value)
    {   //this does not work for $ , 's , &amp; and &quot; and &apos; and &lt; and &gt;
        return $value !== htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validates that the request body does not contain HTML injection
     * 
     * @param $request
     * @throws Exception
     */
    public function validateForHtmlInjection($requestBody)
    {
        $containsHtml = false;

        if (is_array($requestBody)) {
            array_walk_recursive($requestBody, function ($value) use (&$containsHtml) {
                if ($this->hasHtmlInjection($value)) {
                    $containsHtml = true;
                }
            });
        } elseif (is_string($requestBody)) {
            if ($this->hasHtmlInjection($requestBody)) {
                $containsHtml = true;
            }
        } else {
            throw new InvalidArgumentException("Input must be an array or string.");
        }

        if ($containsHtml) {
            throw new Exception("HTML Injection detected in request body");
        }
    }

    public function validateXmlForHtmlInjection($xmlInput)
    {

        /**
         * Recursively checks DOM elements for HTML injections
         *
         * @param DOMElement $element
         * @return boolean
         */

        $dom = new DOMDocument();

        // Disable error reporting for malformed XML
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlInput, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new Exception("Invalid XML format");
        }

        if ($this->_checkDomElementForHtmlInjection($dom->documentElement)) {
            throw new Exception("HTML Injection detected in XML input");
        }
    }

    private function _checkDomElementForHtmlInjection($element)
        {
            foreach ($element->attributes as $attribute) {
                if ($this->hasHtmlInjection($attribute->value)) {
                    return true;
                }
            }

            foreach ($element->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    if ($this->_checkDomElementForHtmlInjection($child)) {
                        return true;
                    }
                } elseif ($child instanceof DOMText && $this->hasHtmlInjection($child->textContent)) {
                    return true;
                }
            }

            return false;
        }


    public function encodeToPreventHtmlInjection($content)
    {
        //sanitize request body
        array_walk_recursive($content, function (&$value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        });
        return $content;
    }

}