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
     * @var string
     */
    private $cachePath;

    /**
     * @var array
     */
    private $helpers;

    /**
     * Template constructor.
     * @param string $templatePath
     * @param string $layoutsPath
     * @param string $cachePath
     */
    public function __construct(string $templatePath, string $layoutsPath, string $cachePath)
    {
        $this->templatePath = $templatePath;
        $this->layoutsPath = $layoutsPath;
        $this->cachePath = $cachePath;
        $this->helpers = [];
    }

    /**
     * @param array $parameters
     * @return resource
     */
    public function capture(array $parameters)
    {
        $stream = fopen('php://memory', 'wb');
        ob_start(function(string $buffer) use ($stream) {
            fwrite($stream, $buffer);
        });
        $this->render($parameters);
        ob_end_clean();
        fseek($stream, 0);
        return $stream;
    }

    /**
     * @param string $templateIdentifier
     * @return \pulledbits\View\Template
     */
    private function sub(string $templateIdentifier): \pulledbits\View\Template
    {
        $template = new self(dirname($this->templatePath) . DIRECTORY_SEPARATOR . $templateIdentifier . '.php',
            $this->layoutsPath, $this->cachePath);
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
                return call_user_func_array($this->helpers[$identifier], $arguments);

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
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . sha1_file($this->templatePath) . '.php';
        if (file_exists($cacheFile) === false) {
            $contents = file_get_contents($this->templatePath);
            file_put_contents($cacheFile,
                preg_replace('/<\?=\s?(.*?)[;\s]*\?>/', '<?=$this->escape($1);?>', $contents));
        }

        $variables = [];
        foreach ($parameters as $parameterIdentifier => $parameter) {
            if (is_callable($parameter)) {
                $this->registerHelper($parameterIdentifier, $parameter);
            } else {
                $variables[$parameterIdentifier] = $parameter;
            }
        }
        extract($variables);
        include $cacheFile;
    }
}