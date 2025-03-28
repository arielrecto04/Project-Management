<?php


namespace App\Enums;

enum TaskStatus: string
{
   case Pending = 'pending';
   case InProgress = 'in_progress';
   case Completed = 'completed';

   public static function values(): array
   {
       return [
           self::Pending->value,
           self::InProgress->value,
           self::Completed->value,
       ];
   }
}