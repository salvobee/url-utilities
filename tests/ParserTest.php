<?php

namespace SalvoBee\UrlUtilities\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SalvoBee\UrlUtilities\Dictionaries\UrlComponents;
use SalvoBee\UrlUtilities\Parser;

class ParserTest extends TestCase
{
    // region Basic Parser Test

    /**
     * @dataProvider urlParseDataProvider
     *
     * @return void
     */
    public function test_urls_are_parsed_accordingly(array $expectations, string $inputUrl)
    {
        [ $query_parameters, $components, $stripped_url ] = $expectations;

        $url = new Parser($inputUrl);

        // Check query parameters
        $this->assertEquals($query_parameters, $url->getQueryParameters());

        // Check url components
        $this->assertEquals($components[UrlComponents::SCHEME], $url->getScheme());
        $this->assertEquals($components[UrlComponents::USERNAME], $url->getUsername());
        $this->assertEquals($components[UrlComponents::PASSWORD], $url->getPassword());
        $this->assertEquals($components[UrlComponents::HOST], $url->getHost());
        $this->assertEquals($components[UrlComponents::PORT], $url->getPort());
        $this->assertEquals($components[UrlComponents::PATH], $url->getPath());
        $this->assertEquals($components[UrlComponents::QUERY_STRING], $url->getQueryString());
        $this->assertEquals($components[UrlComponents::FRAGMENT], $url->getFragment());

        // Check url without query string
        $this->assertEquals($stripped_url, $url->stripQueryString());
    }

    // endregion

    // region Basic Parameter Manipulation

