<?php

namespace TwigPlugin\Templating\Loader;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\FileLocator;

use \App as App;
use \Cache as Cache;

/**
 * Extends the default Twig filesystem loader
 * to work with CakePHP paths.
 */
class FilesystemLoader extends \Twig_Loader_Filesystem
{
    protected $cache = array();
    
    public function __construct(TemplateNameParserInterface $parser, FileLocatorInterface $locator)
    {
        parent::__construct(array());

        $this->parser = $parser;
        $this->locator = $locator;
    }

    public function findTemplate($template)
    {
        if (isset($this->cache[$template])) {
            return $this->cache[$template];
        }
        
        if (self::isAbsolutePath($template) && file_exists($template)) {
            return new FileStorage($template);
        }
        
        $file = null;
        $previous = null;
        try {
            $file = $this->parser->parse($template);
            try {
                $file = $this->locator->locate($file);
            } catch (\InvalidArgumentException $e) {
                $previous = $e;
            }
        } catch (\InvalidArgumentException $e) {
            $previous = $e;
        }

        return $file;
    }
    
    // protected function findTemplate($template)
    // {
    //     if (isset($this->cache[$template])) {
    //         return $this->cache[$template];
    //     }
    //     
    //     if (strstr($template, ':')) {
    //         $parts = explode(':', $template);
    //         if ($parts[0] === 'App') {
    //             $paths = App::path('View');
    //         } else {
    //             $paths = $this->View->getPaths($parts[0]);
    //         }
    //         
    //         // Get rid of the first part
    //         $parts = array_slice($parts, 1);
    //         
    //         $file = trim(implode(DS, $parts), DS);
    //         $file = preg_replace('#\/{2,}#i', DS, $file);
    // 
    //         foreach ($paths as $path) {
    //             $found = $path . 'Layouts' . DS . $file;
    //             if (file_exists($found)){
    //                 $this->_cache[$template] = $found;
    //                 
    //                 return $found;
    //             }
    //         }
    //     } else {
    //         foreach ($this->paths as $path) {
    //             if (is_file($path . DS . $template)) {
    //                 return $this->cache[$template] = $path . DS . $template;
    //             }
    //         }
    //     }
    //     
    //     return $template;
    // }
    
    /**
     * Returns true if the file is an existing absolute path.
     *
     * @param string $file A path
     *
     * @return true if the path exists and is absolute, false otherwise
     */
    static protected function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
        ) {
            return true;
        }

        return false;
    }
}