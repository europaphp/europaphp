<?php

namespace Controller;
use Europa\Controller\RestController;
use Europa\Filter\CamelCaseSplitFilter;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Finder;
use Europa\Reflection\ClassReflector;

/**
 * Generates help information.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Help extends RestController
{
    /**
     * Shows the help.
     * 
     * @param string $command The command to show the help for. If not specified, the generic help is shown.
     * 
     * @return array
     */
    public function cli($command = null)
    {
        if ($command) {
            return $this->getCommand($command);
        }
        return $this->getAllCommands();
    }
    
    /**
     * Returns the help information for the specified command.
     *
     * @return array
     */
    private function getCommand($command)
    {
        $filter  = new ClassNameFilter;
        $class   = $filter->filter($command);
        $class   = __NAMESPACE__ . '\\' . $class;
        $class   = new ClassReflector($class);
        $block   = $class->getMethod('cli')->getDocBlock();
        $params  = [];
        $longest = 0;
        
        // gather parameter information
        if ($block->hasTag('param')) {
            foreach ($block->getTags('param') as $param) {
                $name    = $param->getName();
                $nameLen = strlen($name);
                
                if ($nameLen > $longest) {
                    $longest = $nameLen;
                }
                
                $params[$name] = [
                    'type'        => $param->getType(),
                    'description' => $param->getDescription()
                ];
            }
        }
        
        // sort by name
        ksort($params);
        
        // set padding for parameters
        foreach ($params as $name => $param) {
            $params[$name]['pad'] = $longest - strlen($name);
        }
        
        return [
            'command'     => $command,
            'description' => $block->getDescription(),
            'params'      => $params
        ];
    }
    
    /**
     * Returns all available commands in alphabetical order.
     * 
     * @return array
     */
    private function getAllCommands()
    {
        $filter = new CamelCaseSplitFilter;
        $finder = new Finder;
        $finder->in(__DIR__);
        $finder->is('/\.php$/');
        
        $classes = [];
        $longest = 0;
        
        // format class names from each file name and sort them
        foreach ($finder as $file) {
            $class = $file->getFilename();
            $class = str_replace(__DIR__, '', $file->getPath()) . '\\' . $class;
            $class = str_replace(DIRECTORY_SEPARATOR, '\\', $class);
            $class = trim($class, '\\');
            
            $classLen = strlen($class);
            if ($longest < $classLen) {
                $longest = $classLen;
            }
            
            $command = str_replace('\\', ' ', $class);
            $command = $filter->filter($command);
            $command = implode('-', $command);
            $command = str_replace(' -', ' ', $command);
            $command = strtolower($command);
            $command = trim($command, '-');
            
            $classes[$class] = $command;
            
            ksort($classes);
        }
        
        // format the commands array for the view
        foreach ($classes as $class => $command) {
            $refl = __NAMESPACE__ . '\\' . $class;
            $refl = new ClassReflector($refl);
            $name = $refl->getName();
            
            // only show cli methods
            if (!$refl->hasMethod('cli')) {
                continue;
            }
            
            $command            = str_pad($command, $longest, ' ', STR_PAD_LEFT);
            $commands[$command] = $refl->getMethod('cli')->getDocBlock()->getDescription();;
        }
        
        return [
            'commands' => $commands
        ];
    }
}