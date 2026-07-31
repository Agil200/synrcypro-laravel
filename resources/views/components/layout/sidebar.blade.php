<aside class="manpower-sidebar">


    {{-- Toggle Sidebar --}}
    <div class="manpower-sidebar-header">

        <button
            type="button"
            id="sidebarToggle"
            class="manpower-sidebar-toggle"
        >
            ☰
        </button>

    </div>



    <nav class="manpower-navigation">



        {{-- Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            class="manpower-menu-link active"
        >

            <span class="manpower-menu-icon">

                <img
                    src="{{ asset('assets/images/DASHBOARD.png') }}"
                    alt="Dashboard"
                >

            </span>


            <span class="manpower-menu-label">
                Dashboard
            </span>


        </a>





        {{-- Menu Component --}}

        @php

        $menus = [

            [
                'title'=>'Mine Permit',
                'icon'=>'LOGO MANPOWER.png',
                'items'=>[
                    'Monitoring SHE',
                    'Monitoring Internal Upload',
                    'Monitoring Mine Permit'
                ]
            ],


            [
                'title'=>'Test BNN',
                'icon'=>'BNN.png',
                'items'=>[
                    'Daftar Test BNN',
                    'Monitoring Kehadiran'
                ]
            ],


            [
                'title'=>'Berita Acara Asset',
                'icon'=>'BAST.png',
                'items'=>[
                    'BAST Senter P101X',
                    'BAST Laser',
                    'BAST Laptop',
                    'BAST Radio HT',
                    'BAST Lainnya'
                ]
            ],


            [
                'title'=>'Monitoring APD',
                'icon'=>'APD.png',
                'items'=>[
                    'Pencarian',
                    'Input Pengajuan'
                ]
            ],


            [
                'title'=>'CC ST SP',
                'icon'=>'CC,ST,SP.png',
                'items'=>[
                    'Coaching Counselling',
                    'Surat Teguran',
                    'Surat Peringatan'
                ]
            ],


            [
                'title'=>'MCU & FU',
                'icon'=>'MCU DAN FU.png',
                'items'=>[
                    'Monitoring MCU & FU'
                ]
            ],


            [
                'title'=>'DOCUMENT OUT',
                'icon'=>'E-ARSIP.png',
                'items'=>[
                    'Monitoring Dokumen'
                ]
            ]

        ];

        @endphp





        @foreach($menus as $menu)


        <div class="manpower-menu-group">


            <button
                type="button"
                class="manpower-menu-toggle"
                aria-expanded="false"
            >


                <span class="manpower-menu-icon">

                    <img
                        src="{{ asset('assets/images/'.$menu['icon']) }}"
                        alt="{{ $menu['title'] }}"
                    >

                </span>



                <span class="manpower-menu-label">

                    {{ $menu['title'] }}

                </span>



                <span class="manpower-menu-arrow">

                    ›

                </span>


            </button>





            <div class="manpower-submenu">


                <div class="manpower-submenu-inner">


                    @foreach($menu['items'] as $item)


                    <a
                        href="#"
                        class="manpower-submenu-link"
                    >

                        {{ $item }}

                    </a>


                    @endforeach


                </div>


            </div>



        </div>



        @endforeach




    </nav>






    {{-- Bottom Sidebar --}}

    <div class="manpower-sidebar-bottom">


        <a
            href="#"
            class="manpower-bottom-link"
        >

            <span>
                ⚙
            </span>

            <span>
                Pengaturan
            </span>

        </a>




        <a
            href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email','mpe.ppaba@ppa.co.id')) }}"
            target="_blank"
            class="manpower-bottom-link help"
        >

            <span>
                ?
            </span>


            <span>
                Bantuan
            </span>


        </a>


    </div>




</aside>