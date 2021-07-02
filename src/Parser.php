<?php

namespace SalvoBee\UrlUtilities;

use InvalidArgumentException;

class Parser
{
    /**
     * @var string
     */
    private $original;

    /**
     * @var string|null
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $queryString;

    /**
     * @var string
     */
    private $fragment;

    public function __construct(string $url)
    {
        $this->original = $url;
        $this->parse();
    }

    /**
     * @return void
     */
    private function parse(): void
    {
        $parsed_url = parse_url($this->original);

        $this->scheme = $parsed_url[Dictionaries\UrlComponents::SCHEME] ?? null;
        $this->host = $parsed_url[Dictionaries\UrlComponents::HOST] ?? null;
        $this->port = $parsed_url[Dictionaries\UrlComponents::PORT] ?? null;
        $this->username = $parsed_url[Dictionaries\UrlComponents::USERNAME] ?? null;
        $this->password = $parsed_url[Dictionaries\UrlComponents::PASSWORD] ?? null;
        $this->path = $parsed_url[Dictionaries\UrlComponents::PATH] ?? null;
        $this->queryString = $parsed_url[Dictionaries\UrlComponents::QUERY_STRING] ?? null;
        $this->fragment = $parsed_url[Dictionaries\UrlComponents::FRAGMENT] ?? null;
    }

    /**
     * @return array
     */
    public function getQueryParameters(): array
    {
        $parameters = [];
        if (!empty($this->queryString)) {
            parse_str($this->queryString, $parameters);
        }

        return $parameters;
    }

    public function stripQueryString(): string
    {
        return
            $this->rebuildScheme()
            .$this->rebuildCredentials()
            .$this->rebuildHostnameWithPort()
            .$this->path
            .$this->rebuildFragment();
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    private function buildUrl(array $parameters): string
    {
        return urldecode($this->stripQueryString().'?'.http_build_query($parameters));
    }

    // region Parameter Manipulation
    public function manipulateParameter(string $parameterToManipulate, string $newValue): string
    {
        $parameters = $this->getQueryParameters();

        if (empty($parameters) || !array_key_exists($parameterToManipulate, $parameters)) {
            throw new InvalidArgumentException('query is empty or specified parameter does not exists');
        }

        $parameters[$parameterToManipulate] = $newValue;

        return $this->buildUrl($parameters);
    }

    public function addParameter(string $newParameter, string $newValue): string
    {
        $parameters = $this->getQueryParameters();

        if (array_key_exists($newParameter, $parameters)) {
            throw new InvalidArgumentException('specified parameter already exists, use manipulateParameter() instead');
        }

        $parameters[$newParameter] = $newValue;

        return $this->buildUrl($parameters);
    }

    public function removeParameter(string $parameterToRemove): string
    {
        $parameters = $this->getQueryParameters();

        if (empty($parameters) || !array_key_exists($parameterToRemove, $parameters)) {
            throw new InvalidArgumentException('query is empty or specified parameter does not exists');
        }

        unset($parameters[$parameterToRemove]);

        return count($parameters) > 0 ?
            $this->buildUrl($parameters)
            : $this->stripQueryString();
    }

    // endregion

    // region Array values manipulation
    public function removeMultipleValue($arrayKey, $arrayValue): string
    {
        $parameters = $this->getQueryParameters();

        if (empty($parameters)
            || (!array_key_exists($arrayKey, $parameters) && is_array($parameters[$arrayKey]))
            || !in_array($arrayValue, $parameters[$arrayKey])
        ) {
            throw new InvalidArgumentException('query is empty or specified parameter does not exists');
        }

        return $this->buildUrl($parameters);
    }

    public function manipulateMultipleValue($arrayKey, $oldValue, $newValue): string
    {
        $parameters = $this->getQueryParameters();

        if (empty($parameters)
            || (!array_key_exists($arrayKey, $parameters) && is_array($parameters[$arrayKey]))
            || !in_array($oldValue, $parameters[$arrayKey])
        ) {
            throw new InvalidArgumentException('query is empty or specified parameter does not exists');
        }

        $valueKey = array_search($oldValue, $parameters);
        unset($parameters[$arrayKey][$valueKey]);

        $parameters[$arrayKey] = $newValue;

        return $this->buildUrl($parameters);
    }

    public function addMultipleValue($arrayKey, $newValue): string
    {
        $parameters = $this->getQueryParameters();

        if (empty($parameters)
            || (!array_key_exists($arrayKey, $parameters) && is_array($parameters[$arrayKey]))
        ) {
            throw new InvalidArgumentException('query is empty or specified parameter does not exists');
        }

        $parameters[$arrayKey][] = $newValue;

        return $this->buildUrl($parameters);
    }

    // endregion

    // region Getters

    /**
     * @return string
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    /**
     * @return string
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    // endregion

    // region Rebuilder

    /**
     * @return string
     */
    private function rebuildScheme(): string
    {
        return $this->scheme.'://';
    }

    private function rebuildCredentials(): string
    {
        $postfix = ($this->username || $this->password) ? '@' : '';
        $separator = ($this->password) ? ':' : '';

        return "{$this->username}{$separator}{$this->password}{$postfix}";
    }

    private function rebuildHostnameWithPort()
    {
        $separator = ($this->port) ? ':' : '';

        return "{$this->host}{$separator}{$this->port}";
    }

    /**
     * @return string
     */
    private function rebuildFragment(): string
    {
        return !empty($this->fragment) ? '#'.$this->fragment : '';
    }

    // endregion
}
