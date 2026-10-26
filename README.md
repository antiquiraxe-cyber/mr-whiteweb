# MR. WHITE — Social Deduction Party Game

Sebuah game pesta deduksi sosial berbasis web yang terinspirasi dari game populer *Undercover* dan *Mr. White*. Dibangun secara *mobile-first* dengan gaya visual kartun retro (*neo-brutalism*) yang ramah dimainkan saat nongkrong lewat ponsel.

---

## 🎮 Gameplay & Aturan

Pemain dibagi menjadi 3 kemungkinan peran:
1. **Civilian (Warga):** Mendapatkan kata rahasia (contoh: *"Kopi"*).
2. **Undercover:** Mendapatkan kata yang mirip tapi berbeda (contoh: *"Teh"*).
3. **Mr. White:** Tidak mendapatkan kata sama sekali (*"Kamu Mr. White"*).

### Alur Putaran:
1. **Intip Peran:** Setiap pemain mengintip perannya secara rahasia.
2. **Berikan Petunjuk:** Pemain secara bergantian memberikan 1 kata/kalimat petunjuk mengenai kata mereka tanpa membongkar rahasianya.
3. **Diskusi & Voting:** Grup berdiskusi dan melakukan voting untuk mengeliminasi pemain yang paling dicurigai.
4. **Kesempatan Terakhir Mr. White:** Jika Mr. White tereliminasi, ia memiliki 1 kali kesempatan untuk menebak kata Civilian. Jika tebakannya tepat, Mr. White membalikkan keadaan dan langsung menang!

---

## ✨ Fitur Utama

- **Dual Game Mode:**
  - **Main 1 HP (Pass & Play):** Cukup 1 ponsel yang dioper bergiliran. Dilengkapi interaksi **"Tahan untuk Intip"** menggunakan *Pointer Capture API* (teks kata rahasia hanya muncul saat tombol ditekan, dan langsung tertutup instan begitu jari lepas agar aman dari intipan).
  - **Multiplayer (Multi-Device):** Host membuat room dan mendapatkan kode 4 huruf unik (misal: `ABCD`). Pemain lain bergabung via ponsel masing-masing menggunakan kode tersebut. Layar tersinkronisasi otomatis via *lightweight HTTP polling*.
- **Toggle Undercover (Custom Rule):**
  - Host dapat mengaktifkan atau menonaktifkan peran Undercover di lobby.
  - Sangat berguna untuk party kecil (3–4 pemain) agar pertarungan tetap seimbang (misal: 3 Civilian vs 1 Mr. White).
- **Feedback Eliminasi Real-Time:**
  - Menampilkan kartu notifikasi saat ada pemain yang di-eject (membongkar apakah korban adalah Civilian, Undercover, atau Mr. White).
- **Fast Replay Loop:**
  - Tombol *"Main Lagi"* mengacak ulang peran dan mengambil pasangan kata baru secara instan tanpa perlu mengetik ulang daftar nama pemain.
- **Bank Kata Terkurasi:**
  - 40 pasang kata bahasa Indonesia siap pakai yang dikelompokkan dalam berbagai kategori (makanan, barang sehari-hari, hewan, tempat, profesi).
- **Keamanan Sesi:**
  - Setiap pemain diikat dengan token UUID unik di session browser. Pemain luar tidak dapat mengintip peran pemain lain hanya dengan menebak URL.

---

## 🛠️ Tech Stack

- **Backend:** [Laravel](https://laravel.com) (PHP 8.3+)
- **Frontend:** Laravel Blade, [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev)
- **Database:** SQLite (default, zero-configuration)
- **Testing:** PHPUnit (11 Feature Tests mencakup skenario game end-to-end, otorisasi host, dan kondisi kemenangan)
- **Code Formatter:** Laravel Pint

---

## 🚀 Panduan Instalasi Lokal

Pastikan Anda telah menginstal **PHP 8.3+**, **Composer**, dan **Node.js 18+**.

```bash
# 1. Clone repository
git clone https://github.com/username/mr-white.git
cd mr-white

# 2. Install dependensi backend & frontend
composer install
npm install

# 3. Setup environment file
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi dan seeder database
touch database/database.sqlite
php artisan migrate --seed

# 5. Build asset frontend
npm run build

# 6. Jalankan local development server
php artisan serve
```

Buka browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Pengujian (Testing)

Proyek ini dilengkapi dengan suite automated test yang menguji integritas *state machine* game, distribusi peran, proteksi otorisasi, dan kondisi menang:

```bash
php artisan test
```

Semua 11 test / 49 assertions terverifikasi lulus.

---

## 📂 Struktur Penting

```
app/
├── Http/Controllers/
│   └── GameController.php   # Handle HTTP request, session token, dan view binding
├── Models/
│   ├── Player.php           # Model data pemain & status eliminasi
│   ├── Room.php             # Sesi game, kode unik, dan status state machine
│   └── WordPair.php         # Bank pasangan kata
└── Services/
    └── GameService.php      # Core logic game (distribusi role, eliminasi, evaluasi pemenang)

resources/views/
├── layouts/app.blade.php    # Base layout & font styling
├── home.blade.php           # Form create Pass & Play & form Room Multiplayer
├── lobby.blade.php          # Ruang tunggu & toggle pengaturan Undercover
├── reveal.blade.php         # Layar intip peran (Pass & Play)
├── play.blade.php           # Layar diskusi, voting, feed eliminasi & tebakan Mr. White
└── ended.blade.php          # Layar hasil permainan & fast replay loop
```
