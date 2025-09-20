# Bot Automation Rencana Aksi SKP E-Kinerja - React Version

Aplikasi ini adalah konversi dari `index.php` ke ReactJS dengan mempertahankan logika dan fungsionalitas yang sama persis.

## Struktur Komponen

Aplikasi dibagi menjadi 4 komponen utama sesuai permintaan:

### 1. TambahComponent (`/src/components/TambahComponent.jsx`)
- **Fungsi**: Menambah rencana aksi baru
- **Logika**: Sama seperti tab "Tambah Renaksi" di PHP
- **API**: Mengirim ke `tambakrenaksi.php`
- **Features**:
  - Dropdown cascade Unit → Kategori → User → Bulan
  - Processing JavaScript untuk raw response
  - Validasi form yang sama dengan PHP

### 2. IsiComponent (`/src/components/IsiComponent.jsx`)
- **Fungsi**: Mengisi detail rencana aksi
- **Logika**: Sama seperti tab "Isi Rencana Aksi" di PHP
- **API**: Mengirim ke `ekinprocess.php`
- **Features**:
  - Processing complex mapping bulan dengan status
  - Hidden inputs untuk data JavaScript processing
  - Form validation identik dengan PHP

### 3. AjukanComponent (`/src/components/AjukanComponent.jsx`)
- **Fungsi**: Mengirimkan/mengajukan rencana aksi
- **Logika**: Sama seperti tab "Kirim Renaksi" di PHP
- **API**: Mengirim ke `ajukan.php`
- **Features**:
  - Hanya tampil bulan yang sudah diisi lengkap
  - Mapping id_penilaian yang tepat
  - Single numeric value submission

### 4. PreviewComponent (`/src/components/PreviewComponent.jsx`)
- **Fungsi**: Menampilkan preview data rencana aksi
- **Logika**: Sama seperti tab "Preview Renaksi" di PHP
- **Features**:
  - Tampilan tabel interaktif
  - Summary statistics cards
  - Filter data berdasarkan bulan
  - Status badges dan indicators

## Logika yang Dipertahankan

### 1. JavaScript Processing Function
Fungsi `processRawResponseJS()` diimplementasi identik di setiap komponen untuk:
- Extract data dari raw response
- Parse JSON arrays (penilaiaan_indikator, penilaiaan, rkh_indikator)
- Generate mapping bulan dengan status
- Calculate unique indikator kinerja
- Order renaksi data sesuai mappingTupoksi

### 2. API Calls
- **Endpoint yang sama**: `get_mapping.php`, `tambakrenaksi.php`, `ekinprocess.php`, `ajukan.php`
- **Format data sama**: Plain text untuk credentials, FormData/URLSearchParams untuk submissions
- **Headers identik**: Content-Type dan authentication

### 3. Form Validation
- **Dropdown cascade**: Unit → Kategori → User → Bulan (sama seperti PHP)
- **Disable states**: Identik dengan jQuery logic di PHP
- **Required fields**: Sama dengan PHP validation

### 4. Error Handling
- **Success conditions**: Content length > 500 OR status === 'success'
- **Message display**: Bootstrap alerts dengan timeout
- **Network error handling**: Timeout dan connection issues

## Data Structure

### User Data (`/src/data/userData.js`)
```javascript
const dataUser = {
  "Unit Kerja": {
    "Kategori": {
      "Nama User": {
        username: "username",
        password: "password"
      }
    }
  }
}
```

### Form State Management
Menggunakan React hooks untuk manage state:
- `formData`: Object untuk semua field form
- `isLoading`: Loading states untuk async operations
- `message`: Success/error messages
- `bulanOptions`: Dynamic dropdown options

## Styling

Menggunakan `styles.css` yang sama dengan PHP version:
- **Dark theme** dengan pastel green accents
- **Bootstrap 5.3.2** untuk components
- **Font Awesome 6.4.0** untuk icons
- **Responsive design** untuk mobile compatibility

## Dependencies

### External Libraries (via CDN)
- Bootstrap 5.3.2 CSS & JS
- Font Awesome 6.4.0
- jQuery 3.6.0 (untuk compatibility)
- Axios (untuk HTTP requests)

### React Features Used
- useState untuk state management
- useEffect untuk lifecycle dan cleanup
- Async/await untuk API calls
- Event handling yang identik dengan jQuery

## Installation & Setup

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development server**:
   ```bash
   npm run dev
   ```

3. **Build for production**:
   ```bash
   npm run build
   ```

## API Endpoints

Aplikasi ini tetap bergantung pada backend PHP endpoints:
- `get_mapping.php` - Untuk mapping data user dan bulan
- `tambakrenaksi.php` - Untuk menambah rencana aksi
- `ekinprocess.php` - Untuk mengisi rencana aksi
- `ajukan.php` - Untuk mengajukan rencana aksi

## Key Features Preserved

✅ **Exact same logic** sebagai PHP version
✅ **Identical API calls** dan data format
✅ **Same styling** dengan styles.css
✅ **4 komponen terpisah** sesuai permintaan
✅ **No logic changes** - hanya konversi syntax
✅ **Bootstrap alerts** dan message handling
✅ **Complex JavaScript processing** untuk raw response
✅ **Dropdown cascade** behavior
✅ **Form validation** yang identik

## Browser Compatibility

- Modern browsers dengan ES6+ support
- Bootstrap 5 compatible browsers
- JavaScript fetch API support

## Development Notes

- Semua console.log dari PHP version dipertahankan untuk debugging
- Error handling mengikuti pattern yang sama dengan PHP
- Component separation memudahkan maintenance tanpa mengubah logic
- Data flow identik dengan PHP jQuery implementation