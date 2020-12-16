<?php
namespace Serato\SwsApp\Test\EventDispatcher\Normalizer;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\EventDispatcher\Normalizer\PsrMessageNormalizer;
use ReflectionMethod;
use ReflectionClassConstant;

/**
 * Unit tests for `Serato\SwsApp\EventDispatcher\Normalizer\PsrMessageNormalizer`
 *
 * Current only smokes tests the normalizePsrServerRequestInterface and normalizePsrResponseInterface.
 * Other public methods are not explicitly tested, by all are used by two methods that are tested.
 *
 */
class PsrMessageNormalizerTest extends TestCase
{
    // public function testNormalizePsrServerRequestInterface()
    // {
    //     //
    // }

    // public function testNormalizePsrResponseInterface()
    // {
    //     //
    // }

    # ** public **
    # normalizeUri
    # normalizeHttpHeaders
    # normalizeServerParams
    # normalizeRequestAttributes
    # normalizePsrMessageBody

    /**
     * Tests the `PsrMessageNormalizer::normalizeHeaderValue` private method.
     *
     * This method simply breaks up HTTP header values that contains one or more';'
     * separators and splits them into an array of the constituent pieces.
     *
     * This method works around the quirky behviour of the Slim HTTP message classes.
     * Sometimes they will correctly split a header value but sometimes, most notably with
     * a `Content-Type` header, it will not - it will return the whole string, including
     * ';' character(s), as the value.
     *
     * @dataProvider normalizeHeaderValueProvider
     *
     * @param mixed $raw
     * @param array $normalized
     * @return void
     */
    public function testNormalizeHeaderValue($raw, array $normalized): void
    {
        $normalizer = new PsrMessageNormalizer;
        $method = new ReflectionMethod($normalizer, 'normalizeHeaderValue');
        $method->setAccessible(true);
        $this->assertEquals($method->invoke($normalizer, $raw), $normalized);
    }

    public function normalizeHeaderValueProvider()
    {
        # In most real-world sceanrios $raw will be an array with a single item in it.
        # But PsrMessageNormalizer::normalizeContentTypeHeaderValue allows for a string value to be provided.
        # In this case the raw value is returned unmodified in an array with only the item being the raw string.
        # The data below should make that behaviour clear.
        return [
            # Common used `Content-Type` header values
            ['text/html', ['text/html']],
            [['text/html'], ['text/html']],
            ['text/html; charset=UTF-8', ['text/html; charset=UTF-8']],
            [['text/html; charset=UTF-8'], ['text/html', 'charset=UTF-8']],
            ['application/json', ['application/json']],
            [['application/json'], ['application/json']],
            # `Content-Type` header value commonly used by HTML forms
            ['multipart/form-data; boundary=something', ['multipart/form-data; boundary=something']],
            [['multipart/form-data; boundary=something'], ['multipart/form-data', 'boundary=something']],
            # `Content-Type` header values that look batshit insane so I'll put them in here for fun.
            [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ['application/vnd.openxmlformats-officedocument.presentationml.presentation']
            ],
            [
                'application/vnd.mozilla.xul+xml',
                ['application/vnd.mozilla.xul+xml']
            ],
            # `Content-Disposition` header values can use this form of value
            [
                'form-data; name="myFile"; filename="foo.txt"',
                ['form-data; name="myFile"; filename="foo.txt"']
            ],
            [
                ['form-data; name="myFile"; filename="foo.txt"'],
                ['form-data', 'name="myFile"', 'filename="foo.txt"']
            ],
        ];
    }

    /**
     * Tests the `PsrMessageNormalizer::removeUriPassword` private method.
     *
     * This method intends to remove the "password" component of various values returned
     * from a normalized `Psr\Http\Message\UriInterface` instance.
     *
     * These values only exist exist in requests that use `Basic` authentication. The values
     * contain sub-strings of the form "<user name>:<password>", all in clear text.
     *
     * The `PsrMessageNormalizer::removeUriPassword` method replaces the clear text password
     * with a placeholder.
     *
     * @dataProvider removeUriPasswordProvider
     *
     * @param string $password
     * @param string $raw
     * @param string $clean
     * @return void
     */
    public function testRemoveUriPassword(string $password, string $raw, string $clean): void
    {
        $normalizer = new PsrMessageNormalizer;
        $method = new ReflectionMethod($normalizer, 'removeUriPassword');
        $method->setAccessible(true);
        $this->assertEquals($method->invoke($normalizer, $raw), $clean);
        $this->assertEquals(0, substr_count($method->invoke($normalizer, $raw), $password));
    }

