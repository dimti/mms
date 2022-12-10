<?php namespace Wpstudio\Mms\Classes\Enums\UpIdStatus;

enum TaskType: string
{
    case VzMigrate = 'vzmigrate';
    case VzStart = 'vzstart';
    case VzCreate = 'vzcreate';
    case AptUpdate = 'update';
}
