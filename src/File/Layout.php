<?php
namespace pulledbits\View\File;


/**
 * Class Layout
 * @package pulledbits\View\File
 */
class Layout implements \pulledbits\View\Layout  {
    /**
     * @var string
     */
    private $layoutPath;
    /**
     * @var array
     */
    private $sections;
    /**
     * @var
     */
    private $currentSectionIdentifier;

    /**
     * Layout constructor.
     * @param string $layoutPath
     */
    public function __construct(string $layoutPath)
    {
        $this->layoutPath = $layoutPath;
        $this->sections = [];
        ob_start();
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
        } else {
            ob_end_flush();
        }

        include $this->layoutPath;
    }

    /**
     * @param string $sectionIdentifier
     * @param string|null $content
     */
    public function section(string $sectionIdentifier, string $content = null) {
        if ($content !== null) {
            $this->sections[$sectionIdentifier] = $content;
            return;
        } elseif ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
            ob_start();
        }

        $this->currentSectionIdentifier = $sectionIdentifier;
    }

    /**
     * @param string $sectionIdentifier
     * @return mixed
     */
    private function harvest(string $sectionIdentifier) {
        return $this->sections[$sectionIdentifier];
    }

    /**
     * @param string $layoutIdentifier
     * @return Layout
     */
    private function layout(string $layoutIdentifier) : Layout
    {
        return new self(dirname($this->layoutPath) . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }
}