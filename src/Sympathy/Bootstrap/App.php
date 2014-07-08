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
    protected $appPath;
    protected $debug;
    protected $name;
    protected $version = '1.0';

    public function __construct($environment = 'dev', $appPath = '', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->appPath = $appPath;

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
            $this->name = ucfirst(preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->getAppPath())));
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

    public function getLogPath()
    {
        return realpath($this->appPath . '/../var/log');
    }

    public function getConfigPath()
    {
        return $this->appPath . '/config';
    }

    public function getCachePath()
    {
        return realpath($this->appPath . '/../var/cache');
    }

    public function getAppPath()
    {
        if ($this->appPath == '') {
            $r = new \ReflectionObject($this);
            $this->appPath = str_replace('\\', '/', dirname($r->getFileName()));
        }

        return $this->appPath;
    }

    public function getKernelParameters()
    {
        return array(
            'app.name' => $this->getName(),
            'app.version' => $this->getVersion(),
            'app.environment' => $this->environment,
            'app.debug' => $this->debug,
            'app.charset' => $this->getCharset(),
            'app.path' => $this->getAppPath(),
            'app.cache_path' => $this->getCachePath(),
            'app.log_path' => $this->getLogPath(),
            'app.config_path' => $this->getConfigPath(),
        );
    }

    public function loadContainerConfiguration()
    {
        $configPath = $this->getConfigPath();
        $environment=  $this->getEnvironment();

        $loader = new YamlFileLoader($this->container, new FileLocator($configPath));

        if (file_exists($configPath . '/' . $environment . '.yml')) {
            $loader->load($environment . '.yml');
        }

        if (file_exists($configPath . '/' . $environment . '.local.yml')) {
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