<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Bnn;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationService
{

    protected EmployeeMasterService $employeeMaster;


    public function __construct(
        EmployeeMasterService $employeeMaster
    ){

        $this->employeeMaster = $employeeMaster;

    }



    public function generateDaily()
    {

        $this->birthday();

        $this->bnn();

        $this->mcu();

    }




    /*
    |--------------------------------------------------------------------------
    | BIRTHDAY
    |--------------------------------------------------------------------------
    */

    private function birthday()
    {

        try {


            $snapshot =
            $this->employeeMaster->snapshot();



            $employees =
            $snapshot['employees'] ?? [];



            $birthday = [];



            foreach($employees as $employee){



                if(
                    empty($employee['tanggal_lahir'])
                ){

                    continue;

                }



                try {


                    $date =
                    Carbon::parse(
                        $employee['tanggal_lahir']
                    );



                    if(
                        $date->format('m-d')
                        ==
                        now()->format('m-d')
                    ){


                        $birthday[] =
                        $employee['nama'] ?? 'Karyawan';


                    }



                }
                catch(\Exception $e){

                    continue;

                }


            }




            if(empty($birthday)){

                return;

            }





            Notification::updateOrCreate(

                [

                    'type'=>'birthday',

                    'notification_date'=>today()

                ],


                [

                    'title'=>'🎂 Ulang Tahun Hari Ini',


                    'message'=>

                    count($birthday)
                    .
                    ' karyawan ulang tahun hari ini: '
                    .
                    implode(
                        ', ',
                        array_unique($birthday)
                    ),



                    'target_role'=>'all',

                    'reference_id'=>null,

                    'is_read'=>false


                ]

            );




        }
        catch(\Exception $e){


            logger(
                'Birthday Error : '
                .$e->getMessage()
            );


        }


    }







    /*
    |--------------------------------------------------------------------------
    | BNN
    |--------------------------------------------------------------------------
    */

    private function bnn()
    {


        $data =
        Bnn::whereDate(
            'tanggal_pemeriksaan',
            today()
        )
        ->get();



        foreach($data as $row){



            Notification::updateOrCreate(

                [

                    'type'=>'bnn',

                    'reference_id'=>$row->id,

                    'notification_date'=>today()

                ],


                [

                    'title'=>'🧪 Jadwal BNN',


                    'message'=>

                    $row->nama
                    .
                    ' ('
                    .
                    $row->nrp
                    .
                    ') jadwal BNN hari ini',



                    'target_role'=>'all',

                    'is_read'=>false

                ]

            );


        }


    }








    /*
    |--------------------------------------------------------------------------
    | MCU
    |--------------------------------------------------------------------------
    */


    private function mcu()
    {



        $rows =
        Cache::get(
            'mcu_fu.rows.fresh.v1',
            []
        );



        foreach($rows as $row){


            if(
                empty($row['tanggal_mcu'])
            ){

                continue;

            }




            try{


                $date =
                Carbon::parse(
                    $row['tanggal_mcu']
                );



                if(
                    !$date->isToday()
                ){

                    continue;

                }




                Notification::updateOrCreate(

                    [

                        'type'=>'mcu',

                        'reference_id'=>$row['nrp'] ?? null,

                        'notification_date'=>today()

                    ],



                    [

                        'title'=>'🏥 Jadwal MCU',


                        'message'=>

                        ($row['nama'] ?? 'Karyawan')
                        .
                        ' ('
                        .
                        ($row['nrp'] ?? '-')
                        .
                        ') jadwal MCU hari ini',



                        'target_role'=>'all',

                        'is_read'=>false


                    ]

                );



            }
            catch(\Exception $e){

                continue;

            }



        }



    }



}