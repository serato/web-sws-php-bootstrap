<?php

namespace Serato\SwsApp\Exception;

use Psr\Http\Message\RequestInterface as Request;

/**
 * AbstractWebViewException
 *
 * Exception class to be thrown during a web view request.
 *
 * The exception should be caught and have its `message`, `code` and `http_response_code`
 * values formatted and returned to the client.
 */

abstract class AbstractWebViewException extends AbstractException
{
    /* @var array */
    protected $errorMessages = [];

    /* @var string */
    private $lang;

    public function __construct(?string $lang = 'en', Request $request = null)
    {
        $this->lang = $lang;
        parent::__construct('', $request);
    }

    /**
     * Returns an error message in the specified language.
     *
     * If a message is not available in the specific language the English
     * language equivalent is returned.
     *
     * @param string $lang  ISO language code
     * @return string
     */
    public function getTranslatedMessage(): string
    {
        if (isset($this->errorMessages[$this->lang])) {
            return $this->errorMessages[$this->lang];
        } else {
            return $this->errorMessages['en'];
        }
    }
}
