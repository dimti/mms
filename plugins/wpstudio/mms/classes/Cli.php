<?php

namespace Wpstudio\Mms\Classes;

use Collective\Remote\Connection;
use Wpstudio\Mms\Classes\Exceptions\MmsCliException;
use Wpstudio\Mms\Classes\Exceptions\MmsCliFileNotFoundException;
use Wpstudio\Mms\Classes\Helpers\SshHelper;

class Cli
{
    const CGROUPS_ROOT_UID = 10000;

    public Connection $sshConnection;

    public function __construct(Connection $sshConnection)
    {
        $this->sshConnection = $sshConnection;
    }

    public function run(string|array $commands): string
    {
        if (!is_array($commands)) {
            $commands = [$commands];
        }

        $commandsOutput = [];

        foreach ($commands as $command) {
            $this->sshConnection->run($command, function (string $output) use (&$commandsOutput) {
                $commandsOutput[] = trim($output);
            });

            if ($this->getExistCode() != 0) {
                throw new MmsCliException(sprintf(
                    'Command execute fails with exit code %d: %s',
                    $this->getExistCode(),
                    $command
                ));
            }
        }

        return implode(PHP_EOL, $commandsOutput);
    }

    public function getExistCode(): int
    {
        return $this->sshConnection->getGateway()->getConnection()->getExitStatus();
    }

    /**
     * @param string $filePath
     * @return void
     * @throws MmsCliFileNotFoundException
     */
    public function checkExistsFile(string $filePath): void
    {
        if (!$this->hasExistsFile($filePath)) {
            throw new MmsCliFileNotFoundException(sprintf(
                'File not exists: %s',
                $filePath
            ));
        }
    }

    /**
     * @param string $dirPath
     * @return void
     * @throws MmsCliFileNotFoundException
     */
    public function checkExistsDir(string $dirPath): void
    {
        if (!$this->hasExistsDir($dirPath)) {
            throw new MmsCliFileNotFoundException(sprintf(
                'Directory not exists: %s',
                $dirPath
            ));
        }
    }

    public function hasExistsFile(string $filePath): bool
    {
        return $this->sshConnection->exists($filePath) && $this->sshConnection->getGateway()->getConnection()->is_file($filePath);
    }

    public function hasExistsDir(string $dirPath): bool
    {
        return $this->sshConnection->exists($dirPath) && $this->sshConnection->getGateway()->getConnection()->is_dir($dirPath);
    }

    /**
     * @param string $filePath
     * @return string|false
     * @throws MmsCliFileNotFoundException
     */
    public function get(string $filePath): string|false
    {
        return $this->sshConnection->getString($filePath) ? : throw new MmsCliFileNotFoundException(sprintf(
            'Unable to get file content: %s',
            $filePath
        ));
    }

    public function put(string $filePath, string $content): void
    {
        $this->sshConnection->putString($filePath, $content);
    }

    /**
     * @param string $filePath
     * @param int $owner
     * @param int|null $group
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function chmodFile(string $filePath, int $owner, ?int $group = null): void
    {
        $this->checkExistsFile($filePath);

        if (is_null($group)) {
            $group = $owner;
        }

        $this->run(sprintf(
            'chown %d:%d %s',
            $owner,
            $group,
            $filePath
        ));
    }

    /**
     * @param string $dirPath
     * @param int $owner
     * @param int|null $group
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function chmodDir(string $dirPath, int $owner, ?int $group = null): void
    {
        $this->checkExistsDir($dirPath);

        if (is_null($group)) {
            $group = $owner;
        }

        $this->run(sprintf(
            'chown -R %d:%d %s',
            $owner,
            $group,
            $dirPath
        ));
    }

    /**
     * @param string $filePath
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function rmFile(string $filePath): void
    {
        $this->checkExistsFile($filePath);

        $this->run([
            sprintf(
                'rm %s',
                $filePath,
            ),
        ]);
    }

    /**
     * @param string $dirPath
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function rmDir(string $dirPath): void
    {
        $this->checkExistsDir($dirPath);

        if ($this->run(sprintf(
            'realpath %s',
            $dirPath
        )) == '/') {
            throw new MmsCliException(sprintf(
                'Unable to rootfs on the server to remove: %s',
                $dirPath
            ));
        }

        $this->run([
            sprintf(
                'rm %s/*',
                $dirPath,
            ),
            sprintf(
                'rm -d %s',
                $dirPath,
            ),
        ]);
    }

    /**
     * @param string $sourceFilePath
     * @param string $destinationFilePath
     * @param string $remoteIpAddress
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function copyFileToRemote(string $sourceFilePath, string $destinationFilePath, string $remoteIpAddress): void
    {
        $this->run([
            sprintf(
                'scp %s root@%s:%s',
                $sourceFilePath,
                $remoteIpAddress,
                $destinationFilePath
            ),
        ]);
    }

    /**
     * @param string $sourceDirPath
     * @param string $destinationDirPath
     * @param string $remoteIpAddress
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    public function copyDirToRemote(string $sourceDirPath, string $destinationDirPath, string $remoteIpAddress): void
    {
        $this->run([
            sprintf(
                'rsync -a %s/ root@%s:%s',
                $sourceDirPath,
                $remoteIpAddress,
                $destinationDirPath
            ),
        ]);
    }
}
