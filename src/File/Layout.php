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

    public function section(string $sectionIdentifier, string $content = null) {
        if ($content !== null) {
            $this->sections[$sectionIdentifier] = $content;
            return;
        } elseif ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
        }

        $this->currentSectionIdentifier = $sectionIdentifier;
        ob_start();
    }

    public function render() {
        if ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
        }

        include $this->layoutPath;
        ob_end_flush();
    }

    private function harvest(string $sectionIdentifier) {
        return $this->sections[$sectionIdentifier];
    }
}