<?php




namespace App\Enums;

enum BoardStagesDefault: string
{
    case TO_DO = 'To Do';
    case IN_PROGRESS = 'In Progress';
    case DONE = 'Done';
}
