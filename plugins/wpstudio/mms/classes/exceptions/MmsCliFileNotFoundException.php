<?php namespace Wpstudio\Mms\Classes\Exceptions;

use Wpstudio\Mms\Models;

class MmsCliFileNotFoundException extends MmsCliException
{
    public function withServer(Models\Server $server): self
    {
        $this->message = rtrim($this->message, '. ') . '. ' . sprintf(
            'Server: %s',
            $server->code
        );

        return $this;
    }
}
