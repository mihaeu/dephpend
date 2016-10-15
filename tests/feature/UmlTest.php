<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class UmlTest extends \PHPUnit_Framework_TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';

    public function testCreatesUml()
    {
        $tempFilePng = sys_get_temp_dir().'/dephpend-uml-test.png';
        $tempFileUml = sys_get_temp_dir().'/dephpend-uml-test.uml';
        shell_exec(self::DEPHPEND.' uml '.self::SRC.' --no-classes --keep-uml '
            .'--output="'.$tempFilePng.'" -f Mihaeu\\\\PhpDependencies\\\\Util');
        $this->assertEquals(
            '@startuml'.PHP_EOL
            .'Mihaeu.PhpDependencies.Util --|> Mihaeu.PhpDependencies.Analyser'.PHP_EOL
            .'Mihaeu.PhpDependencies.Util --|> Mihaeu.PhpDependencies.Dependencies'.PHP_EOL
            .'Mihaeu.PhpDependencies.Util --|> Mihaeu.PhpDependencies.OS'.PHP_EOL
            .'Mihaeu.PhpDependencies.Util --|> PhpParser'.PHP_EOL
            .'@enduml',
            file_get_contents($tempFileUml)
        );

        if (@unlink($tempFilePng) === false
            && @unlink($tempFileUml) === false) {
            $this->fail('Uml diagram or uml artifact was not created.');
        }
    }
}
