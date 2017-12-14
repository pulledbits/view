<?php
namespace pulledbits\View\File;


/**
 * Class Layout
 * @package pulledbits\View\File
 */
class Layout implements \pulledbits\View\Layout  {

    private $extends;

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
    }

    public static function load(string $layoutsDirectory, string $layoutIdentifier) : self
    {
        $layout = new self($layoutsDirectory . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
        if (strpos($layoutIdentifier, '.') !== false) {
            $parentLayoutIdentifier = substr($layoutIdentifier, 0, strpos($layoutIdentifier, '.'));
            $layout->extends = self::load($layoutsDirectory, $parentLayoutIdentifier);
        }
        return $layout;
    }

    public function record() : void {
        ob_start();
    }
    public function play() : void {
        if ($this->currentSectionIdentifier !== null) {
            $this->sections[$this->currentSectionIdentifier] = ob_get_clean();
        } else {
            ob_end_flush();
        }

        if ($this->extends === null) {
            include $this->layoutPath;
        } else {
            $this->extends->record();
            $layout = $this->extends;
            include $this->layoutPath;
            $this->extends->play();
        }
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
}