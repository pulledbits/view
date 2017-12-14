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

        $this->assertEquals('<html>BlaBla</html>' . $variable, $object->capture());
    }


    public function testCapture_When_SameTemplateDifferentVariables_Expect_DifferentOutput()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>' . $variable);

        $object = $this->template->prepare(['foo' => 'bar']);
        $this->assertEquals('<html>barBlaBla</html>' . $variable, $object->capture());
        $object = $this->template->prepare(['foo' => 'bar2']);
        $this->assertEquals('<html>bar2BlaBla</html>' . $variable, $object->capture());
    }


    public function testCapture_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html>BlaBla</html>' . $variable);

        $object = $this->template->prepare([]);

        $this->assertEquals('<html>BlaBla</html>' . $variable, $object->capture());
    }

    public function testCapture_When_NestedVoidHelpers_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?php $this->bar();?></html>');

        $object = $this->template->prepare([
            'foo' => function() : void { print 'Bla'; },
            'bar' => function() : void { $this->foo(); }
        ]);

        $this->assertEquals('<html>Bla</html>', $object->capture());
    }


    public function testRender_When_TemplateUsingLayout_Expect_ContentsOutputted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?= $this->harvest("foobar"); ?>BlaBlaLayout</html>');

        $object = new File\Template($this->templatePath);
        $object->registerHelper('layout', function(string $layoutIdentifier) : Layout {
            return new \pulledbits\View\File\Layout(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $layoutIdentifier);
        });
        file_put_contents($this->templatePath, '<?php $layout = $this->layout("' . basename($layoutPath) . '"); $layout->section("foobar", $this->escape("Cöntent")); $layout->compile(); ?>');

        $this->assertEquals('<html>C&ouml;ntentBlaBlaLayout</html>', $object->prepare([])->capture());
    }

    public function testRender_When_ExistingTemplateWithVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>');

        $object = $this->template->prepare([
            'foo' => 'bar'
        ]);

        $this->assertEquals('<html>barBlaBla</html>', $object->capture());
    }


    public function testRender_When_ExistingTemplateWithHTMLUnsafeVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$this->escape($foo);?>BlaBla</html>');

        $object = $this->template->prepare([
            'foo' => '<bar>'
        ]);


        $this->assertEquals('<html>&lt;bar&gt;BlaBla</html>', $object->capture());
    }

    public function test__call_When_NonExistingHelper_Expect_Error()
    {
        $object = $this->template->prepare([]);

        $this->expectException('\\PHPUnit\\Framework\\Error\\Error');
        $this->expectExceptionMessage('Call to undefined method ' . get_class($object) . '::nonExistingHelper');
        $object->__call('nonExistingHelper', []);
    }

    public function testRender_When_HelperRegistered_Expect_ContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->escape($this->url(\'/path/to/file\'));?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path): string {
            return 'https://example.com/<>' . $path;
        });
        $object = $this->template->prepare([]);

        $this->assertEquals('<html>https://example.com/&lt;&gt;/path/to/fileBlaBla</html>', $object->capture());
    }

    public function testRender_When_HelperRegisteredWhichReturnsNULLAndOutputsDirectly_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path) : void {
            print 'https://example.com' . $path;
        });

        $object = $this->template->prepare([]);

        $this->assertEquals('<html>https://example.com/path/to/fileBlaBla</html>', $object->capture());
    }

    public function testRender_When_HelperRegisteredNoReturnType_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path) {
            print 'https://example.com' . $path;
        });
        $object = $this->template->prepare([]);

        $this->assertEquals('<html>https://example.com/path/to/fileBlaBla</html>', $object->capture());
    }

    public function testRender_When_HelperRegisteredOtherReturnType_Expect_ReturnedValue()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path) : int {
            print 'https://example.com' . $path;
            return 0;
        });
        $object = $this->template->prepare([]);

        $this->assertEquals('<html>0BlaBla</html>', $object->capture());
    }

    public function testRender_When_HelperUsingOtherHelper_Expect_ContentsWithHelperHelper()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });
        $object = $this->template->prepare([
            'host' => function(): string {
                return 'example.com';
            }
        ]);

        $this->assertEquals('<html>https://example.com/path/to/fileBlaBla</html>', $object->capture());
    }


    public function testRender_When_HelperUsingOtherPrivateHelper_Expect_ContentsWithHelperHelper()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->template->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });
        $object = $this->template->prepare([
            'host' => function(): string {
                return 'example.com';
            }
        ]);

        $this->assertEquals('<html>https://example.com/path/to/fileBlaBla</html>', $object->capture());
    }
}
