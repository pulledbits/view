<?php
namespace pulledbits\View\File;


class Template implements \pulledbits\View\Template {
    private $templatePath;
    private $layoutsPath;
    private $cachePath;

    public function __construct(string $templatePath, string $layoutsPath, string $cachePath) {
        $this->templatePath = $templatePath;
        $this->layoutsPath = $layoutsPath;
        $this->cachePath = $cachePath;
    }

    private function sub(string $templateIdentifier): \pulledbits\View\Template  {
        return new self(dirname($this->templatePath) . DIRECTORY_SEPARATOR . $templateIdentifier . '.php', $this->layoutsPath, $this->cachePath);
    }

    private function url(string $path): string
    {
        if (array_key_exists('HTTPS', $_SERVER) === false) {
            $scheme = 'http';
        } else {
            $scheme = 'https';
        }

        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $path;
    }

    public function render(array $variables) {
        extract($variables);

        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . sha1_file($this->templatePath) . '.php';
        if (file_exists($cacheFile) === false) {
            $contents = file_get_contents($this->templatePath);
            file_put_contents($cacheFile, preg_replace('/<\?=\s?(.*?)\s?\?>/', '<?=htmlentities($1);?>', $contents));
        }
        include $cacheFile;
    }

    private function layout(string $layoutIdentifier) : \pulledbits\View\Layout
    {
        return new Layout($this->layoutsPath . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }
}