<?php

namespace Olidol\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sabre\DAV\Client;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class ConfigurationCommand extends Command
{
    protected $configType;
    protected $logger;

    protected $configFile;

    public function __construct($configType, LoggerInterface $logger = null)
    {
        $this->configType = $configType;
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * doConfigure
     */
    abstract protected function doConfigure();

    /**
     * doExecute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    abstract protected function doExecute(InputInterface $input, OutputInterface $output);

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->doConfigure();

        $this
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'In which folder config should live.', '.config')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $path = $input->getOption('path');

        if (!$fs->exists($path)) {
            $fs->mkdir($path);
            $this->logger->info('Configuration folder {folder} created.', [
                'folder' => $path
            ]);
        }

        $file = $path.'/'.$this->configType;

        if (!$fs->exists($file)) {
            $fs->touch($file);
            $this->logger->info('Configuration file {file} created.', [
                'file' => $file
            ]);
        }

        $this->configFile = $file;

        $this->doExecute($input, $output);
    }

    /**
     * loadConfig
     *
     * @return []
     */
    protected function loadConfig()
    {
        if ('' === $content = file_get_contents($this->configFile)) {
            return [];
        }

        return json_decode($content, true);
    }

    /**
     * saveConfig
     *
     * @param [] $config
     */
    protected function saveConfig(array $config)
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->configFile, json_encode($config));
    }
}
