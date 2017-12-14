<?php

namespace pulledbits\View;


class TemplateInstanceTest extends \PHPUnit\Framework\TestCase
{
    private $templatePath;

    /**
     * @var Template
     */
    private $template;

    protected function setUp()
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'tt_') . '.php';

        $this->template = new File\Template($this->templatePath);
    }

    protected function tearDown()
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    public function testRender_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html>BlaBla</html>' . $variable);

        $object = $this->template->prepare([]);

        $this->expectOutputString('<html>BlaBla</html>' . $variable);
        $object->render();
    }
}
