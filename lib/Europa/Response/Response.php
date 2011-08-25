<?php

namespace Europa\Response;

/**
 * Counterpart to request object, outputs headers and contents
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Response implements \IteratorAggregate, ResponseInterface
{
    /**
     * The HTTP protocol version
     * 
     * @var string
     */
    const HTTP_VERSION = 'HTTP/1.1';
    
    /**
     * The JSON content type.
     * 
     * @var string
     */
    const CONTENT_TYPE_JSON = 'application/json';
    
    /**
     * The HTML content type.
     *
     * @var string
     */
    const CONTENT_TYPE_HTML = 'text/html';
    
    /**
     * The CSV content type.
     * 
     * @var string
     */
    const CONTENT_TYPE_CSV = 'text/csv';
    
    /**
     * The XML content type.
     *
     * @var string
     */
    const CONTENT_TYPE_XML = 'xml';
    
    /**
     * The content-type header name.
     * 
     * @var string
     */
    const HEADER_CONTENT_TYPE = 'content-type';
    
    /**
     * The headers.
     * 
     * @var array
     */
    private $headers = array();
    
    /**
     * The HTTP status.
     * 
     * @var array
     */
    private $codeMessages = array('200' => 'OK',
                                  '404' => 'Not Found',
                                  '403' => 'Forbidden',
                                  '301' => 'Moved Permanently',
                                  '500' => 'Internal Server Error',
                                  '401' => 'Unauthorized');
    
    /**
     * Sets the specified request header.
     *
     * @param string $name  The name of the header.
     * @param mixed  $value The value of the header.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->setHeader($name, $value);
    }

    /**
     * Returns the specified request header.
     *
     * @param string $name The name of the header.
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getHeader($name);
    }

    /**
     * Sets the content type header to the specified type
     * 
     * @param string $type One of json, http, csv, xml
     * 
     * @return true if $type exists, otherwise false
     * 
     */
    public function setContentType($type)
    {
        $this->setHeader(self::HEADER_CONTENT_TYPE, $type);
        return $this;
    }
    
    /**
     * Returns the content-type of the response.
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->getHeader(self::HEADER_CONTENT_TYPE);
    }
    
    /**
     * Sets the HTTP status
     * 
     * @param string $code
     * @param string $message 
     */
    public function setStatus($code, $message = null)
    {
        if (!$message) {
            $message = $this->codeMessages[$code];
        }
        $this->setHeader((string)$code, $message);
    }
    
    /**
     * Sets a header value
     * 
     * @param string $name  The header type (in camel cased format, will be converted in output)
     * @param string $value The header value
     * 
     * @return void
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
        
    /**
     * Returns a header
     * 
     * @param string $name The header to return
     * 
     * @return string 
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }
    
    /** 
     * Sets the specified headers. Accepts an array or object.
     * 
     * @param mixed $headers The headers to set.
     * 
     * @return Response
     */
    public function setHeaders($headers)
    {
        if (is_array($headers) || is_object($headers)) {
            foreach ($headers as $name => $value) {
                $this->setHeader($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Returns all of the set headers.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Given content generated from a view, output any headers, set any env vars and output the content.
     * 
     * @param string $content The content to output
     * 
     * @return void
     */
    public function output($content)
    {
        foreach ($this->headers as $name => $value) {
            // ensure camel-cased attr converted to headers, e.g. contentType => content-type
            $name = StringObject::create($name)->splitUcWords('-');
            if (array_key_exists($name, $this->codeMessages)) {
                header(self::HTTP_VERSION . ' ' .$name . ' ' . $value);
            } else {
                header($name . ': ' . $value);
            }
        }        
        echo $content;       
    }
    
    /**
     * Returns the iterator to use when iterating over the Reponse object.
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }
}
