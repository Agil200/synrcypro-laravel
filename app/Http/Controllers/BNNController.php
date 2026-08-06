<?php

namespace App\Http\Controllers;


use App\Models\Bnn;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;



class BNNController extends Controller
{


    /**
     * Halaman Form BNN
     */
    public function index()
    {
        return view('manpower.bnn.form');
    }





    /**
     * Simpan Data BNN
     */
    public function store(Request $request)
    {


        $request->validate([

            'nrp'=>'required',

            'nama'=>'required',

            'tanggal_pemeriksaan'=>'required',

            'akomodasi'=>'required'

        ]);



        Bnn::create([


            'nrp'=>$request->nrp,

            'nama'=>$request->nama,

            'jenis_kelamin'=>$request->jenis_kelamin,

            'perusahaan'=>$request->perusahaan,

            'dept'=>$request->dept,

            'posisi'=>$request->posisi,

            'usia'=>$request->usia,

            'kontak'=>$request->kontak,

            'nik'=>$request->nik,

            'tanggal_pemeriksaan'=>$request->tanggal_pemeriksaan,

            'akomodasi'=>$request->akomodasi


        ]);




        return redirect()

            ->route('bnn.monitoring')

            ->with(
                'success',
                'Data BNN berhasil disimpan'
            );


    }








    /**
     * Dashboard BNN
     */
    public function dashboard(Request $request)
    {


        $bulan = $request->bulan ?? date('Y-m');



        $summary = [


            'total'=>Bnn::count(),



            'month'=>Bnn::whereYear(

                    'tanggal_pemeriksaan',

                    Carbon::parse($bulan)->year

                )

                ->whereMonth(

                    'tanggal_pemeriksaan',

                    Carbon::parse($bulan)->month

                )

                ->count(),




            'done'=>Bnn::count(),




            'pending'=>Employee::whereNotIn(

                    'nrp',

                    Bnn::pluck('nrp')

                )

                ->count()


        ];








        $akomodasi=[



            'mess'=>Bnn::where(

                'akomodasi',

                'DIANTAR DI MESS'

            )->count(),




            'sendiri'=>Bnn::where(

                'akomodasi',

                'BERANGKAT SENDIRI'

            )->count(),




            'bangko'=>Bnn::where(

                'akomodasi',

                'BANGKO'

            )->count()



        ];









        $trend=[];


        for($i=1;$i<=12;$i++){



            $trend[]=[


                'bulan'=>Carbon::create()

                    ->month($i)

                    ->translatedFormat('M'),




                'total'=>Bnn::whereMonth(

                        'tanggal_pemeriksaan',

                        $i

                    )

                    ->whereYear(

                        'tanggal_pemeriksaan',

                        date('Y')

                    )

                    ->count()


            ];

        }




        $maxTrend = collect($trend)->max('total');


        if(!$maxTrend){

            $maxTrend=1;

        }





        $recent=Bnn::latest()

            ->limit(10)

            ->get();





       return view(
    'manpower.bnn.dashboard',
    compact(
        'summary',
        'akomodasi',
        'trend',
        'maxTrend',
        'recent',
        'bulan'
    )
);


    }










    /**
     * Monitoring BNN
     */
    public function monitoring(Request $request)
    {


        $data=Bnn::query();



        if($request->nrp){


            $data->where(

                'nrp',

                $request->nrp

            );


        }



        if($request->tanggal){


            $data->whereDate(

                'tanggal_pemeriksaan',

                $request->tanggal

            );


        }




        $data=$data

            ->latest()

            ->paginate(25);




        return view(

            'bnn.monitoring',

            compact('data')

        );


    }










    /**
     * Auto Cari NRP
     */
    public function cariNRP($nrp)
    {


        $data=Employee::where(

            'nrp',

            $nrp

        )

        ->first();



        if(!$data){


            return response()->json([]);


        }




        return response()->json([


            'nrp'=>$data->nrp,

            'nama'=>$data->nama,

            'jenis_kelamin'=>$data->jenis_kelamin,

            'perusahaan'=>$data->perusahaan,

            'dept'=>$data->dept,

            'posisi'=>$data->posisi,

            'usia'=>$data->usia,

            'kontak'=>$data->kontak,

            'nik'=>$data->nik


        ]);


    }



}