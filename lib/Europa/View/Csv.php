<?php

namespace Europa\View;
use Europa\String;
use Europa\View;

/**
 * A view class for rendering CSV data from bound parameters.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Csv extends View
{
    /**
     * Renders the set parameters as a CSV string.
     * 
     * @return string
     */
    public function render()
    {
        $this->sendHeaders();
        ob_start();
        $handle = fopen('php://output', 'w');
        foreach ($this->formatParamsToCsvArray() as $row) {
            $this->writeToHandle($handle, $row);
        }
        return ob_get_clean();
    }
    
    /**
     * Writes the array of data to the handle.
     * 
     * @param resource $handle The file handle.
     * @param array    $data   The data to write.
     * 
     * @return Csv
     */
    private function writeToHandle($handle, array $data)
    {
        fputcsv($handle, $data);
        return $this;
    }
    
    /**
     * Sends CSV headers if no headers have been output yet.
     * 
     * @return Csv
     */
    private function sendHeaders()
    {
        if (!headers_sent()) {
            header('Content-Type: Application\CSV');
        }
        return $this;
    }
    
    /**
     * Serializes the passed in parameters into an array.
     *  
     * @return array
     */
    private function formatParamsToCsvArray()
    {
        $array  = array();
        $first  = true;
        $params = $this->getParams();
        
        // add the headers
        $array[] = $this->getHeaders($params);
        
        // apply all rows
        foreach ($params as $name => $item) {
            $formatted = array();
            if (is_array($item) || is_object($item)) {
                foreach ($item as $data) {
                    $formatted[] = $this->convertToString($data);
                }
            }
            $array[] = $formatted;
        }
        return $array;
    }
    
    /**
     * Gets headers from a normal associative array and returns them as a normal indexed array.
     * 
     * @param array $data The data to get the headers from.
     * 
     * @return array
     */
    private function getHeaders(array $data)
    {
        $headers = array();
        if (isset($data[0]) && (is_array($data[0]) || is_object($data[0]))) {
            foreach ($data[0] as $header => $col) {
                $headers[] = $header;
            }
        }
        return $headers;
    }
    
    /**
     * Converts the incoming data to a string.
     * 
     * @param mixed $data The data to convert.
     * 
     * @return string
     */
    private function convertToString($data)
    {
        if (is_object($data)) {
            return $this->convertObjectToString($data);
        } elseif (is_array($data)) {
            return $this->convertArrayToString($data);
        }
        return String::create($data)->__toString();
    }
    
    /**
     * Converts the incoming object to a string.
     * 
     * @param object $data The data to convert.
     * 
     * @return string
     */
    private function convertObjectToString($data)
    {
        if ($data instanceof Traversible) {
            $array = iterator_to_array($data);
        } else {
            $array = array();
            foreach ($data as $item) {
                $array[] = $item;
            }
        }
        return $this->convertArrayToString($array);
    }
    
    /**
     * Converts the incoming array to a string.
     * 
     * @param array $data The data to convert.
     * 
     * @return string
     */
    private function convertArrayToString(array $data)
    {
        return implode(', ', $data);
    }
}