<?php
namespace pulledbits\View\File;


class Layout implements \pulledbits\View\Layout  {
    private $layoutPath;
    private $sections;
    private $currentSectionIdentifier;

    public function __construct(string $layoutPath)
    {
        $this->layoutPath = $layoutPath;
        $this->sections = [];
        ob_start();
    }

    public function __destruct()
    {
        if ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
        } else {
            ob_end_flush();
        }

        include $this->layoutPath;
    }

    public function section(string $sectionIdentifier, string $content = null) {
        if ($content !== null) {
            $this->sections[$sectionIdentifier] = htmlentities($content);
            return;
        } elseif ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
            ob_start();
        }

        $this->currentSectionIdentifier = $sectionIdentifier;
    }

    private function harvest(string $sectionIdentifier) {
        return $this->sections[$sectionIdentifier];
    }

    private function layout(string $layoutIdentifier) : Layout
    {
        return new self(dirname($this->layoutPath) . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }
}