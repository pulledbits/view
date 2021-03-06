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
        return new self($layoutsDirectory . DIRECTORY_SEPARATOR . $layoutIdentifier . '.php');
    }

    public function compile() : void {
        if ($this->currentSectionIdentifier !== null) {
            ob_end_flush();
        }

        if ($this->extends === null) {
            include $this->layoutPath;
        } else {
            $layout = $this->extends;
            include $this->layoutPath;
            $this->extends->compile();
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
        }

        if ($this->currentSectionIdentifier !== null) {
            ob_end_flush();
        }
        $this->currentSectionIdentifier = $sectionIdentifier;
        ob_start(function(string $buffer) {
            $this->sections[$this->currentSectionIdentifier] = $buffer;
        });
    }

    /**
     * @param string $sectionIdentifier
     * @return mixed
     */
    private function harvest(string $sectionIdentifier) {
        return $this->sections[$sectionIdentifier];
    }

    private function layout(string $layoutIdentifier) : self {
        return self::load(dirname($this->layoutPath), $layoutIdentifier);
    }
}