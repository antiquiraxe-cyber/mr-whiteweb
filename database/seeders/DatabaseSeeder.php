<?php

namespace Database\Seeders;

use App\Models\WordPair;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            ['Kopi', 'Teh'], ['Bakso', 'Mie Ayam'], ['Nasi Goreng', 'Mie Goreng'],
            ['Rendang', 'Gulai'], ['Martabak Manis', 'Roti Bakar'], ['Kerupuk', 'Keripik'],
            ['Sate', 'Kebab'], ['Burger', 'Sandwich'], ['Pizza', 'Roti Tawar'], ['Es Krim', 'Gelato'],
            ['Bantal', 'Guling'], ['Kipas Angin', 'AC'], ['Payung', 'Jas Hujan'],
            ['Kacamata', 'Softlens'], ['Earphone', 'Speaker'], ['Laptop', 'Komputer'],
            ['Sendok', 'Garpu'], ['Sabun', 'Shampo'], ['Dompet', 'Tas'], ['Jam Tangan', 'Jam Dinding'],
            ['Pasar', 'Supermarket'], ['Pantai', 'Gunung'], ['Bioskop', 'Teater'],
            ['Hotel', 'Villa'], ['Sekolah', 'Kampus'], ['Stasiun', 'Bandara'],
            ['Rumah Sakit', 'Puskesmas'], ['Kafe', 'Warkop'], ['Kucing', 'Anjing'],
            ['Singa', 'Harimau'], ['Bebek', 'Ayam'], ['Nyamuk', 'Lalat'],
            ['Hiu', 'Lumba-lumba'], ['Buaya', 'Komodo'], ['Kuda', 'Zebra'],
            ['Dokter', 'Perawat'], ['Polisi', 'Tentara'], ['Guru', 'Dosen'],
            ['Tidur', 'Rebahan'], ['Lari', 'Jalan Kaki'],
        ];

        foreach ($pairs as [$wordA, $wordB]) {
            WordPair::updateOrCreate(['word_a' => $wordA], ['word_b' => $wordB]);
        }
    }
}
