<?php

/*
 * This file is part of Zippy.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Zippy\Adapter;

use Alchemy\Zippy\Exception\RuntimeException;
use Alchemy\Zippy\Exception\NotSupportedException;
/**
 * GNUTarAdapter allows you to create and extract files from archives using GNU tar
 *
 * @see http://www.gnu.org/software/tar/manual/tar.html
 */
class ZipAdapter extends AbstractBinaryAdapter
{
    /**
     * @inheritdoc
     */
    public function create($path, $files = null, $recursive = true)
    {
        $files = (array) $files;

        $builder = $this
            ->inflatorProcessBuilder
            ->create();

        if (0 === count($files)) {
           throw new NotSupportedException('Can not create empty zip archive');
        } else {

            if ($recursive) {
                $builder->add('-R');
            }

            $builder->add($path);

            if (!$this->addBuilderFileArgument($files, $builder)) {
                throw new InvalidArgumentException('Invalid files');
            }
        }

        $process = $builder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return new Archive($path, $this);
    }

    /**
     * @inheritdoc
     */
    public function isSupported()
    {
        $processDeflate = $this
            ->deflateProcessBuilder
            ->create()
            ->add('-h')
            ->getProcess();

        $processDeflate->run();

        $processInflate = $this
            ->inflateProcessBuilder
            ->create()
            ->add('-h')
            ->getProcess();

        $processInflate->run();

        return $processInflate->isSuccessful() && $processDeflate->isSuccessful();
    }

    /**
     * @inheritdoc
     */
    public function listMembers($path)
    {
        $process = $this
            ->deflatorProcessBuilder
            ->create()
            ->add('-l')
            ->add($path)
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $this->parser->parseFileListing($process->getOutput() ?: '');
    }

    /**
     * @inheritdoc
     */
    public function add($path, $files, $recursive = true)
    {
        $files = (array) $files;

        $builder = $this
            ->inflatorProcessBuilder
            ->create();

        if ($recursive) {
            $builder->add('-R');
        }

        $builder
            ->add('-u')
            ->add($path);

        if (!$this->addBuilderFileArgument($files, $builder)) {
            throw new InvalidArgumentException('Invalid files');
        }

        $process = $builder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function getDeflatorVersion()
    {
        $process = $this
            ->deflatorProcessBuilder
            ->create()
            ->add('-h')
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $this->parser->parseVersion($process->getOutput() ?: '');
    }

    /**
     * @inheritdoc
     */
    public function getInflatorVersion()
    {
        $process = $this
            ->inflatorProcessBuilder
            ->create()
            ->add('-h')
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $this->parser->parseVersion($process->getOutput() ?: '');
    }

    /**
     * @inheritdoc
     */
    public function remove($path, $files)
    {
         $files = (array) $files;

        $builder = $this
            ->inflatorProcessBuilder
            ->create();

        $builder
            ->add('-d')
            ->add($path);

        if (!$this->addBuilderFileArgument($files, $builder)) {
            throw new InvalidArgumentException('Invalid files');
        }

        $process = $builder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    public static function getName()
    {
        return 'zip';
    }
        /**
     * @inheritdoc
     */
    public static function getDefaultDeflatorBinaryName()
    {
        return 'zip';
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultInflatorBinaryName()
    {
        return 'unzip';
    }
}
