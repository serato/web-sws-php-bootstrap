<?php

namespace Serato\SwsApp\Validation\Rules;

use Rakit\Validation\Rule;

class NoHtmlTag extends Rule
{
    /**
      * Validation rule name for params without HTML tags.
      * @var string
    */
    public const NO_HTML_TAG_RULE = 'no_html_tag';
    /**
      * Regex validation rule for params without HTML tags.
      * @var string
    */
    public const NO_HTML_TAG_REGEX = '/^(?:(?!<[^>]*$)[^<])*$/';

    /** @var string */
    protected $message = "The :attribute contains html tag.";

    /**
     * Check the $value is valid by checking it does not contain html tags
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        return preg_match(self::NO_HTML_TAG_REGEX, $value) > 0;
    }
}
