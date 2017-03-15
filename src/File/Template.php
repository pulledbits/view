<?php
namespace pulledbits\View\File;


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
     * @var string
     */
    private $layoutsPath;

    /**
     * @var array
     */
    private $helpers;

    /**
     * Template constructor.
     * @param string $templatePath
     * @param string $layoutsPath
     */
    public function __construct(string $templatePath, string $layoutsPath)
    {
        $this->templatePath = $templatePath;
        $this->layoutsPath = $layoutsPath;
        $this->helpers = [];
    }

    public function response(int $code, array $parameters) : \Psr\Http\Message\ResponseInterface {
        return (new \GuzzleHttp\Psr7\Response($code))->withBody(\GuzzleHttp\Psr7\stream_for($this->capture($parameters)));
    }

    /**
     * @param array $parameters
     * @return resource
     */
    public function capture(array $parameters)
    {
        ob_start();
        $this->render($parameters);
        return ob_get_clean();
    }

    /**
     * @param string $templateIdentifier
     * @return \pulledbits\View\Template
     */
    private function sub(string $templateIdentifier): \pulledbits\View\Template
    {
        $template = new self(dirname($this->templatePath) . DIRECTORY_SEPARATOR . $templateIdentifier . '.php', $this->layoutsPath);
        $template->helpers = $this->helpers;
        return $template;
    }

    /**
     * @param string $layoutIdentifier
     * @return \pulledbits\View\Layout
     */
    private function layout(string $layoutIdentifier): \pulledbits\View\Layout
    {
        return new Layout($this->layoutsPath . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }

    /**
     * @param string $unsafestring
     * @return string
     */
    private function escape(string $unsafestring)
    {
        return htmlentities($unsafestring);
    }

    /**
     * @param string $identifier
     * @param array $arguments
     * @return string
     */
    public function __call(string $identifier, array $arguments): string
    {
        if (array_key_exists($identifier, $this->helpers) === false) {
            return '';
        }

        $helperReflection = new \ReflectionFunction($this->helpers[$identifier]);
        if ($helperReflection->hasReturnType() === false) {
            call_user_func_array($this->helpers[$identifier], $arguments);
            return '';
        }

        switch ($helperReflection->getReturnType()) {
            case 'string':
                return $this->escape(call_user_func_array($this->helpers[$identifier], $arguments));

            case 'void':
                call_user_func_array($this->helpers[$identifier], $arguments);
                return '';

            default:
                ob_start();
                call_user_func_array($this->helpers[$identifier], $arguments);
                ob_end_clean();
                return '';
        }
    }

    /**
     * @param string $identifier
     * @param callable $callback
     */
    public function registerHelper(string $identifier, callable $callback) : void
    {
        $this->helpers[$identifier] = \Closure::bind($callback, $this, __CLASS__);
    }

    /**
     * @param array $parameters
     */
    public function render(array $parameters): void
    {
        $variables = [];
        foreach ($parameters as $parameterIdentifier => $parameter) {
            if (is_callable($parameter)) {
                $this->registerHelper($parameterIdentifier, $parameter);
            } else {
                $variables[$parameterIdentifier] = $parameter;
            }
        }
        extract($variables);
        include $this->templatePath;
    }
}