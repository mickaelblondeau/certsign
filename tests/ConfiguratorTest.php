<?php

namespace YllyCertSign\Tests;

use PHPUnit\Framework\TestCase;
use YllyCertSign\Configurator;
use YllyCertSign\Exception\NotFoundEnvironnementException;
use YllyCertSign\Factory\SignatorFactory;
use YllyCertSign\Signator;

class ConfiguratorTest extends TestCase
{
    /**
     * @throws NotFoundEnvironnementException
     */
    public function testConfigureFromArray()
    {
        $config = Configurator::loadFromFile(__DIR__ . '/config.yml');
        $signator = SignatorFactory::createFromArray($config);

        $this->assertTrue($signator instanceof Signator);
    }

    /**
     * @throws NotFoundEnvironnementException
     */
    public function testConfigureFromArrayWithProxy()
    {
        $config = Configurator::loadFromFile(__DIR__ . '/config.yml');
        $config['proxy'] = '127.0.0.1:8080';
        $signator = SignatorFactory::createFromArray($config);

        $this->assertTrue($signator instanceof Signator);
    }

    /**
     * @throws NotFoundEnvironnementException
     */
    public function testConfigureFromFile()
    {
        $signator = SignatorFactory::createFromYamlFile(__DIR__ . '/config.yml');

        $this->assertTrue($signator instanceof Signator);
    }
}
