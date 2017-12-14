<?php
namespace pulledbits\View\File;

use pulledbits\View\TemplateInstance;

/**
 * Class Template
 * @package pulledbits\View\File
 */
class Template implements \pulledbits\View\Template
{
    /**
     * @var string
     */
    private $templatePath;

    /**
     * @var array
     */
    private $helpers;

    /**
     * Template constructor.
     * @param string $templatePath
     */
    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
        $this->helpers = [];
        $this->registerHelper('escape', function(string $unsafestring) : string
        {
            return htmlentities($unsafestring);
        });
    }

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void
    {
        $this->helpers[$identifier] = \Closure::bind($callback, $this, __CLASS__);
    }

    public function prepare(array $parameters) : TemplateInstance {
        $instance = new TemplateInstance($this->templatePath, $parameters);
        foreach ($this->helpers as $helperIdentifier => $helper) {
            $instance->registerHelper($helperIdentifier, clone$helper);
        }
        return $instance;
    }
}