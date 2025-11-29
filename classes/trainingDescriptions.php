<?php

class TrainingDescriptions {

    public static $data = [

        "Attack" => [
            "Electric" => "Penyaluran Voltase Puncak: Fokus Petir Terpusat",
            "Grass"    => "Badai Daun Silet: Latihan Ketajaman Klorofil",
            "Fire"     => "Semburan Inferno: Pernapasan Inti Magma",
            "Water"    => "Meriam Hidro: Kompresi Tekanan Air Absolut",
        ],

        "Defense" => [
            "Electric" => "Jubah Medan Magnet: Tolakan Statis",
            "Grass"    => "Meditasi Akar Tua: Pengerasan Kulit Kayu",
            "Fire"     => "Tameng Uap Panas: Evaporasi Serangan Air",
            "Water"    => "Zirah Cairan Non-Newtonian: Adaptasi Benturan",
        ],

        "Speed" => [
            "Electric" => "Transmisi Syaraf Kilat: Refleks Kecepatan Cahaya",
            "Grass"    => "Luncuran Fotosintesis: Manuver Hutan Rimba",
            "Fire"     => "Akselerasi Roket Pijar: Ledakan Langkah Pembakaran",
            "Water"    => "Aliran Arus Deras: Teknik Renang Aerodinamis",
        ],
    ];

    public static function getDesc($category, $type) {

        if (isset(self::$data[$category][$type])) {
            return self::$data[$category][$type];
        }

        return "Latihan umum tepi lapangan";
    }
}
