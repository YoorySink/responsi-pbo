#  Pokemon Training Academy
![demo](https://github.com/YoorySink/fachrielYogaWicaksono_H1H024042_ResponsiPBO25/blob/main/Pok%C3%A9mon%20Training%20Academy%20-%20Home%20-%20Google%20Chrome%202025-11-29%2017-30-56.gif?raw=true)

Aplikasi web **Pokemon Training Academy** adalah sistem manajemen training Pokemon yang dibangun dengan **PHP native murni** tanpa framework. Aplikasi ini menerapkan konsep **Object-Oriented Programming (OOP)** dengan interface, abstract class, Polymorphism, dan inheritance untuk mengelola 4 jenis elemen Pokemon (Electric, Grass, Fire, Water).

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Struktur File](#-struktur-file)
- [Cara Menjalankan](#-cara-menjalankan)
- [Penjelasan Kode](#-penjelasan-kode)
  - [1. pokemon.php](#1-pokemonphp)
  - [2. basePokemon.php](#2-basepokemonphp)
  - [3. classPokemon.php](#3-classpokemonphp)
  - [4. elemenMoves.php](#4-elemenmovesphp)
  - [5. trainingDescriptions.php](#5-trainingdescriptionsphp)
  - [6. training.php](#6-trainingphp)
  - [7. history.json](#8-historyjson)

---

##  Fitur Utama

###  intinya
- **4 Jenis Pokemon**: Electric ⚡, Grass 🌿, Fire 🔥, Water 💧 tapi lebih fokus ke raichu ⚡
- **Level Progression System**: Pokemon naik level berdasarkan training
- **Energy Management**: Setiap training mengkonsumsi energy, bisa di-restore dengan Rest
- **Stats System**: HP, ATK, DEF, SPD yang bertambah setiap naik level
- **Unlock Moves**: Pokemon mendapatkan move baru setiap mencapai level tertentu
- **3 Kategori Training**:
  -  **Attack Training**: Meningkatkan ATK
  -  **Defense Training**: Meningkatkan DEF
  -  **Speed Training**: Meningkatkan SPD
- **Training Variations**: Setiap kategori memiliki 3 variasi training dengan bonus berbeda
- **Duration Options**: 10min, 20min, 30min, dengan konsumsi energy berbeda
- **History System**: Menyimpan semua aktivitas training ke file JSON

---

##  Struktur File

```
pokemon-training-academy/
│
├── index.php                    # Main application file (Controller + View)
├── pokemon.php                  # Interface Pokemon
├── basePokemon.php              # Abstract class BasePokemon
├── classPokemon.php             # Concrete Pokemon classes (Electric, Grass, Fire, Water)
├── elemenMoves.php              # Class ElementMoves untuk moves setiap elemen
├── trainingDescriptions.php     # Class TrainingDescriptions untuk deskripsi training
├── training.php                 # Class Training untuk logic training
├── history.json                 # JSON file untuk menyimpan history (generated)
└── README.md                    # Dokumentasi
```

---

##  Cara Menjalankan
#### **1. Pastikan Semua File Ada**
Pastikan file berikut ada di folder yang sama:
- `pokemon.php`
- `basePokemon.php`
- `classPokemon.php`
- `elemenMoves.php`
- `trainingDescriptions.php`
- `training.php`
- `index.php`
- `history.json`

atau pakai autoload.php saja

#### **2. Jalankan dengan PHP Built-in Server**
```bash
php -S localhost:8000
```

#### **3. Buka di Browser**
```
http://localhost:8000
```
---

##  Penjelasan Kode

### 1. `pokemon.php`

**Link**: [`pokemon.php`](./classes/pokemon.php)

**Deskripsi**: Interface yang mendefinisikan kontrak untuk semua Pokemon. Semua class Pokemon harus implement interface ini.

#### **Functions**

| Function | Parameter | Return Type | Deskripsi |
|----------|-----------|-------------|-----------|
| `getName()` | - | `string` | Mendapatkan nama Pokemon |
| `getType()` | - | `string` | Mendapatkan tipe elemen (Electric/Grass/Fire/Water) |
| `getLevel()` | - | `int` | Mendapatkan level Pokemon |
| `getHP()` | - | `int` | Mendapatkan HP Pokemon |
| `getEnergy()` | - | `int` | Mendapatkan energy Pokemon |
| `getAtk()` | - | `int` | Mendapatkan attack stat |
| `getDef()` | - | `int` | Mendapatkan defense stat |
| `getSpd()` | - | `int` | Mendapatkan speed stat |
| `getMoves()` | - | `array` | Mendapatkan array moves yang sudah di-unlock |
| `levelUp()` | - | `void` | Menaikkan level Pokemon |
| `rest()` | - | `void` | Restore energy Pokemon |
| `consumeEnergy()` | `int $amount` | `bool` | Mengurangi energy, return false jika tidak cukup |
| `addMove()` | `string $move` | `void` | Menambahkan move baru |
| `specialMove()` | - | `string` | Mendapatkan special move Pokemon |

#### **Konsep OOP**
- **Interface**: Mendefinisikan kontrak tanpa implementasi
- **Polymorphism**: Semua Pokemon punya method yang sama tapi implementasi berbeda

---

### 2. `basePokemon.php`

**Link**: [`basePokemon.php`](./classes/basePokemon.php)

**Deskripsi**: Abstract class yang mengimplementasikan interface `Pokemon`. Berisi implementasi default untuk semua method dan property Pokemon.

#### **Properties**

| Property | Type | Deskripsi |
|----------|------|-----------|
| `$name` | `string`  | Nama Pokemon |
| `$type` | `string`  | Tipe elemen Pokemon |
| `$level` | `int` | Level Pokemon (1-100) |
| `$hp` | `int` | Health Points |
| `$energy` | `int`  | Energy untuk training (0-100) |
| `$atk` | `int`  | Attack stat |
| `$def` | `int`  | Defense stat |
| `$spd` | `int`  | Speed stat |
| `$moves` | `array`  | Array moves yang sudah di-unlock |

#### **Methods**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `__construct()` | `string $name, string $type, int $level, int $hp, int $energy, int $atk, int $def, int $spd` | - | Constructor untuk inisialisasi Pokemon |
| `getName()` | - | `string` | Return nama Pokemon |
| `getType()` | - | `string` | Return tipe elemen |
| `getLevel()` | - | `int` | Return level Pokemon |
| `getHP()` | - | `int` | Return HP Pokemon |
| `getEnergy()` | - | `int` | Return energy Pokemon |
| `getAtk()` | - | `int` | Return attack stat |
| `getDef()` | - | `int` | Return defense stat |
| `getSpd()` | - | `int` | Return speed stat |
| `getMoves()` | - | `array` | Return array moves |
| `levelUp()` | - | `void` | Naikkan level +1, HP +100, ATK +10, DEF +10, SPD +5 |
| `rest()` | - | `void` | Restore energy +20 (max 100) |
| `consumeEnergy()` | `int $amount` | `bool` | Kurangi energy, return false jika tidak cukup |
| `addMove()` | `string $move` | `void` | Tambah move baru (jika belum ada) |
| `specialMove()` | - | `string` | Abstract method, harus di-override di child class |

#### **Konsep OOP**
- **Abstract Class**: Class yang tidak bisa di-instantiate langsung
- **Encapsulation**: Property `protected` hanya bisa diakses via getter/setter
- **Inheritance**: Semua Pokemon class akan extend class ini

---

### 3. `classPokemon.php`

**Link**: [`classPokemon.php`](./classes/classPokemon.php)

**Deskripsi**: File yang berisi 4  class Pokemon yang extend `BasePokemon`. Setiap class merepresentasikan satu elemen Pokemon.

#### **Class: ElectricPokemon ; raichu**
sebagai salah satu contoh
```php
class ElectricPokemon extends BasePokemon {
    public function __construct($name = "Raichu") {
        parent::__construct($name, "Electric", 1, 300, 100, 12, 8, 10);
        $this->moves[] = "Thunder Shock ⚡";
    }
    public function specialMove() { return "Thunder Shock ⚡"; }
}
```

| Aspect | Value |
|--------|-------|
| **Default Name** | Raichu |
| **Type** | Electric ⚡ |
| **Initial Stats** | HP: 300, Energy: 100, ATK: 12, DEF: 8, SPD: 10 |
| **Special Move** | Thunder Shock ⚡ |
| **Karakteristik** | ATK tinggi, balanced stats |



#### **Konsep OOP**
- **Inheritance**: Semua class extend `BasePokemon`
- **Constructor**: Memanggil `parent::__construct()` untuk inisialisasi
- **Method Override**: Override `specialMove()` dari abstract class
- **Default Parameters**: Constructor punya default name untuk setiap Pokemon

---

### 4. `elemenMoves.php`

**Link**: [`elemenMoves.php`](./classes/elemenMoves.php)

**Deskripsi**: Class yang menyimpan data moves untuk setiap elemen Pokemon berdasarkan level unlock.

#### **Class Structure**

```php
class ElementMoves {
    public static function getMoves($type) {
        // Return array moves berdasarkan type
    }
}
```

#### **Moves per Element**
```php
        "Electric" => [10 => "Spark ⚡", 20 => "Thunder Bolt ⚡⚡", 30 => "Volt Tackle ⚡💥"],
        "Grass"    => [10 => "Vine Whip 🌿", 20 => "Razor Leaf 🍃", 30 => "Seed Bomb 🌱💥"],
        "Fire"     => [10 => "Ember 🔥", 20 => "Fire Fang 🔥🐾", 30 => "Flamethrower 🔥💨"],
        "Water"    => [10 => "Water Gun 💧", 20 => "Water Pulse 🌊", 30 => "Hydro Pump 💦💥"],
```
#### **Method**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `getMoves()` | `string $type` | `array` | Return associative array moves dengan key = level unlock |


---

### 5. `trainingDescriptions.php`

**Link**: [`trainingDescriptions.php`](./classes/trainingDescriptions.php)

**Deskripsi**: Class yang menyimpan deskripsi training untuk setiap kategori dan tipe.

#### **Class Structure**

```php
class TrainingDescriptions {
    public static function get($category, $type) {
        // Return string deskripsi training
    }
}
```

#### **Training Descriptions**
```php
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
```

#### **Method**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `get()` | `string $category, string $type` | `string` | Return deskripsi training sesuai kategori dan type |

---

### 6. `training.php`

**Link**: [`training.php`](./classes/training.php)

**Deskripsi**: Class yang berisi logic untuk training Pokemon. Handle generation choices, processing training, dan perhitungan stat increase.

#### **Class Structure**

```php
class Training {
    public static function generateChoices($pokemonType, $category) { }
    public static function process($pokemon, $chosenType, $category, $duration) { }
}
```

#### **Methods**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `generateChoices()` | `string $pokemonType, string $category` | `array` | Generate 3 random choices training dengan tipe berbeda dan bonus random 1-3 |
| `process()` | `Pokemon $pokemon, string $chosenType, string $category, int $duration` | `array` | Process training: validasi energy, increase stats, cek level up, unlock moves |

##### **generateChoices()**
**Output Format**:
```php
[
    [
        'type' => 'Electric',
        'bonus' => 1/2/3,
        'description' => 'Penyaluran Voltase Puncak: Fokus Petir Terpusat'
    ],
    [
        'type' => 'Grass',
        'bonus' => 1/2/3,
        'description' => 'Badai Daun Silet: Latihan Ketajaman Klorofil'
    ],
    [
        'type' => 'Fire',
        'bonus' => 1/2/3,
        'description' => 'Semburan Inferno: Pernapasan Inti Magma'
    ]
]
```

**Rules**:
- Generate 3 pilihan dengan tipe berbeda
- Salah satu pilihan harus sesuai dengan tipe Pokemon (bonus x2)
- stat bertambah 1-3 untuk setiap pilihan sesuai durasi
- Jika tipe cocok dengan Pokemon, bonus dikali 2

##### **process()**

**Input**:
- `$pokemon`: Object Pokemon yang akan di-train
- `$chosenType`: Tipe training yang dipilih (Electric/Grass/Fire/Water)
- `$category`: Kategori training (Attack/Defense/Speed)
- `$duration`: Durasi training (10, 20, atau 30 menit)

**Output Format**:
```php
[
    'success' => true,
    'before' => [
        'level' => 1,
        'hp' => 300,
        'atk' => 12,
        'def' => 8,
        'spd' => 10,
        'energy' => 100,
        'moves' => ['Thunder Shock ⚡']
    ],
    'after' => [
        'level' => 2,
        'hp' => 400,
        'atk' => 22,
        'def' => 18,
        'spd' => 15,
        'energy' => 80,
        'moves' => ['Thunder Shock ⚡']
    ],
    'unlockedMoves' => []
]
```

**Training Calculation**:

| Aspect | Formula |
|--------|---------|
| **Energy Cost** | `duration` (10min = -10 energy, 20min = -20 energy) |
| **Base Increase** | `(duration / 10)` |
| **Same Type Bonus** | Bonus x2 jika tipe training = tipe Pokemon |
| **HP Increase** | `base * 10` |
| **Attack Increase** | `base + 20` (jika category = Attack) |
| **Defense Increase** | `base + 10` (jika category = Defense) |
| **Speed Increase** | `base + 5` (jika category = Speed) |
| **Level Up** | Otomatis cek apakah ada moves baru yang bisa di-unlock |

**Level Up Logic**:
1. Cek moves yang available untuk tipe Pokemon
2. Loop semua moves dengan level requirement ≤ current level
3. Jika move belum di-unlock, add ke array `unlockedMoves`
4. Call `$pokemon->addMove()` untuk setiap new move
5. Call `$pokemon->levelUp()` jika HP mencapai threshold level berikutnya

---



### 7. `history.json`

**Link**: [`history.json`](./history.json)

**Deskripsi**: File JSON untuk menyimpan history training dan rest. File ini di-generate otomatis oleh aplikasi.

#### **Structure**

```json
[
    {
        "time": "2025-11-29 08:42:49",
        "pokemon": "Raichu",
        "text": "Attack (Electric) 30min",
        "before": {
            "level": 27,
            "hp": 3150,
            "atk": 132,
            "def": 8,
            "spd": 10,
            "energy": 100,
            "moves": [
                "Thunder Shock ⚡",
                "Spark ⚡",
                "Thunder Bolt ⚡⚡"
            ]
        },
        "after": {
            "level": 31,
            "hp": 3600,
            "atk": 152,
            "def": 8,
            "spd": 10,
            "energy": 70,
            "moves": [
                "Thunder Shock ⚡",
                "Spark ⚡",
                "Thunder Bolt ⚡⚡",
                "Volt Tackle ⚡💥"
            ]
        },
        "unlocked": [
            "Volt Tackle ⚡💥"
        ]
    }
]
```

#### **Penggunaan**

- **Load**: `loadHistory()` di awal script
- **Save**: `saveHistory($history)` setiap selesai training/rest
- **Display**: 10 item terakhir ditampilkan di UI
- **Format**: Pretty print dengan `JSON_PRETTY_PRINT` untuk readable
- **Encoding**: `JSON_UNESCAPED_UNICODE` untuk support emoji

---

## Cara Bermain

### 1 **Pilih Pokemon**
Klik salah satu dari 4 Pokemon yang tersedia:
- **Raichu** (Electric) - High ATK
- **Bulbasaur** (Grass) - High DEF
- **Charmander** (Fire) - Balanced
- **Squirtle** (Water) - High DEF

### 2 **Pilih Training Category**
- 🎯 **Attack**: Meningkatkan ATK
- 🛡️ **Defense**: Meningkatkan DEF
- ⚡ **Speed**: Meningkatkan SPD

### 3 **Generate Choices**
Klik "🎲 Generate Choices" untuk mendapatkan 3 pilihan training random dengan bonus berbeda (1-3).

### 4 **Pilih Training Type**
Pilih salah satu dari 3 pilihan. **Tips**: Pilih type yang sama dengan Pokemon type untuk bonus 2x!

### 5 **Pilih Duration**
Pilih durasi training:
- **10min**: -10 energy, gain 1 level
- **20min**: -20 energy, gain 2 level
- **30min**: -30 energy, gain 3 level

### 6 **Start Training**
Klik "Start Training" untuk memulai. Pokemon akan:
- Mendapat increase HP, ATK/DEF/SPD (tergantung category)
- Energy berkurang
- Bisa naik level
- Bisa unlock moves baru

### 7 **Rest**
Jika energy rendah (<20), klik "😴 Rest" untuk restore energy +20.

### 8 **Unlock Moves**
Pokemon otomatis unlock moves baru setiap mencapai level tertentu:
- Level 10, 20, 30
![demo](https://github.com/YoorySink/fachrielYogaWicaksono_H1H024042_ResponsiPBO25/blob/main/Pok%C3%A9mon%20Training%20Academy%20-%20Home%20-%20Google%20Chrome%202025-11-29%2017-30-56.gif?raw=true)

---

##  konsep OOP yang ada

| Pattern | Implementasi |
|---------|--------------|
| **Interface** | `Pokemon` interface |
| **Abstract Class** | `BasePokemon` abstract class |
| **Inheritance** | Electric/Grass/Fire/WaterPokemon extend BasePokemon |
| **Encapsulation** | Properties `protected`, akses via getter |
| **Polymorphism** | `pokemon`, `classPokemon`, `basePokemon` |
---
