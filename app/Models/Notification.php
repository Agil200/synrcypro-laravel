<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Notification extends Model
{

protected $fillable = [

'title',
'message',
'type',
'target_role',
'reference_id',
'is_read',
'notification_date'

];


protected $casts=[

'is_read'=>'boolean',
'notification_date'=>'date'

];


}