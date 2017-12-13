<?php
/**
 * User: hameijer
 * Date: 9-3-17
 * Time: 15:08
 */

namespace File;


use pulledbits\View\Directory;
use pulledbits\View\File\Template;
use pulledbits\View\Layout;


class TemplateTest extends \PHPUnit\Framework\TestCase
{
    private $templatePath;
    private $templateIdentifier;

    /**
     * @var Template
     */
    private $object;

    private $layout;

    protected function setUp()
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'tt_') . '.php';

        $this->layout = new class implements Layout {

            private $sections = [];

            public $content = '';

            /**
             * @param string $sectionIdentifier
             * @param string|null $content
             * @return mixed
             */
            public function section(string $sectionIdentifier, string $content = null)
            {

            }
        };

        $this->object = new Template($this->layout, $this->templatePath);
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
        $layout = new class implements Layout {

            private $sections = [];

            public $content = '<html>BlaBlaLayout</html>';

            public function __construct()
            {
                ob_start();
            }

            public function __destruct()
            {
                ob_end_flush();
                print $this->content;
            }

            /**
             * @param string $sectionIdentifier
             * @param string|null $content
             * @return mixed
             */
            public function section(string $sectionIdentifier, string $content = null)
            {

            }
        };

        $object = new Template($layout, $this->templatePath);
        file_put_contents($this->templatePath, '<?php $layout = $this->layout(); ?>');

        $this->expectOutputString('<html>BlaBlaLayout</html>');
        $object->render([]);
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
