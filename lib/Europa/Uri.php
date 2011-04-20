<?php

namespace Europa;

class Uri
{
    /**
     * The scheme set on the URI.
     * 
     * @var string
     */
    private $scheme;
    
    /**
     * The host set on the URI.
     * 
     * @var string
     */
    private $host;
    
    /**
     * The port set on the URI.
     * 
     * @var int
     */
    private $port;
    
    /**
     * The request set on the URI.
     * 
     * @var string
     */
    private $request;
    
    /**
     * The parameters set on the URI.
     * 
     * @var array
     */
    private $params = array();
    
    /**
     * A port map for default scheme ports.
     * 
     * @var array
     */
    private $portMap = array(
        'http'  => 80,
        'https' => 443
    );
    
    /**
     * Aliases toString for magic string conversion.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name  The name of the parameter to set.
     * @param mixed  $value The value of the parameter to set.
     * 
     * @return \Europa\Uri
     */
    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }
    
    /**
     * Returns the specified parameter if it exists. If not, null is returned.
     * 
     * @param string $name The name of the parameter to get.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }
    
    /**
     * Returns whether or not the specified parmaeter exists.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasParam($name);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * 
     * @return \Europa\Uri
     */
    public function __unset($name)
    {
        return $this->removeParam($name);
    }
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name  The name of the parameter to set.
     * @param mixed  $value The value of the parameter to set.
     * 
     * @return \Europa\Uri
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the specified parameter if it exists. If not, null is returned.
     * 
     * @param string $name The name of the parameter to get.
     * 
     * @return mixed
     */
    public function getParam($name)
    {
        if ($this->hasParam($name)) {
            return $this->params[$name];
        }
        return null;
    }
    
    /**
     * Returns whether or not the specified parmaeter exists.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return bool
     */
    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * 
     * @return \Europa\Uri
     */
    public function removeParam($name)
    {
        if ($this->hasParam($name)) {
            unset($this->params[$name]);
        }
        return $this;
    }
    
    /**
     * Bulk-sets parameters on the URI.
     * 
     * @param array $params The parameters to set.
     * 
     * @return \Europa\Uri
     */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * Returns all set query parameters as an array.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Removes all set parameters.
     * 
     * @return \Europa\Uri
     */
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    /**
     * Sets the scheme.
     * 
     * @param string $scheme The scheme to set.
     * 
     * @return \Europa\Uri
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
    
    /**
     * Returns the scheme portion of the URI.
     * 
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }
    
    /**
     * Returns the scheme part of the URI. If a scheme exists, then it is returned with followed by the colon and two
     * forward slashes.
     * 
     * @return string
     */
    public function getSchemePart()
    {
        if ($scheme = $this->getScheme()) {
            return $scheme . '://';
        }
        return null;
    }
    
    /**
     * Sets the specified host.
     * 
     * @param string $host The host to set.
     * 
     * @return \Europa\Uri
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    
    /**
     * Returns the host portion of the uri.
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * Returns the full host part of the URI. This includes the scheme, hostname and port. A host must exist for
     * anything to be returned at all.
     * 
     * @return string
     */
    public function getHostPart()
    {
        if ($host = $this->getHost()) {
            return $this->getSchemePart() . $host . $this->getPortPart();
        }
        return null;
    }
    
    /**
     * Normalizes and sets the specified port.
     * 
     * @param mixed $port The port to set.
     * 
     * @return \Europa\Uri
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }
    
    /**
     * Returns the port the current request came through.
     * 
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * Returns the port part of the URI. If the port exists and is not the default port for the scheme it is returned
     * with the loading colon. If it does not exist or is the default port for the scheme, then it is not returned.
     * In order to detect the default scheme port, however, a scheme must be set. Otherwise there is nothing to compare
     * the port against and it is returned no matter what if it is set.
     * 
     * @return string
     */
    public function getPortPart()
    {
        $port = $this->getPort();
        if ($port) {
            $scheme = $this->getScheme();
            if ($scheme && !isset($this->portMap[$scheme])) {
                return null;
            }
            if ($scheme && $this->portMap[$scheme] === $port) {
                return null;
            }
            return ':' . $port;
        }
        return null;
    }
    
    /**
     * Takes the specified request, normalizes it and then sets it.
     * 
     * @param string $request The request to set.
     * 
     * @return \Europa\Uri
     */
    public function setRequest($request)
    {
        $this->request = trim($request, '/');
        return $this;
    }

    /**
     * Returns the request portion of the URI.
     * 
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the request part of the URI. If a request part exists, it is returned with the leading forward slash. If
     * it does not exist, then null is returned.
     * 
     * @return string
     */
    public function getRequestPart()
    {
        if ($request = $this->getRequest()) {
            return '/' . $request;
        }
        return null;
    }
    
    /** 
     * Sets the query for the URI. The query string is parsed and parameters set.
     * 
     * @todo Handle arrays and objects.
     * 
     * @param string $query The query to set.
     * 
     * @return \Europa\Uri
     */
    public function setQuery($query)
    {
        $query = trim($query, '?');
        $query = explode('&', $query);
        foreach ($query as $part) {
            $parts = explode('=', $part, 2);
            $name  = urldecode($parts[0]);
            $value = isset($parts[1]) ? urldecode($parts[1]) : null;
            $this->setParam($name, $value);
        }
        return $this;
    }
    
    /**
     * Returns the query string in the current request.
     * 
     * @todo Handle array's and objects.
     * 
     * @return string
     */
    public function getQuery()
    {
        $queries = array();
        foreach ($this->params as $name => $value) {
            $queries[] = urlencode($name) . '=' . urlencode($value);
        }
        return $queries ? implode('&', $queries) : null;
    }
    
    /**
     * Returns the full query part of the URI. This normalizes the query so that if it exists, it is returned with a
     * leading question mark. If it does not exist, then null is returned.
     * 
     * @return string
     */
    public function getQueryPart()
    {
        if ($query = $this->getQuery()) {
            return '?' . $query;
        }
        return null;
    }
    
    /**
     * Returns the full URI that was used in the request.
     * 
     * @return string
     */
    public function toString()
    {
        return $this->getHostPart() . $this->getRequestPart() . $this->getQueryPart();
    }
    
    public static function getRootUri()
    {
        
    }
    
    public static function getRequestUri()
    {
        
    }
}