<?php

namespace App\Support;

class Geometri
{
    /**
     * Hitung convex hull dari sekumpulan titik 2D [x, y] menggunakan Monotone Chain (Andrew's algorithm).
     *
     * @param  array<int, array{0: float|int, 1: float|int}>  $titik
     * @return array<int, array{0: float, 1: float}> Titik hull berlawanan arah jarum jam (CCW) tanpa titik penutup
     */
    public static function convexHull(array $titik): array
    {
        // 1. Hapus duplikat titik
        $unik = [];
        foreach ($titik as $t) {
            $k = (string) $t[0].','.(string) $t[1];
            $unik[$k] = [(float) $t[0], (float) $t[1]];
        }
        $titik = array_values($unik);

        // Jika kurang dari 3 titik unik, kembalikan apa adanya
        if (count($titik) < 3) {
            return $titik;
        }

        // 2. Urutkan titik berdasarkan x (longitude), lalu y (latitude)
        usort($titik, function ($a, $b) {
            if ($a[0] == $b[0]) {
                return $a[1] <=> $b[1];
            }

            return $a[0] <=> $b[0];
        });

        // 3. Bangun lower hull
        $lower = [];
        foreach ($titik as $p) {
            while (count($lower) >= 2 && self::crossProduct($lower[count($lower) - 2], $lower[count($lower) - 1], $p) <= 1e-5) {
                array_pop($lower);
            }
            $lower[] = $p;
        }

        // 4. Bangun upper hull
        $upper = [];
        for ($i = count($titik) - 1; $i >= 0; $i--) {
            $p = $titik[$i];
            while (count($upper) >= 2 && self::crossProduct($upper[count($upper) - 2], $upper[count($upper) - 1], $p) <= 1e-5) {
                array_pop($upper);
            }
            $upper[] = $p;
        }

        // Hapus titik terakhir dari lower dan upper hull karena duplikat dengan titik awal hull lainnya
        array_pop($lower);
        array_pop($upper);

        // Gabungkan lower dan upper hull untuk mendapatkan urutan berlawanan arah jarum jam (CCW)
        return array_merge($lower, $upper);
    }

    /**
     * Perkalian silang (cross product) 2D dari vektor OA x OB.
     * Nilai > 0 menunjukkan belokan berlawanan arah jarum jam (CCW).
     * Nilai <= 0 menunjukkan belokan searah jarum jam (CW) atau segaris (collinear).
     *
     * @param  array{0: float, 1: float}  $o
     * @param  array{0: float, 1: float}  $a
     * @param  array{0: float, 1: float}  $b
     */
    private static function crossProduct(array $o, array $a, array $b): float
    {
        return ($a[0] - $o[0]) * ($b[1] - $o[1]) - ($a[1] - $o[1]) * ($b[0] - $o[0]);
    }
}
