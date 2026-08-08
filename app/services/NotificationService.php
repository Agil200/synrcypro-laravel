<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Bnn;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;


class NotificationService
{

    protected GoogleSheetsService $googleSheets;


    public function __construct(
        GoogleSheetsService $googleSheets
    ){
        $this->googleSheets = $googleSheets;
    }



    public function generateDaily()
    {

        $this->birthday();

        $this->bnn();

        $this->mcu();

    }



    /*
    |--------------------------------------------------------------------------
    | BIRTHDAY FROM GOOGLE SHEET DATABASE
    |--------------------------------------------------------------------------
    */

    private function birthday()
    {

        try {


            $rows =
            $this->googleSheets
            ->getMasterDatabaseValues();



            if(empty($rows)){
                return;
            }



            $birthdayList = [];



            foreach($rows as $index=>$row){


                // skip header
                if($index == 0){
                    continue;
                }



                /*
                GOOGLE SHEET DATABASE

                A = NO
                B = NRP
                C = NAMA
                D = JABATAN
                E = KODE JABATAN
                F = KONTAK
                G = TANGGAL LAHIR

                ARRAY:
                0 = NO
                1 = NRP
                2 = NAMA
                3 = JABATAN
                4 = KODE
                5 = KONTAK
                6 = TANGGAL LAHIR
                */



                if(empty($row[6])){
                    continue;
                }



                try{


                    $tanggal =
                    Carbon::parse(
                        $row[6]
                    );



                    if(
                        $tanggal->format('m-d')
                        ==
                        now()->format('m-d')
                    ){


                        $birthdayList[] = [

                            'nrp'=>$row[1] ?? '-',

                            'nama'=>$row[2] ?? 'Karyawan'

                        ];


                    }


                }
                catch(\Exception $e){

                    continue;

                }



            }



            if(count($birthdayList)==0){

                return;

            }



            $nama =
            collect($birthdayList)
            ->pluck('nama')
            ->unique()
            ->implode(', ');



            Notification::updateOrCreate(

                [

                    'type'=>'birthday',

                    'notification_date'=>today()

                ],


                [

                    'title'=>'🎂 Ulang Tahun Hari Ini',


                    'message'=>

                    count($birthdayList)
                    .
                    ' karyawan ulang tahun hari ini: '
                    .
                    $nama,


                    'target_role'=>'all',


                    'reference_id'=>null,


                    'is_read'=>false

                ]

            );


        }
        catch(\Exception $e){


            logger(
                'Birthday Error : '
                .
                $e->getMessage()
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



        if(empty($rows)){

            return;

        }



        foreach($rows as $row){



            if(empty($row['tanggal_mcu'])){

                continue;

            }



            try{


                $tanggal =
                Carbon::parse(
                    $row['tanggal_mcu']
                );



                if(!$tanggal->isToday()){

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