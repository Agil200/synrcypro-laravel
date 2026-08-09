<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Services\NotificationService;


class NotificationController extends Controller
{


public function index(
    NotificationService $service
)
{


    $service->generateDaily();



    $role =
    auth()->user()?->role ?? 'all';



    $data =
    Notification::where(function($q) use($role){

        $q->where(
            'target_role',
            'all'
        )
        ->orWhere(
            'target_role',
            $role
        );


    })

    ->latest()

    ->limit(20)

    ->get();



    return response()->json([

        'success'=>true,

        'count'=>$data->count(),

        'data'=>$data

    ]);

}



public function unreadCount()
{


    $count =
    Notification::where(
        'is_read',
        false
    )
    ->count();



    return response()->json([

        'count'=>$count

    ]);

}



public function read($id)
{


    Notification::where(
        'id',
        $id
    )
    ->update([

        'is_read'=>true

    ]);



    return response()->json([

        'success'=>true

    ]);

}



}