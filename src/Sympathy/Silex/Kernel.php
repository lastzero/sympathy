<?php

namespace Sympathy\Silex;

use Silex\Application as SilexApplication;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class Kernel
{
    protected $environment;
    protected $container;
    protected $rootDir;
    protected $debug;
    protected $name;
    protected $version = '1.0';

    public function __construct($environment, $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;

        $this->boot();
    }

    protected function boot()
    {
        $this->container = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));

        $this->loadContainerConfiguration();
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer () {
        return $this->container;
    }

    public function getName()
    {
        if (null === $this->name) {
            $this->name = ucfirst(preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->getRootDir())));
        }

        return $this->name;
    }

    public function setName($appName)
    {
        $this->name = $appName;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($appVersion)
    {
        $this->version = $appVersion;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getCharset()
    {
        return 'UTF-8';
    }

    public function getLogDir()
    {
        return $this->rootDir . '/logs';
    }

    public function getConfigDir()
    {
        return $this->rootDir . '/config';
    }

    public function getCacheDir()
    {
        return $this->rootDir . '/cache';
    }

    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
        }

        return $this->rootDir;
    }

    public function getKernelParameters()
    {
        return array(
            'kernel.name' => $this->getName(),
            'kernel.version' => $this->getVersion(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.charset' => $this->getCharset(),
            'kernel.root_dir' => $this->getRootDir(),
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogDir(),
            'kernel.config_dir' => $this->getConfigDir(),
        );
    }

    public function loadContainerConfiguration()
    {
        $configDir = $this->getConfigDir();
        $environment=  $this->getEnvironment();

        $loader = new YamlFileLoader($this->container, new FileLocator($configDir));

        if (file_exists($configDir . '/' . $environment . '.yml')) {
            $loader->load($environment . '.yml');
        }

        if (file_exists($configDir . '/' . $environment . '.local.yml')) {
            $loader->load($environment . '.local.yml');
        }
    }

    public function run()
    {
        $this->getContainer()->get('application')->run();
    }
}