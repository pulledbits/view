<?php

namespace pulledbits\View;


class DirectoryTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() {

    }

    public function testLoad_When_ExistingTemplateFile_Expect_ValidTemplate() {
        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        file_put_contents($templateFilename, '<?php print "TestTest"; ?>');
        $templateIdentifier = basename($templateFilename, '.php');

        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());

        $this->assertEquals("TestTest", $object->load($templateIdentifier)->capture([]));
    }

    public function testLoad_When_ExistingTemplateFile_Expect_SubTemplatesHelper() {
        $subTemplateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        file_put_contents($subTemplateFilename, '<?=$foo;?>');

        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        file_put_contents($templateFilename, '<?php $this->sub("' . basename($subTemplateFilename, '.php') . '", ["foo" => "bar"]); ?>');

        $templateIdentifier = basename($templateFilename, '.php');

        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());

        $this->assertEquals('bar', $object->load($templateIdentifier)->capture([]));
    }

    public function testLoad_When_HelpersRegisterd_Expect_TemplateWithHelpers() {
        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        file_put_contents($templateFilename, '<?php $this->test(); ?>');
        $templateIdentifier = basename($templateFilename, '.php');

        $helper = function() { print "FooBarTest"; };

        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('test', $helper);

        $expectedTemplate = new File\Template($templateFilename);
        $expectedTemplate->registerHelper('test', $helper);
        $this->assertEquals("FooBarTest", $object->load($templateIdentifier)->capture([]));
    }


}
