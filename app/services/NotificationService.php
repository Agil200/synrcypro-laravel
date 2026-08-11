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

    /**
     * Membuat atau memperbarui notifikasi ulang tahun hari ini saja.
     */
    public function generateBirthday(): void
    {
        $this->birthday();
    }




    /*
    |--------------------------------------------------------------------------
    | BIRTHDAY
    |--------------------------------------------------------------------------
    */

    private function birthday(): void
    {
        try {
            $snapshot = $this->employeeMaster->snapshot();
            $employees = $snapshot['employees'] ?? [];
            $today = Carbon::now('Asia/Jakarta');
            $birthdayEmployees = [];

            foreach ($employees as $employee) {
                $birthDateValue = trim((string) (
                    $employee['tanggal_lahir'] ?? ''
                ));

                if ($birthDateValue === '' || $birthDateValue === '-') {
                    continue;
                }

                try {
                    $birthDate = Carbon::parse($birthDateValue);
                } catch (\Throwable) {
                    continue;
                }

                if ($birthDate->format('m-d') !== $today->format('m-d')) {
                    continue;
                }

                $name = trim((string) ($employee['nama'] ?? 'Karyawan'));
                $nrp = trim((string) ($employee['nrp'] ?? ''));
                $department = trim((string) (
                    $employee['departemen'] ?? ''
                ));
                $age = max(0, $today->year - $birthDate->year);

                $identity = array_filter([
                    $nrp !== '' ? 'NRP '.$nrp : null,
                    $department !== '' ? $department : null,
                    $age > 0 ? $age.' tahun' : null,
                ]);

                $birthdayEmployees[] = $name
                    .($identity !== []
                        ? ' ('.implode(' · ', $identity).')'
                        : '');
            }

            $birthdayEmployees = array_values(array_unique(
                $birthdayEmployees
            ));

            if ($birthdayEmployees === []) {
                return;
            }

            $startOfDayUtc = $today->copy()->startOfDay()->utc();
            $endOfDayUtc = $today->copy()->endOfDay()->utc();

            $notification = Notification::query()
                ->where('type', 'birthday')
                ->whereNull('reference_id')
                ->whereBetween(
                    'notification_date',
                    [$startOfDayUtc, $endOfDayUtc]
                )
                ->oldest()
                ->first();

            $isNewNotification = $notification === null;

            if ($isNewNotification) {
                $notification = new Notification();
                $notification->type = 'birthday';
                $notification->notification_date = $today
                    ->copy()
                    ->startOfDay();
            }

            $notification->title = '🎂 Ulang Tahun Hari Ini';
            $notification->message = count($birthdayEmployees)
                .' operator/karyawan ulang tahun hari ini: '
                .implode('; ', $birthdayEmployees);
            $notification->target_role = 'all';
            $notification->reference_id = null;

            // Notifikasi yang sudah dibaca tidak dibuat unread kembali
            // apabila generator dijalankan ulang pada hari yang sama.
            if ($isNewNotification) {
                $notification->is_read = false;
            }

            $notification->save();
        } catch (\Throwable $e) {
            logger('Birthday Error: '.$e->getMessage());
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