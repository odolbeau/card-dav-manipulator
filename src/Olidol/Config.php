<?php

namespace Olidol;

use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Config
{
    protected $logger;
    protected $path;

    /**
     * __construct.
     *
     * @param string          $path
     * @param LoggerInterface $logger
     */
    public function __construct($path, LoggerInterface $logger = null)
    {
        $this->path = $path;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * load.
     *
     * @param string $name
     *
     * @return []
     */
    public function load($name)
    {
        $fs = new Filesystem();

        $file = $this->path.'/'.$name;

        if (!$fs->exists($file)) {
            return [];
        }

        if ('' === $content = file_get_contents($this->path.'/'.$name)) {
            return [];
        }

        return json_decode($content, true);
    }

    /**
     * save.
     *
     * @param string $name
     * @param []     $config
     */
    public function save($name, array $config)
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->path)) {
            $fs->mkdir($this->path);
            $this->logger->info('Configuration folder {folder} created.', [
                'folder' => $this->path,
            ]);
        }

        $file = $this->path.'/'.$name;

        if (!$fs->exists($file)) {
            $fs->touch($file);
            $this->logger->info('Configuration file {file} created.', [
                'file' => $file,
            ]);
        }

        $fs->dumpFile($file, json_encode($config));
    }
}
