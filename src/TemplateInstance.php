<?php


namespace pulledbits\View;


use Psr\Http\Message\StreamInterface;

class TemplateInstance implements Renderable
{
    private $templatePath;

    private $variables;

    private $helpers;

    public function __construct(string $templatePath, array $parameters)
    {
        $this->templatePath = $templatePath;
        $this->variables = [];
        foreach ($parameters as $parameterIdentifier => $parameter) {
            if (is_callable($parameter)) {
                $this->registerHelper($parameterIdentifier, $parameter);
            } else {
                $this->variables[$parameterIdentifier] = $parameter;
            }
        }
    }

    public function registerHelper(string $identifier, callable $callback) : void
    {
        $this->helpers[$identifier] = \Closure::bind($callback, $this, __CLASS__);
    }

    public function __call(string $identifier, array $arguments)
    {
        if (array_key_exists($identifier, $this->helpers) === false) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $identifier, E_USER_ERROR);
        }

        $helperReflection = new \ReflectionFunction($this->helpers[$identifier]);
        $returnType = $helperReflection->getReturnType();
        if ($helperReflection->hasReturnType() === false) {
            $returnType = 'void';
        }

        switch ($returnType) {
            case 'string':
                return $this->helpers[$identifier](...$arguments);

            case 'void':
                $this->helpers[$identifier](...$arguments);
                return '';

            default:
                ob_start();
                $return = $this->helpers[$identifier](...$arguments);
                ob_end_clean();
                return $return;
        }
    }

    public function capture() : string {
        extract($this->variables);
        ob_start();
        include $this->templatePath;
        return ob_get_clean();
    }

    public function convertToStream(): StreamInterface
    {
        return \GuzzleHttp\Psr7\stream_for($this->capture());
    }
}