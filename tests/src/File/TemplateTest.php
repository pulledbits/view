<?php
/**
 * User: hameijer
 * Date: 9-3-17
 * Time: 15:08
 */

namespace File;

use pulledbits\View\File\Template;
use pulledbits\View\Layout;


class TemplateTest extends \PHPUnit\Framework\TestCase
{
    private $templatePath;

    /**
     * @var Template
     */
    private $object;

    protected function setUp()
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'tt_') . '.php';

        $this->object = new Template($this->templatePath);
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
        $this->object->render([]);
    }


    public function testCapture_When_SameTemplateDifferentVariables_Expect_DifferentOutput()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>' . $variable);

        $this->assertEquals('<html>barBlaBla</html>' . $variable, $this->object->capture(['foo' => 'bar']));
        $this->assertEquals('<html>bar2BlaBla</html>' . $variable, $this->object->capture(['foo' => 'bar2']));
    }


    public function testCapture_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $variable = microtime();
        file_put_contents($this->templatePath, '<html>BlaBla</html>' . $variable);

        $this->assertEquals('<html>BlaBla</html>' . $variable, $this->object->capture([]));
    }

    public function testCapture_When_NestedVoidHelpers_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?php $this->bar();?></html>');

        $this->assertEquals('<html>Bla</html>', $this->object->capture([
            'foo' => function() : void { print 'Bla'; },
            'bar' => function() : void { $this->foo(); }
        ]));
    }



    public function testRender_When_TemplateUsingLayout_Expect_ContentsOutputted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?= $this->harvest("foobar"); ?>BlaBlaLayout</html>');
        $layout = new \pulledbits\View\File\Layout($layoutPath);
        $object = new Template($this->templatePath);
        file_put_contents($this->templatePath, '<?php $layout->section("foobar", $this->escape("Cöntent")); ?>');
        $layout->record();
        $this->expectOutputString('<html>C&ouml;ntentBlaBlaLayout</html>');
        $object->render(['layout' => $layout]);
        $layout->play();
    }

    public function testRender_When_ExistingTemplateWithVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$foo?>BlaBla</html>');

        $this->expectOutputString('<html>barBlaBla</html>');
        $this->object->render([
            'foo' => 'bar'
        ]);
    }


    public function testRender_When_ExistingTemplateWithHTMLUnsafeVariables_Expect_ContentsOutputted()
    {
        file_put_contents($this->templatePath, '<html><?=$this->escape($foo);?>BlaBla</html>');

        $this->expectOutputString('<html>&lt;bar&gt;BlaBla</html>');
        $this->object->render([
            'foo' => '<bar>'
        ]);
    }

    public function test__call_When_NonExistingHelper_Expect_Error()
    {
        $this->expectException('\\PHPUnit\\Framework\\Error\\Error');
        $this->expectExceptionMessage('Call to undefined method ' . get_class($this->object) . '::nonExistingHelper');
        $this->object->__call('nonExistingHelper', []);
    }

    public function testRender_When_HelperRegistered_Expect_ContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path): string {
            return 'https://example.com/<>' . $path;
        });

        $this->expectOutputString('<html>https://example.com/&lt;&gt;/path/to/fileBlaBla</html>');
        $this->object->render([]);
    }

    public function testRender_When_HelperRegisteredWhichReturnsNULLAndOutputsDirectly_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) : void {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render([]);
    }

    public function testRender_When_HelperRegisteredNoReturnType_Expect_OBContentsWithHelperOutput()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) {
            print 'https://example.com' . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render([]);
    }

    public function testRender_When_HelperRegisteredOtherReturnType_Expect_EmptyString()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path) : int {
            print 'https://example.com' . $path;
            return 0;
        });

        $this->expectOutputString('<html>BlaBla</html>');
        $this->object->render([]);
    }

    public function testRender_When_HelperUsingOtherHelper_Expect_ContentsWithHelperHelper()
    {
        file_put_contents($this->templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');
        $this->object->registerHelper('url', function(string $path): string {
            return 'https://' . $this->host() . $path;
        });

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $this->object->render([
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
        $this->object->render([
            'host' => function(): string {
                return 'example.com';
            }
        ]);
    }
}