    public function removeUriPasswordProvider()
    {
        $normalizer = new PsrMessageNormalizer;
        $const = new ReflectionClassConstant($normalizer, 'PASSWORD_REMOVED_SUBSTITUTION_VALUE');

        $appId = 'my-app-id-123';
        $password = 'D0nT_hacK-M33';
        $hosts = ['192.168.4.14:8686', '114.23.249.114', 'id.serato.com', 'id.serato.biz'];
        $replacement = $const->getValue();

        $data = [];
        foreach ($hosts as $host) {
            # URI for used by `authority` property of UriInterface
            # eg. 'my-app-id-123:D0nT_hacK-M33@id.serato.com'
            $data[] = [
                $password,
                $appId . ':' . $password . '@' . $host,
                $appId . ':' . $replacement . '@' . $host,
            ];
            # URI for used by `userInfo` property of UriInterface
            # eg. 'my-app-id-123:D0nT_hacK-M33'
            $data[] = [
                $password,
                $appId . ':' . $password,
                $appId . ':' . $replacement,
            ];
            # URI for used by `baseUrl` property of UriInterface (when HTTPS protocol)
            # eg. 'https://my-app-id-123:D0nT_hacK-M33@id.serato.com'
            $data[] = [
                $password,
                'https://' . $appId . ':' . $password . '@' . $host,
                'https://' . $appId . ':' . $replacement . '@' . $host,
            ];
            # URI for used by `baseUrl` property of UriInterface (when HTTP protocol)
            # eg. 'http://my-app-id-123:D0nT_hacK-M33@id.serato.com'
            $data[] = [
                $password,
                'http://' . $appId . ':' . $password . '@' . $host,
                'http://' . $appId . ':' . $replacement . '@' . $host,
            ];
        }

        return $data;
    }

    /**
     * Tests the `PsrMessageNormalizer::stripRawBodyParams` private method.
     *
     * This method removes sensitive data from encoded PSR message bodies. The method takes a message body
     * encoded into a string, along with the content type of the encoded message body.
     *
     * Two content types are currently supported (and therefore tested):
     *
     * - `application/json`
     * - `application/x-www-form-urlencoded`
     *
     * Note: Our web applications do allow for and work with request message bodies of content type
     * `multipart/form-data`. But the PHP runtime does not expose the raw HTTP request body string
     * when this content type is used so the `PsrMessageNormalizer::stripRawBodyParams` method is
     * never called when the content type is `multipart/form-data`.
     *
     * These paths and substitutions are specified in `PsrMessageNormalizer::BODY_PARAMETER_SUBSTITUTIONS`.
     *
     * @dataProvider stripRawBodyParamsProvider
     *
     * @param string $contentType
     * @param string $dirty
     * @param string $clean
     * @return void
     */
    public function testStripRawBodyParams(string $contentType, string $dirty, string $clean): void
    {
        $normalizer = new PsrMessageNormalizer;
        $method = new ReflectionMethod($normalizer, 'stripRawBodyParams');
        $method->setAccessible(true);

        $this->assertEquals($method->invoke($normalizer, $dirty, $contentType), $clean);
    }

