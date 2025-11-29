#  Pokemon Training Academy

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
  - [7. index.php](#7-indexphp)
  - [8. history.json](#8-historyjson)

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
- **Duration Options**: 5min, 10min, 15min, 20min dengan konsumsi energy berbeda
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

| Property | Type | Access | Deskripsi |
|----------|------|--------|-----------|
| `$name` | `string` | `protected` | Nama Pokemon |
| `$type` | `string` | `protected` | Tipe elemen Pokemon |
| `$level` | `int` | `protected` | Level Pokemon (1-100) |
| `$hp` | `int` | `protected` | Health Points |
| `$energy` | `int` | `protected` | Energy untuk training (0-100) |
| `$atk` | `int` | `protected` | Attack stat |
| `$def` | `int` | `protected` | Defense stat |
| `$spd` | `int` | `protected` | Speed stat |
| `$moves` | `array` | `protected` | Array moves yang sudah di-unlock |

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

| Level | Electric ⚡ | Grass 🌿 | Fire 🔥 | Water 💧 |
|-------|------------|----------|---------|----------|
| 1 | Thunder Shock ⚡ | Tackle 🌿 | Ember Spark 🔥 | Bubble Shot 💧 |
| 5 | Thunder Wave ⚡ | Vine Whip 🌿 | Flame Burst 🔥 | Water Gun 💧 |
| 10 | Spark ⚡ | Razor Leaf 🌿 | Fire Fang 🔥 | Aqua Tail 💧 |
| 15 | Discharge ⚡ | Seed Bomb 🌿 | Flamethrower 🔥 | Hydro Pump 💧 |
| 20 | Thunderbolt ⚡ | Solar Beam 🌿 | Fire Blast 🔥 | Surf 💧 |
| 30 | Thunder ⚡ | Leaf Storm 🌿 | Inferno 🔥 | Water Pledge 💧 |

#### **Method**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `getMoves()` | `string $type` | `array` | Return associative array moves dengan key = level unlock |

#### **Konsep OOP**
- **Static Method**: Method bisa dipanggil tanpa instantiate object

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

##### **🎯 Attack Training**

| Type | Electric | Grass | Fire |
|------|----------|-------|------|
| **Electric** | Unleash powerful Thunder attacks on targets | Control lightning strikes with precision | Generate massive electric surges |
| **Grass** | Strike through dense vegetation efficiently | Execute rapid vine-based combos | Master grass-cutting techniques |
| **Fire** | Perfect your flame-punching techniques | Learn to control fire intensity | Execute devastating fire strikes |
| **Water** | Practice high-pressure water strikes | Master fluid combat movements | Perfect tsunami-level attacks |

##### **🛡️ Defense Training**

| Type | Electric | Grass | Fire |
|------|----------|-------|------|
| **Electric** | Build resistance to electric attacks | Create protective static barriers | Generate defensive electric shields |
| **Grass** | Strengthen your natural plant armor | Grow protective vines and barriers | Master forest fortification |
| **Fire** | Endure extreme heat conditions | Build heat-resistant stamina | Master fire-resistant techniques |
| **Water** | Practice underwater breathing | Build resistance to water pressure | Master aquatic defense |

##### **⚡ Speed Training**

| Type | Electric | Grass | Fire |
|------|----------|-------|------|
| **Electric** | Channel electricity for quick movements | Master lightning-fast reflexes | Perfect electric-speed techniques |
| **Grass** | Practice rapid plant growth techniques | Master swift nature movements | Execute lightning-fast vine strikes |
| **Fire** | Use fire propulsion for speed boosts | Master flame dash techniques | Perfect rapid fire movements |
| **Water** | Swim through turbulent waters | Master water-skating techniques | Perfect aqua jet movements |

#### **Method**

| Method | Parameter | Return Type | Deskripsi |
|--------|-----------|-------------|-----------|
| `get()` | `string $category, string $type` | `string` | Return deskripsi training sesuai kategori dan type |

#### **Konsep OOP**
- **Static Method**: Tidak perlu instantiate untuk mengakses data

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
- `$duration`: Durasi training (5, 10, 15, atau 20 menit)

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
| **Energy Cost** | `duration` (5min = -5 energy, 20min = -20 energy) |
| **Base Increase** | `(duration / 5) * bonus` |
| **Same Type Bonus** | Bonus x2 jika tipe training = tipe Pokemon |
| **HP Increase** | `base * 10` |
| **Attack Increase** | `base * 1` (jika category = Attack) |
| **Defense Increase** | `base * 1` (jika category = Defense) |
| **Speed Increase** | `base * 1` (jika category = Speed) |
| **Level Up** | Otomatis cek apakah ada moves baru yang bisa di-unlock |

**Level Up Logic**:
1. Cek moves yang available untuk tipe Pokemon
2. Loop semua moves dengan level requirement ≤ current level
3. Jika move belum di-unlock, add ke array `unlockedMoves`
4. Call `$pokemon->addMove()` untuk setiap new move
5. Call `$pokemon->levelUp()` jika HP mencapai threshold level berikutnya

---

### 7. `index.php`

**Link**: [`index.php`](./index.php)

**Deskripsi**: Main application file yang berisi html dan css.

#### **Architecture**

```
index.php
├── Includes (Lines 4-9)
├── Helper Functions (Lines 12-24)
├── Session Initialization (Lines 27-45)
├── POST Handler / Controller (Lines 47-152)
└── HTML View (Lines 155-end)
```

#### **1. Includes Section**

| Line | File | Deskripsi |
|------|------|-----------|
| 4 | `pokemon.php` | Interface Pokemon |
| 5 | `basePokemon.php` | Abstract class BasePokemon |
| 6 | `classPokemon.php` | class pokemon perelemen |
| 7 | `elemenMoves.php` | jurus pokemon |
| 8 | `trainingDescriptions.php` | Training descriptions |
| 9 | `training.php` | cara kerja Training |

atau bisa langsung dengan autoload.php

#### **2. Helper Functions**

| Function | Parameter | Return | Deskripsi |
|----------|-----------|--------|-----------|
| `loadHistory()` | - | `array` | Load history dari `history.json`, return empty array jika file tidak ada |
| `saveHistory()` | `array $history` | `void` | Save history ke `history.json` dengan format JSON pretty print |


#### **3. Flow Diagram**

```
User Access
    ↓
Load Classes → session_start() → Load History
    ↓
Initialize Pokemon (if first visit)
    ↓
┌─────────────────────────────────────┐
│  User Interaction                   │
├─────────────────────────────────────┤
│ 1. Select Pokemon                   │
│ 2. Select Category (Attack/Def/Spd) │
│ 3. Generate Choices (3 pilihan)     │
│ 4. Select Choice                    │
│ 5. Select Duration (10/20/30min)  │
│ 6. Click Start Training             │
└─────────────────────────────────────┘
    ↓
POST to index.php
    ↓
Process Training (Training::process)
    ↓
Update Pokemon Stats & Session
    ↓
Save History to JSON
    ↓
Show Alert & Refresh Page
```

---

### 8. `history.json`

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

## 🎮 Cara Bermain

### 1️⃣ **Pilih Pokemon**
Klik salah satu dari 4 Pokemon yang tersedia:
- **Raichu** (Electric) - High ATK
- **Bulbasaur** (Grass) - High DEF
- **Charmander** (Fire) - Balanced
- **Squirtle** (Water) - High DEF

### 2️⃣ **Pilih Training Category**
- 🎯 **Attack**: Meningkatkan ATK
- 🛡️ **Defense**: Meningkatkan DEF
- ⚡ **Speed**: Meningkatkan SPD

### 3️⃣ **Generate Choices**
Klik "🎲 Generate Choices" untuk mendapatkan 3 pilihan training random dengan bonus berbeda (1-3).

### 4️⃣ **Pilih Training Type**
Pilih salah satu dari 3 pilihan. **Tips**: Pilih type yang sama dengan Pokemon type untuk bonus 2x!

### 5️⃣ **Pilih Duration**
Pilih durasi training:
- **10min**: -10 energy, gain 1 level
- **20min**: -20 energy, gain 2 level
- **30min**: -30 energy, gain 3 level

### 6️⃣ **Start Training**
Klik "💪 Start Training" untuk memulai. Pokemon akan:
- Mendapat increase HP, ATK/DEF/SPD (tergantung category)
- Energy berkurang
- Bisa naik level
- Bisa unlock moves baru

### 7️⃣ **Rest**
Jika energy rendah (<20), klik "😴 Rest" untuk restore energy +20.

### 8️⃣ **Unlock Moves**
Pokemon otomatis unlock moves baru setiap mencapai level tertentu:
- Level 10, 20, 30

---

## 🏗️ Design Patterns

| Pattern | Implementasi |
|---------|--------------|
| **Interface** | `Pokemon` interface |
| **Abstract Class** | `BasePokemon` abstract class |
| **Inheritance** | Electric/Grass/Fire/WaterPokemon extend BasePokemon |
| **Encapsulation** | Properties `protected`, akses via getter |
| **Polymorphism** | `pokemon`, `classPokemon`, `basePokemon` |
---
