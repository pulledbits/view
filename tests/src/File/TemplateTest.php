<?php
/**
 * User: hameijer
 * Date: 9-3-17
 * Time: 15:08
 */

namespace File;


use pulledbits\View\Directory;
use pulledbits\View\File\Template;


class TemplateTest extends \PHPUnit\Framework\TestCase
{
    private $templatePath;
    private $templateIdentifier;

    /**
     * @var Template
     */
    private $object;

    protected function setUp()
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'tt_') . '.php';
        $this->templateIdentifier = basename($this->templatePath, '.php');

        $this->object = new Template(new Directory(sys_get_temp_dir(), sys_get_temp_dir()), sys_get_temp_dir(), sys_get_temp_dir());
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

        $this->expectOutputString('<html>BlaBla</html>' . $variable);
        $this->object->render($this->templateIdentifier, []);
    }


    public function testCapture_When_SameTemplateDifferentVariables_Expect_DifferentOutput()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>' . $variable);

        $this->assertEquals('<html>barBlaBla</html>' . $variable, $this->object->capture($this->templateIdentifier, ['foo' => 'bar']));
        $this->assertEquals('<html>bar2BlaBla</html>' . $variable, $this->object->capture($this->templateIdentifier, ['foo' => 'bar2']));
    }


    public function testCapture_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html>BlaBla</html>' . $variable);

        $this->assertEquals('<html>BlaBla</html>' . $variable, $this->object->capture($this->templateIdentifier, []));
    }

    public function testCapture_When_NestedVoidHelpers_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?php $this->bar();?></html>');

        $this->assertEquals('<html>Bla</html>', $this->object->capture($this->templateIdentifier, [
            'foo' => function() : void { print 'Bla'; },
            'bar' => function() : void { $this->foo(); }
        ]));
    }



    public function testRender_When_TemplateUsingLayout_Expect_ContentsOutputted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath . '.php', '<html>BlaBla</html>');
        file_put_contents($this->templatePath, '<?php $layout = $this->layout(\'' . basename($layoutPath) . '\'); ?>');

        $this->expectOutputString('<html>BlaBla</html>');
        $this->object->render($this->templateIdentifier, []);

        unlink($layoutPath . '.php');
    }

    public function testRender_When_ExistingTemplateWithVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>');

        $this->expectOutputString('<html>barBlaBla</html>');
        $this->object->render($this->templateIdentifier, [
            'foo' => 'bar'
        ]);
    }


    public function testRender_When_ExistingTemplateWithHTMLUnsafeVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$this->escape($foo);?>BlaBla</html>');

        $this->expectOutputString('<html>&lt;bar&gt;BlaBla</html>');
        $this->object->render($this->templateIdentifier, [
            'foo' => '<bar>'
        ]);
    }

    public function test__call_When_NonExistingHelper_Expect_EmptyString()
    {
        $this->assertEquals('', $this->object->nonExistingHelper());
    }

    public function testRender_When_HelperRegistered_Expect_ContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path): string {
            return 'https://example.com/<>' . $path;
        });

        $this->expectOutputString('<html>https://example.com/&lt;&gt;/path/to/fileBlaBla</html>');
        $this->object->render($this->templateIdentifier, []);
    }

    public function testRender_When_HelperRegisteredWhichReturnsNULLAndOutputsDirectly_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) : void {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render($this->templateIdentifier, []);
    }

    public function testRender_When_HelperRegisteredNoReturnType_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render($this->templateIdentifier, []);
    }

    public function testRender_When_HelperRegisteredOtherReturnType_Expect_EmptyString()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) : int {
            print 'https://example.com' . $path;
            return 0;
        });

        $this->expectOutputString('<html>BlaBla</html>');
        $this->object->render($this->templateIdentifier, []);
    }

    public function testRender_When_HelperUsingOtherHelper_Expect_ContentsWithHelperHelper()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render($this->templateIdentifier, [
            'host' => function(): string {
                return 'example.com';
            }
        ]);
    }


    public function testRender_When_HelperUsingOtherPrivateHelper_Expect_ContentsWithHelperHelper()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render($this->templateIdentifier, [
            'host' => function(): string {
                return 'example.com';
            }
        ]);
    }
}
