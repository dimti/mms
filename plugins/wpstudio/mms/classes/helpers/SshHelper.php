<?php namespace Wpstudio\Mms\Classes\Helpers;

use Collective\Remote\Connection;
use EloquentEncryption;
use Illuminate\Support\Facades\Config;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;
use SSH;

final class SshHelper
{
    private function __construct()
    {

    }

    public static function getConnection(string $ipAddress, string $password = null)
    {
        return SSH::connect([
            'host' => $ipAddress,
            'username' => 'root',
            'password' => $password,
            'key' => '',
            'keytext' => is_null($password) ? EloquentEncryption::getKey() : '',
            'keyphrase' => '',
            'agent' => '',
            'timeout' => 10,
        ]);
    }

    public static function getOutput(Connection $connection, string|array $command): string
    {
        $commandOutput = null;

        $connection->run($command, function (string $output) use (&$commandOutput) {
            $commandOutput = trim($output);
        });

        return $commandOutput;
    }

    public static function getPublicKey(): string
    {
        $rsaKeyHandler = app()->make(
            Config::get('eloquent_encryption.handler', RsaKeyStorageHandler::class)
        );

        assert($rsaKeyHandler instanceof RsaKeyStorageHandler);

        return $rsaKeyHandler->getPublicKey();
    }
}