    public function stripRawBodyParamsProvider(): array
    {
        $senstiveString = 'TOP-SECRET_SHHHH';
        $data = [];

        $normalizer = new PsrMessageNormalizer;
        $const = new ReflectionClassConstant($normalizer, 'BODY_PARAMETER_SUBSTITUTIONS');

        ###################################################################################################
        # Data for message bodies of content type `application/x-www-form-urlencoded`                     #
        ###################################################################################################
        # Note: These can ONLY be simple key/value pairs.

        # Construct some data that contains actual paths we're testing for in the normalizer
        # but limit these to paths that only contain one level of depth (ie. just a key/value pair)
        foreach ($const->getValue() as $findReplace) {
            if (count($findReplace[0]) === 1) {
                $replacement = $findReplace[1];
                $data[] = [
                    'application/x-www-form-urlencoded',
                    http_build_query([$findReplace[0][0] => $senstiveString, 'p2' => 'value 2', 'p3' => 'value three']),
                    http_build_query([$findReplace[0][0] => $replacement, 'p2' => 'value 2', 'p3' => 'value three'])
                ];
                $data[] = [
                    'application/x-www-form-urlencoded',
                    http_build_query(['p1' => 'val1', $findReplace[0][0] => $senstiveString, 'p3' => 'value three']),
                    http_build_query(['p1' => 'val1', $findReplace[0][0] => $replacement, 'p3' => 'value three'])
                ];
            }
        }

        # Add is some data that does not match any of the paths were looking for (ie. the string value should
        # remain unaltered)
        $data[] = [
            'application/x-www-form-urlencoded',
            http_build_query(['p1' => 'val1', 'p2' => 'value 2', 'p3' => 'value three']),
            http_build_query(['p1' => 'val1', 'p2' => 'value 2', 'p3' => 'value three'])
        ];

        #################################################################################################
        # Data for message bodies of content type `application/json`                                    #
        #################################################################################################

        # This self::stripBodyParamsProvider creates nice deeply structured message bodies as native PHP arrays.
        # So re-use this.
        foreach ($this->stripBodyParamsProvider() as $messageBodies) {
            $data[] = [
                'application/json',
                json_encode($messageBodies[0]),
                json_encode($messageBodies[1])
            ];
        }

        return $data;
    }

    /**
     * Tests the `PsrMessageNormalizer::stripBodyParams` private method.
     *
     * This method removes sensitive data from PSR message bodies. It takes a native PHP
     * array representation of the message body. It then traverses specific path and, if required,
     * replaces the value of a specific array path with a defined replacement value.
     *
     * These paths and substitutions are specified in `PsrMessageNormalizer::BODY_PARAMETER_SUBSTITUTIONS`.
     *
     * @dataProvider stripBodyParamsProvider
     *
     * @param array $dirty
     * @param array $clean
     * @return void
     */
    public function testStripBodyParams(array $dirty, array $clean): void
    {
        $normalizer = new PsrMessageNormalizer;
        $method = new ReflectionMethod($normalizer, 'stripBodyParams');
        $method->setAccessible(true);

        $this->assertEquals($method->invoke($normalizer, $dirty), $clean);
    }

    public function stripBodyParamsProvider(): array
    {
        $extraData = [
            'p1' => 'val1',
            'p2' => 'value 2',
            'p3' => 'value three',
            'p4' => [
                'p5' => 'pee 5',
                'p6' => [
                    'p7' => 'so deep'
                ]
            ]
        ];
        $data = [];

        # Construct some data that contains actual paths we're testing for in the normalizer
        # Throw in some extra data at various path locations too to ensure that all extra data
        # is correctly preserved.
        $senstiveString = 'TOP-SECRET_SHHHH';

        $normalizer = new PsrMessageNormalizer;
        $const = new ReflectionClassConstant($normalizer, 'BODY_PARAMETER_SUBSTITUTIONS');
        foreach ($const->getValue() as $findReplace) {
            $pathArray = $findReplace[0];
            $replacement = $findReplace[1];
            # Create the body structure
            $data[] = [
                array_merge($this->createBodyArray($pathArray, $senstiveString, $extraData), $extraData),
                array_merge($this->createBodyArray($pathArray, $replacement, $extraData), $extraData)
            ];
        }

        # Now add in some additional structures that should all pass through the stripping
        # process unaltered
        foreach ([[], ['p1' => 'val1', 'p2' => 'value 2', 'p3' => 'value three'], $extraData] as $item) {
            $data[] = [$item, $item];
        }

        return $data;
    }

    /**
     * @param array $keys
     * @param string $value
     * @return array|string
     */
    private function createBodyArray(array $keys, string $value, array $extraData)
    {
        $key = array_shift($keys);
        if (count($keys) === 0) {
            $body = array_merge([$key => $value], $extraData);
        } else {
            $body = array_merge([$key => $this->createBodyArray($keys, $value, $extraData)], $extraData);
        }
        return $body;
    }
}
