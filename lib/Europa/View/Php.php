<?php

namespace Europa\View;
use Europa\Exception;
use Europa\ServiceLocator;
use Europa\View;

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden
 * to provide base functionality for view manipulation while the __toString
 * method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends View
{
    /**
     * The script path to look in.
     * 
     * @var array
     */
    private $paths;
    
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $script;
    
    /**
     * The service container used for helpers.
     * 
     * @var \Europa\ServiceLocator
     */
    private $serviceLocator;
    
    /**
     * Attempts to call the specified method on the specified locator if it exists.
     * 
     * @param string $name The specified service to locate and return.
     * @param array  $args The configuration for the service.
     * 
     * @return mixed
     */
    public function __call($name, array $args = array())
    {
        if (!$this->serviceLocator) {
            throw new Exception('Unable to create helper "' . $name . '" because no helper locator was set.');
        }
        
        try {
            array_unshift($args, $this);
            return $this->serviceLocator->create($name, $args);
        } catch (ServiceLocator\Exception $e) {
            throw new Exception('Unable to create instance of helper "' . $name . '".');
        }
    }
        
    /**
     * Attempts to retrieve a parameter by name. If the parameter is not found, then it attempts
     * to use the service locator to find a helper. If nothing is found, it returns null.
     * 
     * @param string $name The name of the property to get or helper to load.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (parent::hasParam($name)) {
            return parent::getParam($name);
        }
        
        // if no service locator is set, just return null
        if (!$this->serviceLocator) {
            return null;
        }
        
        try {
            return $this->serviceLocator->get($name, array($this));
        } catch (ServiceLocator\Exception $e) {
            
        }
        
        return null;
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @return string
     */
    public function render()
    {
        $script = $this->getScript();
        if (!$script) {
            throw new Exception('Could not render view: No script was defined to render.');
        }
        
        if (!$this->paths) {
            throw new Exception('Could not render view: There are no paths to load from.');
        }
        
        foreach ($this->paths as $path => $suffixes) {
            foreach ($suffixes as $suffix) {
                $file = $path . DIRECTORY_SEPARATOR . $this->getScript() . '.' . $suffix;
                if (file_exists($file)) {
                    ob_start();
                    include $file;
                    return ob_get_clean() . PHP_EOL;
                }
            }
        }
        throw new Exception("Could not locate the view {$this->getScript()}.");
    }

    /**
     * Sets the service locator to use for calling helpers.
     * 
     * @param \Europa\ServiceLocator $serviceLocator The service locator to use for helpers.
     * 
     * @return \Europa\View
     */
    public function setHelperLocator(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The path to the script to be rendered relative to the view path, excluding the extension.
     * 
     * @return View
     */
    public function setScript($script)
    {
        $this->script = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $script);
        return $this;
    }
    
    /**
     * Returns the set script.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }
    
    /**
     * Adds a path with the given suffix.
     * 
     * @param string $path     The path to add.
     * @param array  $suffixes The valid suffixes for the path.
     * 
     * @return Php
     */
    public function addPath($path, array $suffixes = array('php'))
    {
        // the path must be a valid path
        $realpath = realpath($path);
        if (!$realpath) {
            throw new Exception("Cannot add path: The path $realpath does not exist.");
        }
        
        // the path needs suffixes to use
        if (!$suffixes) {
            throw new Exception("Cannot add path: No valid suffixes were applied to the path.");
        }
        
        $this->paths[$realpath] = $suffixes;
        return $this;
    }
    
    /**
     * Return the current paths
     *
     * @return Array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}
