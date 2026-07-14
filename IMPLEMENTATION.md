# Ringkasan Implementasi

## Alur autentikasi

1. Route `/` menampilkan halaman login.
2. Google Login mengarah ke `/auth/google`, lalu callback ke `/auth/google/callback`.
3. Guest Login mengirim POST ke `/auth/guest`.
4. Pengguna terautentikasi diarahkan ke `/dashboard`.
5. Logout mengirim POST ke `/logout`, menghapus session, dan kembali ke login.

## Komponen dashboard

- KPI cards.
- Grafik operational output berbasis Canvas API.
- Safety compliance donut chart berbasis CSS.
- Status infrastruktur.
- Progress proyek.
- Recent incidents.
- Sidebar responsif dan waktu server Jakarta.

## Penyesuaian brand

Ubah nama perusahaan dan teks aplikasi pada:

- `resources/views/auth/login.blade.php`
- `resources/views/dashboard.blade.php`
- `.env` pada nilai `APP_NAME`
