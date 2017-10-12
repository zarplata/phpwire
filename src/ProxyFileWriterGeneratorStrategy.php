<?php

namespace Zp\PHPWire;

use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use Zend\Code\Generator\ClassGenerator;

class ProxyFileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * @var \ProxyManager\FileLocator\FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @param \ProxyManager\FileLocator\FileLocatorInterface $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     *
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        $className     = trim($classGenerator->getNamespaceName(), '\\')
            . '\\' . trim($classGenerator->getName(), '\\');
        $generatedCode = $classGenerator->generate();
        $fileName      = $this->fileLocator->getProxyFileName($className);

        $this->writeFile("<?php\n\n" . $generatedCode, $fileName);
        return $generatedCode;
    }

    /**
     * Writes the source file in such a way that race conditions are avoided when the same file is written
     * multiple times in a short time period
     *
     * @param string $source
     * @param string $location
     *
     * @throws FileNotWritableException
     */
    private function writeFile(string $source, string $location) : void
    {
        $tmpFileName = tempnam($location, 'temporaryProxyManagerFile');

        file_put_contents($tmpFileName, $source);

        if (! rename($tmpFileName, $location)) {
            unlink($tmpFileName);

            throw FileNotWritableException::fromInvalidMoveOperation($tmpFileName, $location);
        }

        chmod($location, 0644);
    }
}