    /**
     * @dataProvider parameterManipulateDataProvider
     *
     * @param string $expectedUrl
     * @param string $parameterToManipulate
     * @param string $newValue
     * @param string $inputUrl
     */
    public function test_query_parameters_can_be_manipulated(string $expectedUrl, string $parameterToManipulate, string $newValue, string $inputUrl)
    {
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedUrl, $url->manipulateParameter($parameterToManipulate, $newValue));
    }

    /**
     * @dataProvider parameterAddDataProvider
     *
     * @param string $expectedUrl
     * @param string $parameterToAdd
     * @param string $valueForNewParameter
     * @param string $inputUrl
     */
    public function test_query_parameters_can_be_added(string $expectedUrl, string $parameterToAdd, string $valueForNewParameter, string $inputUrl)
    {
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedUrl, $url->addParameter($parameterToAdd, $valueForNewParameter));
    }

    /**
     * @dataProvider parameterRemoveDataProvider
     *
     * @param string $expectedUrl
     * @param string $parameterToRemove
     * @param string $inputUrl
     */
    public function test_query_parameters_can_be_removed(string $expectedUrl, string $parameterToRemove, string $inputUrl)
    {
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedUrl, $url->removeParameter($parameterToRemove));
    }

    // endregion

    // region Basic Parameter Manipulation Exceptions

    public function test_adding_existing_query_parameters_throw_exception()
    {
        $url = new Parser('http://www.domain.com?arg=value');
        $this->expectException(InvalidArgumentException::class);
        $url->addParameter('arg', 'test');
    }

    public function test_manipulating_query_parameters_with_no_query_strings_throw_exception()
    {
        $url = new Parser('http://www.domain.com');
        $this->expectException(InvalidArgumentException::class);
        $url->manipulateParameter('foo', 'test');
    }

    public function test_removing_query_parameters_with_no_query_strings_throw_exception()
    {
        $url = new Parser('http://www.domain.com');
        $this->expectException(InvalidArgumentException::class);
        $url->removeParameter('foo');
    }

    // endregion

    // region Array Values Manipulation Exceptions

    /**
     * @dataProvider multipleValueRemoveDataProvider
     *
     * @param string $expectedValue
     * @param string $fieldKey
     * @param string $fieldValue
     * @param string $inputUrl
     */
    public function test_query_multiple_values_can_be_removed(string $expectedValue, string $fieldKey, string $fieldValue, string $inputUrl)
    {
        $this->markTestSkipped('feature not implemented yet');
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedValue, $url->removeMultipleValue($fieldKey, $fieldValue));
    }

    /**
     * @dataProvider multipleValueAddDataProvider
     *
     * @param string $expectedValue
     * @param string $fieldKey
     * @param string $fieldValue
     * @param string $inputUrl
     */
    public function test_query_multiple_values_can_be_added(string $expectedValue, string $fieldKey, string $fieldValue, string $inputUrl)
    {
        $this->markTestSkipped('feature not implemented yet');
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedValue, $url->addMultipleValue($fieldKey, $fieldValue));
    }

    /**
     * @dataProvider multipleValueManipulateDataProvider
     *
     * @param string $expectedValue
     * @param string $fieldKey
     * @param string $oldValue
     * @param string $newValue
     * @param string $inputUrl
     */
    public function test_query_multiple_values_can_be_manipulated(string $expectedValue, string $fieldKey, string $oldValue, string $newValue, string $inputUrl)
    {
        $this->markTestSkipped('feature not implemented yet');
        $url = new Parser($inputUrl);
        $this->assertEquals($expectedValue, $url->manipulateMultipleValue($fieldKey, $oldValue, $newValue));
    }

    // endregion

    // region DataProviders
    public function urlParseDataProvider(): array
    {
        return [
            'simple url without query string' => [
                [
                    // query parameters
                    [],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => null,
                        UrlComponents::USERNAME     => null,
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => null,
                        UrlComponents::QUERY_STRING => null,
                        UrlComponents::FRAGMENT     => null,

                    ],

                    // stripped url
                    'https://www.domain.com',
                ],
                'https://www.domain.com',
            ],
            'simple url with query string' => [
                [
                    // query parameters
                    [
                        'foo'   => 'bar',
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => null,
                        UrlComponents::USERNAME     => null,
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => null,
                        UrlComponents::QUERY_STRING => 'foo=bar&bar=foo',
                        UrlComponents::FRAGMENT     => null,
                    ],

                    // stripped url
                    'https://www.domain.com',
                ],
                'https://www.domain.com?foo=bar&bar=foo',
            ],
            'simple url with array in query string' => [
                [
                    // query parameters
                    [
                        'foo'   => ['foo', 'bar'],
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => null,
                        UrlComponents::USERNAME     => null,
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => null,
                        UrlComponents::QUERY_STRING => 'foo[]=foo&foo[]=bar&bar=foo',
                        UrlComponents::FRAGMENT     => null,
                    ],

                    // stripped url
                    'https://www.domain.com',
                ],
                'https://www.domain.com?foo[]=foo&foo[]=bar&bar=foo',
            ],
            'url with path and query string' => [
                [
                    // query parameters
                    [
                        'foo'   => 'bar',
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => null,
                        UrlComponents::USERNAME     => null,
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => '/path/to/resource',
                        UrlComponents::QUERY_STRING => 'foo=bar&bar=foo',
                        UrlComponents::FRAGMENT     => null,

                    ],

                    // stripped url
                    'https://www.domain.com/path/to/resource',
                ],
                'https://www.domain.com/path/to/resource?foo=bar&bar=foo',
            ],
            'url with path, query string and fragment' => [
                [
                    // query parameters
                    [
                        'foo'   => 'bar',
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => null,
                        UrlComponents::USERNAME     => null,
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => '/path/to/resource',
                        UrlComponents::QUERY_STRING => 'foo=bar&bar=foo',
                        UrlComponents::FRAGMENT     => 'anchor',
                    ],

                    // stripped url
                    'https://www.domain.com/path/to/resource#anchor',
                ],
                'https://www.domain.com/path/to/resource?foo=bar&bar=foo#anchor',
            ],
            'url with credentials, but username only' => [
                [
                    // query parameters
                    [
                        'foo'   => 'bar',
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => '9999',
                        UrlComponents::USERNAME     => 'john_doe',
                        UrlComponents::PASSWORD     => null,
                        UrlComponents::PATH         => '/path/to/resource',
                        UrlComponents::QUERY_STRING => 'foo=bar&bar=foo',
                        UrlComponents::FRAGMENT     => 'anchor',
                    ],

                    // stripped url
                    'https://john_doe@www.domain.com:9999/path/to/resource#anchor',
                ],
                'https://john_doe@www.domain.com:9999/path/to/resource?foo=bar&bar=foo#anchor',
            ],
            'url with all components' => [
                [
                    // query parameters
                    [
                        'foo'   => 'bar',
                        'bar'   => 'foo',
                    ],

                    // url components
                    [
                        UrlComponents::SCHEME       => 'https',
                        UrlComponents::HOST         => 'www.domain.com',
                        UrlComponents::PORT         => '9999',
                        UrlComponents::USERNAME     => 'john_doe',
                        UrlComponents::PASSWORD     => 'secret',
                        UrlComponents::PATH         => '/path/to/resource',
                        UrlComponents::QUERY_STRING => 'foo=bar&bar=foo',
                        UrlComponents::FRAGMENT     => 'anchor',
                    ],

                    // stripped url
                    'https://john_doe:secret@www.domain.com:9999/path/to/resource#anchor',
                ],
                'https://john_doe:secret@www.domain.com:9999/path/to/resource?foo=bar&bar=foo#anchor',
            ],
        ];
    }

    public function parameterRemoveDataProvider(): array
    {
        return [
            'url with two parameters' => [
                'https://www.domain.com?bar=foo',
                'foo',
                'https://www.domain.com?foo=bar&bar=foo',
            ],
            'url with one parameters' => [
                'https://www.domain.com',
                'foo',
                'https://www.domain.com?foo=bar',
            ],
        ];
    }

    public function multipleValueRemoveDataProvider(): array
    {
        return [
            'url with array parameters' => [
                'https://www.domain.com?foo[]=bar',
                'foo',
                'foo',
                'https://www.domain.com?foo[]=foo&foo[]=bar',
            ],
        ];
    }

    public function multipleValueAddDataProvider(): array
    {
        return [
            'url with array parameters' => [
                'https://www.domain.com?foo[]=foo&foo[]=bar',
                'foo',
                'bar',
                'https://www.domain.com?foo[]=foo',
            ],
        ];
    }

    public function multipleValueManipulateDataProvider(): array
    {
        return [
            'url with array parameters' => [
                'https://www.domain.com?foo[]=foo',
                'foo',
                'bar',
                'foo',
                'https://www.domain.com?foo[]=bar',
            ],
        ];
    }

    public function parameterAddDataProvider(): array
    {
        return [
            'url with no parameters' => [
                'https://www.domain.com?foo=bar',
                'foo',
                'bar',
                'https://www.domain.com',
            ],
            'url with one parameters' => [
                'https://www.domain.com?foo=bar&bar=foo',
                'bar',
                'foo',
                'https://www.domain.com?foo=bar',
            ],
        ];
    }

    public function parameterManipulateDataProvider(): array
    {
        return [
            'url with two parameters' => [
                'https://www.domain.com?foo=foo&bar=foo',
                'foo',
                'foo',
                'https://www.domain.com?foo=bar&bar=foo',
            ],
            'url with one parameters' => [
                'https://www.domain.com?foo=foo',
                'foo',
                'foo',
                'https://www.domain.com?foo=bar',
            ],
        ];
    }

    // endregion
}
