<?php

namespace Sympathy\Bootstrap;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class App
{
    protected $environment;
    protected $container;
    protected $rootDir;
    protected $debug;
    protected $name;
    protected $version = '1.0';

    public function __construct($rootDirectory = '', $environment = 'dev', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->rootDir = $rootDirectory;

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
        return realpath($this->rootDir . '/../var/log');
    }

    public function getConfigDir()
    {
        return $this->rootDir . '/config';
    }

    public function getCacheDir()
    {
        return realpath($this->rootDir . '/../var/cache');
    }

    public function getRootDir()
    {
        if ($this->rootDir == '') {
            $r = new \ReflectionObject($this);
            $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
        }

        return $this->rootDir;
    }

    public function getKernelParameters()
    {
        return array(
            'app.name' => $this->getName(),
            'app.version' => $this->getVersion(),
            'app.environment' => $this->environment,
            'app.debug' => $this->debug,
            'app.charset' => $this->getCharset(),
            'app.root_dir' => $this->getRootDir(),
            'app.cache_dir' => $this->getCacheDir(),
            'app.log_dir' => $this->getLogDir(),
            'app.config_dir' => $this->getConfigDir(),
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

    public function getApplication()
    {
        return $this->getContainer()->get('application');
    }

    public function run()
    {
        return $this->getApplication()->run();
    }
}