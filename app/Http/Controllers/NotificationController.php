<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;


class NotificationController extends Controller
{


    public function index(
        NotificationService $service
    )
    {


        // generate otomatis
        $service->generateDaily();



        $role =
        auth()->user()?->role
        ??
        'all';



        $data =
        Notification::where(
            function($q) use($role){


                $q->where(
                    'target_role',
                    'all'
                )
                ->orWhere(
                    'target_role',
                    $role
                );


            }
        )


        ->orderBy(
            'created_at',
            'desc'
        )


        ->limit(20)


        ->get();



        return response()->json([

            'success'=>true,

            'count'=>$data->count(),

            'data'=>$data

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