# 📦 Dokumentasi Codebase — Inventaris Barang
### Laravel 11 + Filament 3 + PostgreSQL

> **Tujuan dokumen ini:** Membantu kamu memahami struktur project dan konsep OOP yang dipakai,
> supaya siap menjawab pertanyaan pengawas sertifikasi.

---

## 📋 Daftar Isi

1. [Struktur Folder Project](#1-struktur-folder-project)
2. [Alur Kerja Aplikasi](#2-alur-kerja-aplikasi)
3. [Penjelasan File per File](#3-penjelasan-file-per-file)
4. [Konsep OOP di Laravel](#4-konsep-oop-di-laravel)
   - [Class & Object](#41-class--object)
   - [Inheritance (Pewarisan)](#42-inheritance-pewarisan-⭐)
   - [Encapsulation](#43-encapsulation)
   - [Interface & Abstract](#44-interface--abstract)
5. [Konsep Pemrograman Dasar](#5-konsep-pemrograman-dasar)
   - [Loop (For, Foreach)](#51-loop-for-foreach)
   - [Kondisional (If/Else)](#52-kondisional-ifelse)
   - [Array](#53-array)
6. [Pertanyaan Umum Pengawas & Jawaban](#6-pertanyaan-umum-pengawas--jawaban)

---

## 1. Struktur Folder Project

```
inventaris/
│
├── app/
│   ├── Filament/
│   │   └── Resources/
│   │       ├── BarangResource.php          ← Kelas utama CRUD Barang
│   │       └── BarangResource/
│   │           └── Pages/
│   │               ├── ListBarangs.php     ← Halaman daftar barang
│   │               ├── CreateBarang.php    ← Halaman tambah barang
│   │               └── EditBarang.php      ← Halaman edit barang
│   │
│   ├── Models/
│   │   └── Barang.php                      ← Model = representasi tabel di database
│   │
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php      ← Konfigurasi panel admin Filament
│
├── database/
│   └── migrations/
│       ├── xxxx_create_users_table.php     ← Tabel user (sudah ada dari Laravel)
│       └── xxxx_create_barangs_table.php   ← Tabel barang (kita buat)
│
├── .env                                    ← Konfigurasi database & environment
└── routes/
    └── web.php                             ← Routing aplikasi web
```

> **Analogi:** Bayangkan `Models/` adalah **cetakan data**, `Resources/` adalah **layar tampilan**,
> dan `migrations/` adalah **blueprint tabel database**.

---

## 2. Alur Kerja Aplikasi

```
Browser
  │
  ▼
Route (/admin)
  │
  ▼
Filament Panel (AdminPanelProvider)
  │
  ▼
BarangResource  ──────────────────────────────────────────────
  │                                                           │
  ├── form()      → Menampilkan form tambah/edit             │
  ├── table()     → Menampilkan tabel daftar barang          │
  └── getPages()  → Routing ke halaman List/Create/Edit      │
                                                             │
                                                             ▼
                                                       Model Barang
                                                             │
                                                             ▼
                                                    Database PostgreSQL
                                                      (tabel barangs)
```

**Urutan saat kamu klik "Tambah Barang":**
1. Browser hit URL `/admin/barangs/create`
2. Filament panggil `BarangResource::form()`
3. Form ditampilkan ke user
4. User isi & submit → Filament panggil `Barang::create([...])`
5. Model `Barang` simpan ke tabel `barangs` di PostgreSQL

---

## 3. Penjelasan File per File

---

### 📄 `database/migrations/xxxx_create_barangs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Class ini EXTEND Migration — konsep INHERITANCE
class CreateBarangsTable extends Migration
{
    // Method up() dipanggil saat: php artisan migrate
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();                           // kolom id (auto increment)
            $table->string('nama_barang');          // kolom VARCHAR
            $table->string('kategori');             // kolom VARCHAR
            $table->integer('stok');                // kolom INT
            $table->decimal('harga', 10, 2);        // kolom DECIMAL (10 digit, 2 desimal)
            $table->text('keterangan')->nullable(); // kolom TEXT, boleh kosong
            $table->timestamps();                   // kolom created_at & updated_at
        });
    }

    // Method down() dipanggil saat: php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
}
```

**Poin penting:**
- `Migration` adalah **parent class** dari Laravel
- `CreateBarangsTable` adalah **child class** yang meng-override method `up()` dan `down()`
- Ini adalah contoh nyata **Inheritance + Method Overriding**

---

### 📄 `app/Models/Barang.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // import parent class

// Barang EXTEND Model — Inheritance!
class Barang extends Model
{
    // $fillable = daftar kolom yang BOLEH diisi secara massal (mass assignment)
    // Ini contoh ENCAPSULATION — kita batasi data yang bisa masuk
    protected $fillable = [
        'nama_barang',
        'kategori',
        'stok',
        'harga',
        'keterangan',
    ];

    // Karena extend Model, kita OTOMATIS dapat method-method seperti:
    // Barang::all()          → ambil semua data
    // Barang::find(1)        → ambil data by id
    // Barang::create([...])  → insert data baru
    // $barang->update([...]) → update data
    // $barang->delete()      → hapus data
}
```

**Analogi Inheritance di sini:**
```
Model (parent — dari Laravel)
  └── Barang (child — buatan kita)
       └── Mewarisi: all(), find(), create(), update(), delete(), dll.
```

Kita **tidak perlu tulis ulang** method `create()` atau `delete()` karena sudah diwariskan dari `Model`.

---

### 📄 `app/Filament/Resources/BarangResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Models\Barang;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;          // parent class dari Filament
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

// BarangResource EXTEND Resource — Inheritance lagi!
class BarangResource extends Resource
{
    // Property: menghubungkan Resource ini ke Model Barang
    protected static ?string $model = Barang::class;

    // Property: ikon di sidebar (pakai Heroicons)
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Data Barang';
    protected static ?string $modelLabel = 'Barang';

    // Method: mendefinisikan tampilan FORM (tambah & edit)
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required(),

            // Select = dropdown pilihan
            Select::make('kategori')
                ->label('Kategori')
                ->options([           // ini adalah ARRAY associative
                    'Elektronik' => 'Elektronik',
                    'ATK'        => 'ATK',
                    'Furniture'  => 'Furniture',
                    'Lainnya'    => 'Lainnya',
                ])
                ->required(),

            TextInput::make('stok')
                ->label('Stok')
                ->numeric()
                ->minValue(0)
                ->required(),

            TextInput::make('harga')
                ->label('Harga (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpanFull(),   // form field ini span 2 kolom penuh
        ]);
    }

    // Method: mendefinisikan tampilan TABLE (daftar barang)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Setiap TextColumn::make() = 1 kolom di tabel
                TextColumn::make('nama_barang')->label('Nama Barang')->searchable()->sortable(),
                TextColumn::make('kategori')->label('Kategori')->badge(),
                TextColumn::make('stok')->label('Stok')->sortable(),
                TextColumn::make('harga')->label('Harga')->money('IDR')->sortable(),
                TextColumn::make('created_at')->label('Ditambahkan')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),    // tombol Edit per baris
                Tables\Actions\DeleteAction::make(),  // tombol Hapus per baris
            ])
            ->bulkActions([
                // Bulk action = aksi untuk banyak baris sekaligus (checkbox)
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Method: mendaftarkan halaman-halaman yang ada
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit'   => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
```

---

### 📄 Pages (ListBarangs, CreateBarang, EditBarang)

Ketiga file ini **sangat simpel** karena semua logiknya sudah ada di parent class:

```php
// Pages/ListBarangs.php
class ListBarangs extends ListRecords          // extends ListRecords (dari Filament)
{
    protected static string $resource = BarangResource::class;
    // Tidak perlu tulis logika apapun — sudah diwariskan dari ListRecords
}

// Pages/CreateBarang.php
class CreateBarang extends CreateRecord        // extends CreateRecord (dari Filament)
{
    protected static string $resource = BarangResource::class;
    // Tidak perlu tulis logika apapun — sudah diwariskan dari CreateRecord
}

// Pages/EditBarang.php
class EditBarang extends EditRecord            // extends EditRecord (dari Filament)
{
    protected static string $resource = BarangResource::class;
    // Tidak perlu tulis logika apapun — sudah diwariskan dari EditRecord
}
```

> **Ini adalah kekuatan Inheritance!** Kita dapat fitur lengkap (simpan, validasi, redirect)
> hanya dengan `extends` tanpa menulis satu baris logika pun.

---

## 4. Konsep OOP di Laravel

---

### 4.1 Class & Object

**Class** = blueprint / cetakan
**Object** = hasil instansiasi dari class

```php
// Class
class Barang extends Model {
    protected $fillable = ['nama_barang', 'stok', ...];
}

// Membuat Object (instansiasi)
$barang = new Barang();
$barang->nama_barang = 'Laptop';
$barang->stok = 10;
$barang->save(); // simpan ke database

// Atau cara singkat:
$barang = Barang::create([
    'nama_barang' => 'Laptop',
    'stok'        => 10,
    'kategori'    => 'Elektronik',
    'harga'       => 15000000,
]);
```

---

### 4.2 Inheritance (Pewarisan) ⭐

> Paling sering ditanya pengawas!

**Inheritance** = sebuah class mewarisi properti & method dari class lain.
- Keyword: `extends`
- Parent class = superclass
- Child class = subclass

**Pohon inheritance di project ini:**

```
Laravel\Illuminate\Database\Eloquent\Model   ← Parent (dari Laravel)
    └── App\Models\Barang                    ← Child (buatan kita)
         └── Mewarisi: all(), find(), create(), update(), delete()

Filament\Resources\Resource                  ← Parent (dari Filament)
    └── App\Filament\Resources\BarangResource ← Child (buatan kita)

Filament\Resources\Pages\ListRecords         ← Parent
    └── App\Filament\Resources\BarangResource\Pages\ListBarangs ← Child

Illuminate\Database\Migrations\Migration     ← Parent
    └── CreateBarangsTable                   ← Child
```

**Contoh sederhana inheritance (untuk menjelaskan ke pengawas):**

```php
// Parent Class
class Kendaraan {
    public string $merk;

    public function jalan(): string {
        return "Kendaraan sedang berjalan";
    }
}

// Child Class — mewarisi dari Kendaraan
class Motor extends Kendaraan {

    // Method Overriding — menimpa method parent
    public function jalan(): string {
        return "Motor sedang melaju kencang!";
    }

    // Method baru yang hanya ada di Motor
    public function wheelie(): string {
        return "Motor angkat roda depan!";
    }
}

// Penggunaan
$motor = new Motor();
$motor->merk = "Honda";         // properti dari parent
echo $motor->jalan();           // "Motor sedang melaju kencang!" (overriding)
echo $motor->wheelie();         // method milik Motor sendiri
```

**Hubungannya dengan project:**

```php
// Di project kita, Barang extends Model
// artinya Barang MEWARISI semua kemampuan Model

$semuaBarang = Barang::all();   // method ALL dari parent (Model)
$satu = Barang::find(1);        // method FIND dari parent (Model)
$satu->delete();                // method DELETE dari parent (Model)

// Kita tidak perlu tulis sendiri method-method itu!
```

---

### 4.3 Encapsulation

**Encapsulation** = menyembunyikan detail internal, hanya expose yang perlu.

Di Laravel, ini terlihat di properti `$fillable` dan `$guarded`:

```php
class Barang extends Model
{
    // $fillable = WHITELIST: hanya kolom ini yang bisa diisi via create() / update()
    // Kolom lain (misal: id, created_at) tidak bisa diisi sembarangan dari luar
    protected $fillable = [
        'nama_barang',
        'kategori',
        'stok',
        'harga',
        'keterangan',
    ];
}
```

```php
// Tanpa $fillable → bahaya! User bisa inject kolom apa saja (Mass Assignment Attack)
Barang::create($request->all()); // BERBAHAYA

// Dengan $fillable → aman, hanya kolom yang diizinkan yang masuk
Barang::create($request->only(['nama_barang', 'stok', 'kategori', 'harga'])); // AMAN
```

Keyword `protected` juga bagian dari encapsulation:
- `public` → bisa diakses dari mana saja
- `protected` → hanya bisa diakses dari class itu sendiri & child class-nya
- `private` → hanya bisa diakses dari class itu sendiri

---

### 4.4 Interface & Abstract

> Laravel menggunakan ini secara internal, kamu cukup paham konsepnya.

**Interface** = kontrak yang harus dipenuhi oleh class.

```php
// Contoh konsep Interface
interface BisSimpanKeDatabase {
    public function simpan(): bool;
    public function hapus(): bool;
}

// Class yang IMPLEMENT interface WAJIB punya method simpan() dan hapus()
class Barang implements BisSimpanKeDatabase {
    public function simpan(): bool {
        // implementasi...
        return true;
    }

    public function hapus(): bool {
        // implementasi...
        return true;
    }
}
```

**Abstract Class** = class yang tidak bisa di-instansiasi langsung, harus di-extend dulu.

```php
// Model di Laravel adalah abstract class (secara konsep)
// Kamu tidak bisa: $m = new Model(); — tidak ada artinya
// Kamu harus:      $b = new Barang(); — karena Barang adalah konkret class
```

---

## 5. Konsep Pemrograman Dasar

---

### 5.1 Loop (For, Foreach)

**Foreach** — paling sering dipakai di Laravel (iterasi collection/array):

```php
// Ambil semua barang dari database
$barangs = Barang::all(); // ini mengembalikan Collection (mirip Array)

// Iterasi setiap barang
foreach ($barangs as $barang) {
    echo $barang->nama_barang;  // cetak nama setiap barang
    echo $barang->stok;
}
```

**Contoh di Blade Template (view):**

```blade
<!-- resources/views/barang/index.blade.php -->

<table>
  <tr>
    <th>Nama</th>
    <th>Stok</th>
    <th>Harga</th>
  </tr>

  {{-- Foreach di Blade --}}
  @foreach ($barangs as $barang)
  <tr>
    <td>{{ $barang->nama_barang }}</td>
    <td>{{ $barang->stok }}</td>
    <td>Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
  </tr>
  @endforeach
</table>
```

**For loop biasa** (jarang di Laravel tapi tetap valid):

```php
$barangs = Barang::all();

for ($i = 0; $i < count($barangs); $i++) {
    echo $barangs[$i]->nama_barang;
}
// Lebih baik pakai foreach untuk readability
```

**Filament secara internal pakai loop** untuk render kolom tabel:

```php
// Di dalam table() — kita definisikan array kolom
->columns([
    TextColumn::make('nama_barang'),  // Filament akan foreach ini
    TextColumn::make('kategori'),     // dan render setiap kolom
    TextColumn::make('stok'),
])
// Di balik layar, Filament foreach array columns[] untuk render tabel HTML
```

---

### 5.2 Kondisional (If/Else)

```php
// Cek stok sebelum simpan
$barang = Barang::find(1);

if ($barang->stok > 0) {
    echo "Barang tersedia";
} elseif ($barang->stok === 0) {
    echo "Stok habis!";
} else {
    echo "Data stok tidak valid";
}
```

**Di Blade:**

```blade
@if ($barang->stok > 10)
    <span style="color:green">Stok Aman</span>
@elseif ($barang->stok > 0)
    <span style="color:orange">Stok Menipis</span>
@else
    <span style="color:red">Stok Habis</span>
@endif
```

**Di Filament (conditional column):**

```php
// Contoh warna badge berdasarkan kondisi stok
TextColumn::make('stok')
    ->color(fn ($record) => $record->stok > 10 ? 'success' : 'danger')
    // fn() = arrow function PHP — jika stok > 10, warna hijau, else merah
```

---

### 5.3 Array

Array banyak dipakai di Filament untuk definisi form dan tabel:

```php
// Array Indexed (biasa)
$kolom = ['nama_barang', 'kategori', 'stok', 'harga'];

// Array Associative (key => value)
$kategori = [
    'Elektronik' => 'Elektronik',
    'ATK'        => 'ATK',
    'Furniture'  => 'Furniture',
];

// Array of Objects (Collection dari database)
$barangs = Barang::all();
// $barangs[0]->nama_barang, $barangs[1]->nama_barang, dst.
```

**Penggunaan nyata di project:**

```php
// Di BarangResource::form() — array of components
$form->schema([          // schema() menerima ARRAY
    TextInput::make(...), // elemen 0
    Select::make(...),    // elemen 1
    TextInput::make(...), // elemen 2
]);

// Di BarangResource::table() — array of columns
$table->columns([        // columns() menerima ARRAY
    TextColumn::make(...), // elemen 0
    TextColumn::make(...), // elemen 1
]);

// Di getPages() — array associative
return [
    'index'  => Pages\ListBarangs::route('/'),
    'create' => Pages\CreateBarang::route('/create'),
    'edit'   => Pages\EditBarang::route('/{record}/edit'),
];
```

---

## 6. Pertanyaan Umum Pengawas & Jawaban

---

**❓ Apa itu Inheritance? Berikan contoh di project kamu.**

> Inheritance adalah konsep OOP di mana sebuah class (child) mewarisi properti dan method
> dari class lain (parent). Di project ini, `class Barang extends Model` — artinya Barang
> mewarisi semua kemampuan Model dari Laravel seperti `all()`, `find()`, `create()`,
> `update()`, dan `delete()`. Tanpa inheritance, saya harus menulis sendiri semua
> logika database dari nol.

---

**❓ Apa bedanya `extends` dan `implements`?**

> `extends` dipakai untuk **inheritance** — mengambil semua kemampuan parent class.
> `implements` dipakai untuk **interface** — berjanji bahwa class kita akan menyediakan
> method-method yang didefinisikan di interface. Satu class bisa implement banyak interface,
> tapi hanya bisa extend satu parent class.

---

**❓ Kenapa pakai `protected $fillable`?**

> Untuk keamanan — ini adalah konsep **Encapsulation**. Dengan `$fillable`, kita
> membatasi kolom mana yang boleh diisi secara massal dari input user. Tanpa ini,
> user bisa saja mengisi kolom yang tidak seharusnya (seperti `is_admin = true`),
> yang disebut **Mass Assignment Attack**.

---

**❓ Apa fungsi `foreach` di Laravel?**

> `foreach` dipakai untuk iterasi koleksi data. Misalnya setelah `Barang::all()`,
> kita dapat koleksi semua barang, lalu kita `foreach` untuk menampilkan atau
> memproses satu per satu. Di Blade template, kita pakai `@foreach` untuk render
> baris tabel secara dinamis.

---

**❓ Apa itu Model di Laravel?**

> Model adalah representasi dari sebuah tabel database dalam bentuk class PHP.
> Model `Barang` merepresentasikan tabel `barangs`. Dengan Model, kita bisa
> melakukan operasi database (CRUD) menggunakan syntax PHP yang bersih tanpa
> menulis SQL secara langsung. Ini mengikuti pola desain **Active Record**.

---

**❓ Apa itu Migration?**

> Migration adalah file PHP yang mendeskripsikan struktur tabel database.
> Fungsinya seperti version control untuk database — setiap perubahan struktur
> tabel dicatat di file migration. Dengan `php artisan migrate`, Laravel
> membaca semua migration dan membuat tabel di database.

---

**❓ Kenapa menggunakan Filament?**

> Filament adalah admin panel framework untuk Laravel yang mengotomatisasi
> pembuatan halaman CRUD. Dengan Filament, kita cukup definisikan form dan
> tabel di satu file (`BarangResource.php`), dan Filament otomatis membuat
> halaman List, Create, Edit lengkap dengan validasi, notifikasi, dan UI
> yang responsif — tanpa perlu menulis HTML/CSS manual.

---

**❓ Jelaskan perbedaan `public`, `protected`, `private`.**

> - `public` → bisa diakses dari mana saja (dari class, luar class, child class)
> - `protected` → hanya bisa diakses dari class itu sendiri dan turunannya (child class)
> - `private` → hanya bisa diakses dari class itu sendiri saja, tidak oleh child class

---

*Dokumen ini dibuat untuk keperluan sertifikasi. Semangat! 🚀*
