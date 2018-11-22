<?php
namespace pulledbits\View\File;

use Psr\Http\Message\ResponseInterface;
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
    private $path;

    /**
     * @var array
     */
    private $helpers;

    /**
     * @var array
     */
    private $variables;

    /**
     * Template constructor.
     * @param string $templatePath
     */
    public function __construct(string $templatePath, array $variables = [])
    {
        $this->path = $templatePath;
        $this->helpers = [];
        $this->variables = $variables;
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

    public function prepareAsResponse(ResponseInterface $response, array $parameters = []) : ResponseInterface {
        return $response->withBody($this->prepare($parameters)->convertToStream());
    }

    public function prepare(array $parameters = []) : TemplateInstance {
        $instance = new TemplateInstance($this->path, array_merge($this->variables, $parameters));
        foreach ($this->helpers as $helperIdentifier => $helper) {
            $instance->registerHelper($helperIdentifier, $helper);
        }
        return $instance;
    }
}