<?php
// ================= KONFIGURASI USER =================
// Struktur: Unit -> Kategori -> User
include('user.php')
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <title>Bot Automation Rencana Aksi SKP E-Kinerja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- ✅ Enhanced dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ✅ Enhanced styles -->
    <link href="css/enhanced_styles.css" rel="stylesheet">
    <!-- External CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<body class="p-4">

<!-- Simple Brand Header -->
<div class="container d-flex justify-content-center">
  <div class="text-center mb-4">
    <div class="brand-container">
      <div class="main-title">
        <i class="fas fa-robot me-3"></i>Bot Automation
      </div>
      <div class="sub-title">
        Rencana Aksi SKP E-Kinerja
      </div>
      <div class="brand-line"></div>
    </div>
  </div>
</div>





<!-- Modern Navigation -->
<nav class="mb-5">
  <div class="container d-flex justify-content-center">
    <ul class="nav nav-pills nav-fill gap-3 flex-row flex-wrap justify-content-center" id="mainMenu" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="nav-tambah-renaksi" type="button" role="tab">
          <i class="fas fa-plus-circle me-2"></i>
          <span>Tambah Renaksi</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="nav-isi-renaksi" type="button" role="tab">
          <i class="fas fa-edit me-2"></i>
          <span>Isi Rencana Aksi</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="nav-ajukan-renaksi" type="button" role="tab">
          <i class="fas fa-paper-plane me-2"></i>
          <span>Kirim Renaksi</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="nav-preview-renaksi" type="button" role="tab">
          <i class="fas fa-eye me-2"></i>
          <span>Preview Renaksi</span>
        </button>
      </li>
    </ul>
  </div>
</nav>

<!-- Elegant separator -->
<div class="container px-0 mb-4">
  <hr>
</div>

<div id="alert-success" class="alert alert-success mt-3 d-none text-center" role="alert">
  Data berhasil dikirim!
</div>

<!-- fixed message container -->
<div class="message-container" id="message-container"></div>



<div class="container-fluid px-2 px-md-5">
   

    <div id="main-content">
        

      <!-- Form Tambah Rencana Aksi -->
      <div id="form-renaksi">
        <div class="container">
          <div class="text-center mb-4">
            <h2>
              <i class="fas fa-plus-circle me-3"></i>
              <strong>Tambah Rencana Aksi</strong>
            </h2>
            <p class="lead">
              <i class="fas fa-info-circle me-2"></i>
              Bulan yang tersedia adalah bulan yang belum memiliki rencana aksi
            </p>
          </div>
          <div class="card p-4 mb-4">
          <form id="form-tambah" autocomplete="off">
            <input type="hidden" id="tupoksi" name="tupoksi" value="">
            <input type="hidden" id="id_skp_t" name="id_skp_t" value="">
            <input type="hidden" id="tahun" name="tahun" value="2025">

            <div class="row g-3 justify-content-center">
              <div class="col-12">
                <label for="unit_t" class="form-label">
                  <i class="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select id="unit_t" class="form-select">
                  <option value="">-- Pilih Unit --</option>
                  <?php foreach ($users as $unit => $kategori): ?>
                    <option value="<?= $unit ?>"><?= $unit ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label for="kategori_t" class="form-label">
                  <i class="fas fa-tags me-2"></i>Kategori
                </label>
                <select id="kategori_t" class="form-select" disabled>
                  <option value="">-- Pilih Kategori --</option>
                </select>
              </div>
              <div class="col-12">
                <label for="user_t" class="form-label">
                  <i class="fas fa-user me-2"></i>Pengguna
                </label>
                <select id="user_t" class="form-select" disabled>
                  <option value="">-- Pilih User --</option>
                </select>
              </div>
              <div class="col-12">
                <label for="bulan_t" class="form-label">
                  <i class="fas fa-calendar me-2"></i>Bulan
                </label>
                <select id="bulan_t" class="form-select" disabled>
                  <option value="">-- Pilih Bulan --</option>
                </select>
              </div>
            </div>
            <div class="mt-4 text-center">
              <button type="button" id="submit-tambah" class="btn btn-success btn-lg px-5">
                <i class="fas fa-plus me-2"></i>Tambah Rencana Aksi
              </button>
            </div>
          </form>
        </div>
      </div>
      </div>

      <!-- Isi Rencana Aksi -->
      <div id="isi-renaksi" style="display:none;">
        <div class="container">
          <div class="text-center mb-4">
            <h2>
              <i class="fas fa-edit me-3"></i>
              <strong>Isi Rencana Aksi</strong>
            </h2>
            <p class="lead">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Pastikan rencana aksi telah ditambahkan sebelum mengisi
            </p>
          </div>
          <div class="card p-4 mb-4">
          <form id="form-ekin" method="post" action="ekinprocess.php" autocomplete="off">
          <input type="hidden" id="allcookies" name="allcookies" value="">
          <input type="hidden" id="csrf_token" name="csrf_token" value="">
          <input type="hidden" id="jumlahUnikIndikatorKinerja" name="jumlahUnikIndikatorKinerja" value="">
          <input type="hidden" id="bukti_dukung" name="bukti_dukung" value="">
          <input type="hidden" id="rhk" name="rhk" value="">
          <div class="row g-3 justify-content-center">
            <div class="col-12">
              <label for="unit" class="form-label">
                <i class="fas fa-building me-2"></i>Unit Kerja
              </label>
              <select id="unit" class="form-select" required>
                <option value="" disabled selected>-- Pilih Unit --</option>
                <?php foreach ($users as $unit => $kategori): ?>
                  <option value="<?= $unit ?>"><?= $unit ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label for="kategori" class="form-label">
                <i class="fas fa-tags me-2"></i>Kategori
              </label>
              <select id="kategori" class="form-select" required disabled>
                <option value="">-- Pilih Kategori --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="user" class="form-label">
                <i class="fas fa-user me-2"></i>Pengguna
              </label>
              <select id="user" name="cookie" class="form-select" required disabled>
                <option value="">-- Pilih User --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="bulan" class="form-label">
                <i class="fas fa-calendar me-2"></i>Bulan
              </label>
              <select id="bulan" name="id_indikator" class="form-select" required disabled>
                <option value="">-- Pilih Bulan --</option>
              </select>
            </div>
          </div>
          <div class="mt-4 text-center">
            <button type="submit" class="btn btn-success btn-lg px-5">
              <i class="fas fa-save me-2"></i>Simpan Rencana Aksi
            </button>
          </div>
        </form>
          </div>
    </div>
</div>

      <!-- Kirim Renaksi -->
      <div id="ajukan-renaksi" style="display:none;">
        <div class="container">
          <div class="text-center mb-4">
            <h2>
              <i class="fas fa-paper-plane me-3"></i>
              <strong>Kirim Rencana Aksi</strong>
            </h2>
            <p class="lead">
              <i class="fas fa-check-circle me-2"></i>
              Hanya menampilkan rencana aksi yang sudah diisi lengkap
            </p>
          </div>
          <div class="card p-4 mb-4">
          <form id="form-ajukan" autocomplete="off">
            <input type="hidden" id="id_skp_a" name="id_skp_a" value="">
            <div class="row g-3 justify-content-center">
              <div class="col-12">
                <label for="unit_a" class="form-label">
                  <i class="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select id="unit_a" class="form-select">
                  <option value="">-- Pilih Unit --</option>
                  <?php foreach ($users as $unit => $kategori): ?>
                    <option value="<?= $unit ?>"><?= $unit ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label for="kategori_a" class="form-label">
                  <i class="fas fa-tags me-2"></i>Kategori
                </label>
                <select id="kategori_a" class="form-select" disabled>
                  <option value="">-- Pilih Kategori --</option>
                </select>
              </div>
              <div class="col-12">
                <label for="user_a" class="form-label">
                  <i class="fas fa-user me-2"></i>Pengguna
                </label>
                <select id="user_a" class="form-select" disabled>
                  <option value="">-- Pilih User --</option>
                </select>
              </div>
              <div class="col-12">
                <label for="bulan_a" class="form-label">
                  <i class="fas fa-calendar me-2"></i>Bulan
                </label>
                <select id="bulan_a" class="form-select" disabled>
                  <option value="">-- Pilih Bulan --</option>
                </select>
              </div>
            </div>
            <div class="mt-4 text-center">
              <button type="button" id="submit-ajukan" class="btn btn-success btn-lg px-5">
                <i class="fas fa-paper-plane me-2"></i>Kirim Rencana Aksi
              </button>
            </div>
          </form>
        </div>
      </div>
 </div>

      <!-- Preview Renaksi Tab -->
      <div id="preview-renaksi" style="display:none;">
        <div class="container">
          <div class="text-center mb-4">
            <h2>
              <i class="fas fa-eye me-3"></i>
              <strong>Preview Isi Renaksi</strong>
            </h2>
            <p class="lead">
              <i class="fas fa-search me-2"></i>
              Lihat detail rencana aksi yang sudah diisi berdasarkan bulan
            </p>
          </div>
          <div class="card p-4 mb-4">
        <form id="form-preview" autocomplete="off">
          <div class="row g-3 justify-content-center">
            <div class="col-12">
              <label for="unit_p" class="form-label">
                <i class="fas fa-building me-2"></i>Unit Kerja
              </label>
              <select id="unit_p" class="form-select">
                <option value="">-- Pilih Unit --</option>
                <?php foreach ($users as $unit => $kategori): ?>
                  <option value="<?= $unit ?>"><?= $unit ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label for="kategori_p" class="form-label">
                <i class="fas fa-tags me-2"></i>Kategori
              </label>
              <select id="kategori_p" class="form-select" disabled>
                <option value="">-- Pilih Kategori --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="user_p" class="form-label">
                <i class="fas fa-user me-2"></i>Pengguna
              </label>
              <select id="user_p" class="form-select" disabled>
                <option value="">-- Pilih User --</option>
              </select>
            </div>
            <div class="col-12">
              <label for="bulan_p" class="form-label">
                <i class="fas fa-calendar me-2"></i>Bulan
              </label>
              <select id="bulan_p" class="form-select" disabled>
                <option value="">-- Pilih Bulan untuk Preview --</option>
              </select>
            </div>
          </div>
          <div class="mt-4 text-center">
            <button type="button" id="btn-preview" class="btn btn-info btn-lg px-5" disabled>
              <i class="fas fa-eye me-2"></i>Tampilkan Preview
            </button>
          </div>
        </form>
    </div>

          <!-- Preview Results Container -->
          <div id="preview-results-container" style="display:none;" class="mt-4">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center" 
                   style="background: linear-gradient(45deg, #6b9b6d, #8bc88c); color: white; border-radius: 16px 16px 0 0;">
                <span>
                  <i class="fas fa-chart-line me-2"></i>
                  <strong>Hasil Preview Rencana Aksi</strong>
                </span>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill" 
                        onclick="$('#preview-results-container').slideUp()">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="card-body p-4">
                <div id="preview-content">
                  <!-- Preview content akan ditampilkan di sini -->
                </div>
              </div>
            </div>
          </div>
  </div>
 </div>
      

<script>
const dataUser = <?= json_encode($users) ?>;

// helper to show a Bootstrap message box attached to body
function showMessage(message, type = 'info', timeout = 5000) {
  var id = 'msg-' + Date.now();
  var iconMap = {
    'success': 'fas fa-check-circle',
    'danger': 'fas fa-exclamation-triangle', 
    'warning': 'fas fa-exclamation-circle',
    'info': 'fas fa-info-circle'
  };
  var icon = iconMap[type] || 'fas fa-bell';
  
  var tpl = '<div id="'+id+'" class="alert alert-' + type + ' alert-dismissible fade show message-box" role="alert">'
          + '<i class="' + icon + ' me-2"></i>'
          + message
          + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
  $('#message-container').append(tpl);
  if (timeout > 0) setTimeout(function(){ $('#' + id).alert('close'); }, timeout);
}

// Fungsi JavaScript untuk memproses raw response (sama seperti js_processor.html)
function processRawResponseJS(rawResponse) {
  try {
    // Validasi input
    if (!rawResponse || typeof rawResponse !== 'string') {
      console.error('❌ Raw response validation failed:', typeof rawResponse, rawResponse);
      throw new Error('Raw response is null, undefined, or not a string. Type: ' + typeof rawResponse);
    }
    
    console.log('🔍 Processing raw response with JavaScript...', 'Length:', rawResponse.length);
    
    // Extract penilaiaan_indikator (sama seperti PHP regex)
    const indikatorMatch = rawResponse.match(/var\s+penilaiaan_indikator\s*=\s*(\[[\s\S]*?\]);/i);
    let indikatorData = null;
    let jumlahUnikIndikatorKinerja = 0;

    if (indikatorMatch) {
      try {
        indikatorData = JSON.parse(indikatorMatch[1]);
        console.log('✅ Found indikator data:', indikatorData.length, 'items');
        
        // Ambil semua id_indikator
        const allIndikatorKinerja = [];
        if (Array.isArray(indikatorData)) {
          indikatorData.forEach(item => {
            if (item.id_indikator) {
              allIndikatorKinerja.push(item.id_indikator);
            }
          });
        }
        
        // Hitung jumlah unik
        const uniqueIndikatorKinerja = [...new Set(allIndikatorKinerja)];
        jumlahUnikIndikatorKinerja = uniqueIndikatorKinerja.length;
        console.log('📊 Unique indikator kinerja:', jumlahUnikIndikatorKinerja);
      } catch (e) {
        console.error('❌ Error parsing indikator JSON:', e);
      }
    } else {
      console.warn('⚠️ No penilaiaan_indikator found in response');
    }

    // Extract penilaiaan
    const penilaiaanMatch = rawResponse.match(/var\s+penilaiaan\s*=\s*(\[[\s\S]*?\]);/i);
    let penilaiaanArr = null;
    if (penilaiaanMatch) {
      try {
        penilaiaanArr = JSON.parse(penilaiaanMatch[1]);
        console.log('✅ Found penilaiaan data:', penilaiaanArr.length, 'items');
      } catch (e) {
        console.error('❌ Error parsing penilaiaan JSON:', e);
      }
    } else {
      console.warn('⚠️ No penilaiaan found in response');
    }

    // Extract rkh_indikator
    const rhkMatch = rawResponse.match(/var\s+rkh_indikator\s*=\s*(\[[\s\S]*?\]);/i);
    let rhkIndikator = null;
    let mappingTupoksi = [];
    if (rhkMatch) {
      try {
        rhkIndikator = JSON.parse(rhkMatch[1]);
        console.log('✅ Found RHK indikator data:', rhkIndikator.length, 'items');
        if (Array.isArray(rhkIndikator)) {
          rhkIndikator.forEach(item => {
            if (item.id_indikator) {
              mappingTupoksi.push(item.id_indikator);
            }
          });
        }
      } catch (e) {
        console.error('❌ Error parsing RHK JSON:', e);
      }
    } else {
      console.warn('⚠️ No rkh_indikator found in response');
    }

    // Mapping bulan
    const bulan = {
      1: "Januari", 2: "Februari", 3: "Maret", 4: "April",
      5: "Mei", 6: "Juni", 7: "Juli", 8: "Agustus",
      9: "September", 10: "Oktober", 11: "November", 12: "Desember"
    };

    // Process mapping bulan dengan status
    let mappingBulanId = {};
    let mappingBulanIdIndikator = {}; // Map untuk id_indikator yang sesuai urutan id
    if (Array.isArray(penilaiaanArr) && Array.isArray(indikatorData)) {
      const bulanLookup = {};
      const statusLookup = {};
      
      penilaiaanArr.forEach(item => {
        if (item.id && item.bulan !== undefined && item.status !== undefined && [0,1,2].includes(item.status)) {
          bulanLookup[item.id] = item.bulan;
          statusLookup[item.id] = item.status;
        }
      });

      indikatorData.forEach(ind => {
        if (!ind.id || !ind.id_penilaiaan || !ind.id_indikator) return;
        
        const id = ind.id;
        const id_penilaiaan = ind.id_penilaiaan;
        const id_indikator = ind.id_indikator;
        const realisasi = ind.realisasi || 0;
        
        if (!bulanLookup[id_penilaiaan]) return;
        
        const bulan_num = parseInt(bulanLookup[id_penilaiaan]);
        if (!bulan[bulan_num]) return;
        
        const namaBulan = bulan[bulan_num];
        const status = statusLookup[id_penilaiaan];
        
        let label = namaBulan;
        if (status === 1) {
          label += " - Sudah di Ajukan";
        } else if (status === 2) {
          label += " - Sudah Di Nilai";
        } else if (status === 0) {
          if (realisasi == 0) {
            label += " - Belum diisi";
          } else {
            label += " - Sudah diisi";
          }
        }

        if (!mappingBulanId[label]) {
          mappingBulanId[label] = [];
          mappingBulanIdIndikator[label] = [];
        }
        mappingBulanId[label].push(id);
        mappingBulanIdIndikator[label].push(id_indikator);
      });
    }

    // Extract renaksi data - DIURUTKAN BERDASARKAN urutan dalam mappingBulanId
    const renaksiBulanId = [];
    const renaksiOrderMap = {}; // Map untuk menyimpan urutan yang benar
    
    if (Array.isArray(indikatorData)) {
      // Buat mapping renaksi dan id berdasarkan id_indikator
      const renaksiByIdIndikator = {};
      const idByIdIndikator = {};
      
      indikatorData.forEach(item => {
        if (item.id_indikator && item.renaksi && item.id) {
          renaksiByIdIndikator[item.id_indikator] = item.renaksi;
          idByIdIndikator[item.id_indikator] = item.id;
        }
      });
      
      // Untuk setiap bulan, buat mapping urutan yang konsisten
      for (let bulan in mappingBulanId) {
        const bulanIds = mappingBulanId[bulan];
        const bulanIdIndikators = mappingBulanIdIndikator[bulan];
        
        bulanIds.forEach((id, index) => {
          const idIndikator = bulanIdIndikators[index];
          if (renaksiByIdIndikator[idIndikator]) {
            renaksiOrderMap[id] = renaksiByIdIndikator[idIndikator];
          }
        });
      }
      
      // Ambil renaksi sesuai urutan mappingTupoksi (konsisten dengan form submission)
      mappingTupoksi.forEach(idIndikator => {
        if (renaksiByIdIndikator[idIndikator]) {
          renaksiBulanId.push(renaksiByIdIndikator[idIndikator]);
        } else {
          renaksiBulanId.push(''); // Placeholder
        }
      });
      
      console.log('🔍 RENAKSI ORDER DEBUG:');
      console.log('- mappingTupoksi:', mappingTupoksi);
      console.log('- renaksiOrderMap:', renaksiOrderMap);
      console.log('- renaksiBulanId final:', renaksiBulanId);
    }

    // Generate mapping id_penilaian seperti PHP
    let mappingIdPenilaiaan = {};
    if (Array.isArray(penilaiaanArr)) {
      penilaiaanArr.forEach(item => {
        if (item.status === 0 && item.bulan && bulan[item.bulan]) {
          const namaBulan = bulan[item.bulan];
          mappingIdPenilaiaan[namaBulan] = item.id;
        }
      });
    }

    // Generate mapping id_penilaian1 (bulan berikutnya) seperti PHP
    let mappingIdPenilaiaan1 = {};
    if (Array.isArray(penilaiaanArr)) {
      let bulanTerakhir = 0;
      penilaiaanArr.forEach(item => {
        if (item.bulan && parseInt(item.bulan) > bulanTerakhir) {
          bulanTerakhir = parseInt(item.bulan);
        }
      });
      
      const bulanBerikutnya = bulanTerakhir + 1;
      if (bulan[bulanBerikutnya]) {
        const namaBulan = bulan[bulanBerikutnya];
        const idDitemukan = penilaiaanArr.find(item => 
          parseInt(item.bulan) === bulanBerikutnya
        );
        mappingIdPenilaiaan1[namaBulan] = idDitemukan ? idDitemukan.id : namaBulan;
      }
    }

    // Get bukti_dukung
    let buktiDukung = '';
    if (Array.isArray(indikatorData)) {
      const found = indikatorData.find(item => item.bukti_dukung);
      if (found) buktiDukung = found.bukti_dukung;
    }

    const result = {
      // Data mentah
      indikatorData,
      penilaiaanArr,
      rhkIndikator,
      
      // Data processed untuk dropdown
      jumlahUnikIndikatorKinerja,
      mappingTupoksi,
      mappingBulanId,
      mappingBulanIdIndikator,
      mappingIdPenilaiaan,
      mappingIdPenilaiaan1,
      renaksiBulanId,
      buktiDukung,
      
      success: true
    };

    console.log('🎉 JavaScript processing completed:', result);
    
    // Display hasil detail di console untuk debugging
    console.log('📊 Hasil Processing Detail:');
    console.log('- Indikator Kinerja Unik:', result.jumlahUnikIndikatorKinerja);
    console.log('- Mapping Tupoksi:', result.mappingTupoksi.length, 'items');
    console.log('- Mapping Bulan:', Object.keys(result.mappingBulanId).length, 'bulan');
    console.log('- Renaksi Data:', result.renaksiBulanId.length, 'items');
    console.log('- Bukti Dukung:', result.buktiDukung ? 'Ada' : 'Tidak ada');
    
    return result;

  } catch (error) {
    console.error('❌ JavaScript Processing Error:', error);
    showMessage('JavaScript Processing Error: ' + error.message, 'danger', 8000);
    return { success: false, error: error.message };
  }
}





// Tambahkan fungsi untuk menampilkan alert sukses dengan pesan custom
function showSuccessAlert(message) {
  $("#alert-success").removeClass("d-none").text(message);
  setTimeout(function() {
    $("#alert-success").addClass("d-none");
  }, 6000);
}

// Jika unit dipilih
$("#unit").on("change", function() {
    let unit = $(this).val();
    $("#kategori").html('<option value="">-- Pilih Kategori --</option>');
    $("#user").html('<option value="">-- Pilih User --</option>').prop("disabled", true);
    $("#bulan").html('<option value="">-- Pilih Bulan --</option>').prop("disabled", true);

    if (unit && dataUser[unit]) {
        Object.keys(dataUser[unit]).forEach(k => {
            $("#kategori").append('<option value="'+k+'">'+k+'</option>');
        });
        $("#kategori").prop("disabled", false);
    }
});

// Saat kategori dipilih
$("#kategori").on("change", function() {
    let unit = $("#unit").val();
    let kategori = $(this).val();
    $("#user").html('<option value="">-- Pilih User --</option>');
    $("#bulan").html('<option value="">-- Pilih Bulan --</option>').prop("disabled", true);

  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(nama => {
      let userObj = dataUser[unit][kategori][nama];
      // use username as the option value; password will be looked up from dataUser on selection
      $("#user").append('<option value="'+userObj.username+'">'+nama+'</option>');
    });
    $("#user").prop("disabled", false);
  }
});

// Saat user dipilih → ambil mapping bulan via AJAX
$("#user").on("change", function() {
  // jumlah_rhk may not be set in the users data; keep existing hidden input if present
  let jumlah_rhk = $("#jumlah_rhk").val();

  // selected value is the username
  let username = $(this).val();

  // lookup password from in-memory dataUser to avoid embedding sensitive data in DOM
  let unit = $("#unit").val();
  let kategori = $("#kategori").val();
  let password = "";
  if (unit && kategori && dataUser[unit] && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(nama => {
      let u = dataUser[unit][kategori][nama];
      if (u.username == username) password = u.password;
    });
  }
  

  $("#bulan").html('<option>Loading...</option>');
  $("#user").prop('disabled', true);
  // send raw POST body with only the username and password values (space-separated)
  $.ajax({
    url: "get_mapping.php",
    method: "POST",
    contentType: 'text/plain; charset=UTF-8',
    dataType: 'json',
    data: username + ' ' + password,
    timeout: 60000,
    success: function(res, textStatus, xhr) {
      // if xhr missing or response empty => treat as dropped/failed
      if (!xhr || (xhr.status && xhr.status === 0)) {
        $("#bulan").html('<option>❌ Request dropped (network)</option>');
        return;
      }
      $("#bulan").html('<option value="">-- Pilih Bulan --</option>');
      // consider both formats: {success: true} or {status: 'success'}
      var isSuccess = false;
      if (res && typeof res === 'object') {
        if (res.success === true) isSuccess = true;
        else if (res.status && String(res.status).toLowerCase() === 'success') isSuccess = true;
      }

      if (isSuccess) {
        // Set basic data dari API response
        var csrfVal = res.csrf_token || '';
        var cookiesVal = res.allCookies || '';

        // fallback: extract XSRF-TOKEN from cookies if csrf empty
        if ((!csrfVal || csrfVal === '') && cookiesVal) {
          var m = cookiesVal.match(/XSRF-TOKEN=([^;]+)/i);
          if (m) {
            try { csrfVal = decodeURIComponent(m[1]); } catch(e) { csrfVal = m[1]; }
          }
        }

        $("#csrf_token").val(csrfVal);
        $("#allcookies").val(cookiesVal ? cookiesVal : '');
        
        // 🔥 PROCESS RAW RESPONSE DENGAN JAVASCRIPT
        if (res.raw_response) {
          console.log('🚀 Processing raw response with JavaScript...');
          showMessage('📊 Processing data dengan JavaScript...', 'info', 3000);
          
          // Proses raw response dengan JavaScript
          const jsResult = processRawResponseJS(res.raw_response);
          
          if (jsResult.success) {
            showMessage('✅ JavaScript processing berhasil!', 'success', 3000);
            
            // 🔥 POPULATE BULAN DROPDOWN DARI JAVASCRIPT RESULT
            $("#bulan").html('<option value="">-- Pilih Bulan --</option>');
            if (jsResult.mappingBulanId) {
              for (let bulan in jsResult.mappingBulanId) {
                let canSelect = bulan.includes('Sudah diisi') || bulan.includes('Belum diisi');
                
                // PENTING: Gunakan mappingTupoksi untuk konsistensi dengan renaksiBulanId
                // Tapi pastikan urutan id sesuai dengan mappingTupoksi
                let bulanIds = jsResult.mappingBulanId[bulan];
                let bulanIdIndikators = jsResult.mappingBulanIdIndikator[bulan];
                
                // Buat array id yang diurutkan sesuai dengan mappingTupoksi
                let orderedIds = [];
                if (jsResult.mappingTupoksi && bulanIdIndikators) {
                  jsResult.mappingTupoksi.forEach(idIndikator => {
                    let index = bulanIdIndikators.indexOf(idIndikator);
                    if (index !== -1 && bulanIds[index]) {
                      orderedIds.push(bulanIds[index]);
                    }
                  });
                }
                
                $("#bulan").append(
                  '<option value="'+JSON.stringify(orderedIds)+'"'+(canSelect ? '' : ' disabled')+'>'+bulan+'</option>'
                );
              }
            }
            $("#bulan").prop("disabled", false);
            
            // 🔥 SET HIDDEN INPUTS DARI JAVASCRIPT RESULT
            $("#jumlahUnikIndikatorKinerja").val(jsResult.jumlahUnikIndikatorKinerja || '');
            $("#bukti_dukung").val(jsResult.buktiDukung || '');
            $("#rhk").val(jsResult.renaksiBulanId ? JSON.stringify(jsResult.renaksiBulanId) : '');
            
            // 🔍 DEBUG: Tampilkan mapping untuk debugging
            console.log('🔍 DEBUG MAPPINGS:');
            console.log('- mappingTupoksi (urutan id_indikator):', jsResult.mappingTupoksi);
            console.log('- renaksiBulanId (urutan sesuai mappingTupoksi):', jsResult.renaksiBulanId);
            
            if (jsResult.mappingBulanId && jsResult.mappingBulanIdIndikator) {
              for (let bulan in jsResult.mappingBulanId) {
                let bulanIds = jsResult.mappingBulanId[bulan];
                let bulanIdIndikators = jsResult.mappingBulanIdIndikator[bulan];
                
                // Buat ordered IDs sesuai mappingTupoksi
                let orderedIds = [];
                if (jsResult.mappingTupoksi) {
                  jsResult.mappingTupoksi.forEach(idIndikator => {
                    let index = bulanIdIndikators.indexOf(idIndikator);
                    if (index !== -1 && bulanIds[index]) {
                      orderedIds.push(bulanIds[index]);
                    }
                  });
                }
                
                console.log(`- ${bulan}:`);
                console.log(`  Original IDs: [${bulanIds.join(', ')}]`);
                console.log(`  ID Indikators: [${bulanIdIndikators.join(', ')}]`);
                console.log(`  Ordered IDs (akan dikirim): [${orderedIds.join(', ')}]`);
              }
            }
            
            // Display hasil di console untuk monitoring
            console.log('=== ISI RENAKSI - HASIL PROCESSING DATA ===');
            console.log('📊 Summary Data:');
            console.log('- Jumlah Indikator Kinerja Unik:', jsResult.jumlahUnikIndikatorKinerja || 0);
            console.log('- Bukti Dukung:', jsResult.buktiDukung ? 'Ada' : 'Tidak ada');
            console.log('- Jumlah Renaksi Data:', jsResult.renaksiBulanId ? jsResult.renaksiBulanId.length : 0);
            console.log('- Data Processing Status:', jsResult.success ? 'Berhasil' : 'Gagal');
            console.log('🔍 Full Data Object:', jsResult);
            console.log('===== END ISI RENAKSI PROCESSING =====\n');
            
            // Store JS results untuk digunakan nanti
            window.jsProcessingResult = jsResult;
            
            console.log('✅ Dropdown populated from JavaScript processing');
          } else {
            $("#bulan").html('<option>❌ JavaScript processing gagal</option>');
            showMessage('⚠️ JavaScript processing gagal: ' + (jsResult.error || 'Unknown error'), 'warning', 6000);
          }
        } else {
          $("#bulan").html('<option>❌ No raw response</option>');
          console.warn('⚠️ No raw_response found in API response');
          showMessage('⚠️ Raw response tidak tersedia untuk JavaScript processing', 'warning', 4000);
        }
      } else {
        $("#bulan").html('<option>❌ Gagal ambil data</option>');
      }
    },
    error: function(xhr, status, err) {
      if (status === 'timeout') {
        $("#bulan").html('<option>❌ Request timeout</option>');
      } else if (xhr && xhr.status === 0) {
        $("#bulan").html('<option>❌ Request dropped (network)</option>');
      } else {
        $("#bulan").html('<option>❌ Gagal ambil data</option>');
      }
    },complete: function() {
      $("#user").prop('disabled', false); // enable kembali setelah selesai
    }
  });
});

$("#form-ekin").on("submit", function(e) {
  e.preventDefault();
  $("#alert-success").addClass("d-none"); // sembunyikan alert dulu

  // disable the submit button and save its original text
  var $form = $(this);
  var $submitBtn = $form.find('button[type=submit]');
  if (!$submitBtn.data('orig-text')) $submitBtn.data('orig-text', $submitBtn.text());
  $submitBtn.prop('disabled', true).text('Mengirim...');
  
  // Store form data BEFORE disabling elements
  var formData = $(this).serialize();
  
  // Now disable the form elements
  $("#user").prop('disabled', true);
  $("#kategori").prop('disabled', true);
  $("#bulan").prop('disabled', true);
  $("#unit").prop('disabled', true);

  $.ajax({
    url: $(this).attr("action"),
    method: "POST",
    data: formData, // Use the stored serialized data
    timeout: 1200000, // 1200s timeout
    success: function(res, status, xhr) {
      // treat success only if HTTP 200 and response indicates success (if JSON)
      var httpStatus = xhr && xhr.status ? xhr.status : null;
      var okBody = true;
      try {
        if (res && typeof res === 'object') {
          if ('success' in res) okBody = res.success === true;
          else if ('status' in res) okBody = String(res.status).toLowerCase() === 'success';
          else okBody = false;
        }
      } catch (e) { okBody = false; }

      // Tambahkan pengecekan jika response content length > 500
      var contentLen = 0;
      if (typeof res === 'string') contentLen = res.length;
      else if (typeof res === 'object') contentLen = JSON.stringify(res).length;

      if (okBody || contentLen > 500) {
        showSuccessAlert('Rencana Aksi Berhasil di Isi');
      } else {
        var msg = 'Gagal mengirim data!';
        if (httpStatus) msg += ' (status: ' + httpStatus + ')';
        showMessage(msg, 'danger', 8000);
      }
    },
    error: function(xhr, status, error) {
  var msg = 'Gagal mengirim data!';
  if (status === 'timeout') msg += ' (timeout)';
  else if (xhr && xhr.status) msg += ' (status: ' + xhr.status + ')';
  showMessage(msg, 'danger', 8000);
    }
  ,
    complete: function() {
      // re-enable submit button and restore text
      var $btn = $submitBtn;
      if ($btn && $btn.length) {
        $btn.prop('disabled', false).text($btn.data('orig-text'));
      $("#unit").prop('disabled', false);
      $("#kategori").prop('disabled', false);
      $("#user").prop('disabled', false);
      $("#bulan").prop('disabled', false);
      }
    }
  });
});

// ===== Handlers for Tambah tab (unit_t/kategori_t/user_t/bulan_t) =====
// populate kategori_t when unit_t changes
$("#unit_t").on("change", function() {
  var unit = $(this).val();
  $("#kategori_t").html('<option value="">-- Pilih Kategori --</option>');
  $("#user_t").html('<option value="">-- Pilih User --</option>').prop('disabled', true);
  $("#bulan_t").html('<option value="">-- Pilih Bulan --</option>').prop('disabled', true);
  if (unit && dataUser[unit]) {
    Object.keys(dataUser[unit]).forEach(k => {
      $("#kategori_t").append('<option value="'+k+'">'+k+'</option>');
    });
    $("#kategori_t").prop('disabled', false);
  }
});

// populate users when kategori_t changes
$("#kategori_t").on("change", function() {
  var unit = $("#unit_t").val();
  var kategori = $(this).val();
  $("#user_t").html('<option value="">-- Pilih User --</option>');
  $("#bulan_t").html('<option value="">-- Pilih Bulan --</option>').prop('disabled', true);
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(nama => {
      var u = dataUser[unit][kategori][nama];
      $("#user_t").append('<option value="'+u.username+'">'+nama+'</option>');
    });
    $("#user_t").prop('disabled', false);
  }
});

// when a user_t is selected, call get_mapping.php and populate bulan_t with numeric values
$("#user_t").on("change", function() {
  var username = $(this).val();
  var unit = $("#unit_t").val();
  var kategori = $("#kategori_t").val();
  var password = '';
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(nama => {
      var u = dataUser[unit][kategori][nama];
      if (u.username == username) password = u.password;
    });
  }

  $("#bulan_t").html('<option>Loading...</option>');
  // Disable user dropdown saat loading (tambahan agar tidak bisa diklik)
  $("#user_t").prop('disabled', true);

  $.ajax({
   url: 'get_mapping.php', method: 'POST', contentType: 'text/plain; charset=UTF-8', dataType: 'json',
    data: username + ' ' + password,
    success: function(res) {
      $('#bulan_t').html('<option value="" disabled selected>-- Pilih Bulan --</option>');
      var ok = res && (res.success === true || (res.status && String(res.status).toLowerCase()==='success'));
      if (!ok) { $('#bulan_t').html('<option>❌ Gagal ambil data</option>'); return; }

      // set id_skp for ajukan form if provided
      var idskp = res.id_skp || '';
      $('#id_skp_t').val(idskp);

      // 🔥 PROCESS RAW RESPONSE DENGAN JAVASCRIPT
      if (res.raw_response) {
        console.log('🚀 [TAMBAH TAB] Processing raw response with JavaScript...');
        
        const jsResult = processRawResponseJS(res.raw_response);
        
        if (jsResult.success) {
          console.log('✅ [TAMBAH TAB] JavaScript processing berhasil');
          
          // 🔥 POPULATE BULAN DROPDOWN DARI JAVASCRIPT RESULT (ID_PENILAIAN1)
          var monthOrder = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
          var monthMapNum = { 'Januari':1,'Februari':2,'Maret':3,'April':4,'Mei':5,'Juni':6,'Juli':7,'Agustus':8,'September':9,'Oktober':10,'November':11,'Desember':12 };
          var addedTambah = false;
          
          if (jsResult.mappingIdPenilaiaan1) {
            for (var i=0;i<monthOrder.length;i++){
              var nm = monthOrder[i];
              var val = jsResult.mappingIdPenilaiaan1[nm] || '';
              if (!val) continue; // skip months without id_penilaian
              var bulanNum = monthMapNum[nm]; // Ambil nomor bulan
              $('#bulan_t').append('<option value="'+bulanNum+'">'+nm+'</option>');
              addedTambah = true;
            }
          }
          
          if (!addedTambah) {
            $('#bulan_t').html('<option value="" disabled selected>-- Tidak ada bulan yang dapat ditambahkan --</option>');
          }
          $('#bulan_t').prop('disabled', false);
          
          // Set tupoksi dari JavaScript result
          $("#tupoksi").val(JSON.stringify(jsResult.mappingTupoksi || []));
          
        } else {
          $('#bulan_t').html('<option>❌ JavaScript processing gagal</option>');
        }
      } else {
        $('#bulan_t').html('<option>❌ No raw response</option>');
      }
      
      // Set csrf, cookies dari API response
      var csrfVal = res.csrf_token || '';
      var cookiesVal = res.allCookies || '';
      // fallback: extract XSRF-TOKEN from cookies if csrf empty
      if ((!csrfVal || csrfVal === '') && cookiesVal) {
        var m2 = cookiesVal.match(/XSRF-TOKEN=([^;]+)/i);
        if (m2) {
          try { csrfVal = decodeURIComponent(m2[1]); } catch(e) { csrfVal = m2[1]; }
        }
      }
      $('#csrf_token').val(csrfVal);
      $('#allcookies').val(cookiesVal ? cookiesVal : '');
    },
    error: function() { $('#bulan_t').html('<option>❌ Gagal ambil data</option>'); },
    complete: function() {
      $('#user_t').prop('disabled', false); // enable kembali setelah selesai
    }
  });
});

  $(document).ready(function() {
    // Reset form saat halaman dimuat
    $("#form-ekin")[0].reset();
    $("#kategori").prop("disabled", true).val('');
    $("#user").prop("disabled", true).val('');
    $("#bulan").prop("disabled", true).val('');
    $("#csrf_token").val('');
    $("#allcookies").val('');

    // Selalu tampilkan hanya tab pertama saat reload
    $("#form-renaksi").show();
    $("#isi-renaksi").hide();
    $("#ajukan-renaksi").hide();
    $("#preview-renaksi").hide();
    $(".nav-link").removeClass("active");
    $("#nav-tambah-renaksi").addClass("active");

    // Toggle konten
    $("#nav-tambah-renaksi").on("click", function(e) {
        e.preventDefault();
        $("#form-renaksi").show();
        $("#isi-renaksi").hide();
        $("#ajukan-renaksi").hide();
        $("#preview-renaksi").hide();
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
    });
    $("#nav-isi-renaksi").on("click", function(e) {
        e.preventDefault();
        $("#form-renaksi").hide();
        $("#isi-renaksi").show();
        $("#ajukan-renaksi").hide();
        $("#preview-renaksi").hide();
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
    });
    $("#nav-ajukan-renaksi").on("click", function(e) {
        e.preventDefault();
        $("#form-renaksi").hide();
        $("#isi-renaksi").hide();
        $("#ajukan-renaksi").show();
        $("#preview-renaksi").hide();
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
    });
    $("#nav-preview-renaksi").on("click", function(e) {
        e.preventDefault();
        $("#form-renaksi").hide();
        $("#isi-renaksi").hide();
        $("#ajukan-renaksi").hide();
        $("#preview-renaksi").show();
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
    });
    // disable submit buttons until a user is chosen
    $("#submit-ajukan").prop('disabled', true);
    $("#submit-tambah").prop('disabled', true);
    // disable Isi form submit
    $("#form-ekin").find('button[type=submit]').prop('disabled', true);

    // enable/disable submit only when both user and bulan are selected
    $(document).on('change', '#user, #bulan', function() {
      var userVal = $('#user').val();
      var bulanVal = $('#bulan').val();
      $('#form-ekin').find('button[type=submit]').prop('disabled', !(userVal && bulanVal));
    });
    $(document).on('change', '#user_t, #bulan_t', function() {
      var userVal = $('#user_t').val();
      var bulanVal = $('#bulan_t').val();
      $('#submit-tambah').prop('disabled', !(userVal && bulanVal));
    });
    $(document).on('change', '#user_a, #bulan_a', function() {
      var userVal = $('#user_a').val();
      var bulanVal = $('#bulan_a').val();
      $('#submit-ajukan').prop('disabled', !(userVal && bulanVal));
    });
    $(document).on('change', '#user_p, #bulan_p', function() {
      var userVal = $('#user_p').val();
      var bulanVal = $('#bulan_p').val();
      $('#btn-preview').prop('disabled', !(userVal && bulanVal));
      
      // Jika preview sudah ditampilkan dan user berubah, sembunyikan preview lama
      if ($(this).attr('id') === 'user_p' && $('#preview-results-container').is(':visible')) {
        $('#preview-results-container').slideUp();
        showMessage('Pilihan user berubah. Klik "Tampilkan Preview" untuk melihat data terbaru.', 'info', 3000);
      }
    });
});

// Handler for Tambah submit button
$(document).on('click', '#submit-tambah', function(e) {
  e.preventDefault();
  $("#alert-success").addClass("d-none"); // sembunyikan alert dulu
  // collect values
  var tupoksiVal = $('#tupoksi').length ? $('#tupoksi').val() : '';
  var bulanVal = $('#bulan_t').val();
  var idSkpVal = $('#id_skp_t').val();
  var csrfVal = $('#csrf_token').val();
  var cookiesVal = $('#allcookies').val();

  if (!tupoksiVal || !bulanVal || !idSkpVal) {
    alert('Pastikan Tupoksi, Bulan, dan ID SKP sudah terisi.');
    return;
  }

  // send as form-encoded POST
  var payload = $.param({
    tupoksi: tupoksiVal,
    bulan: bulanVal,
    id_skp: idSkpVal,
  username: $('#user_t').val(),
    csrf_token: csrfVal,
    allcookies: cookiesVal
  });

  $('#submit-tambah').prop('disabled', true).text('Mengirim...');
   $("#user_t").prop('disabled', true);
    $("#kategori_t").prop('disabled', true);
     $("#bulan_t").prop('disabled', true);
      $("#unit_t").prop('disabled', true);

  $.ajax({
    url: 'tambakrenaksi.php',
    method: 'POST',
    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
    data: payload,
    dataType: 'json',
    success: function(res) {
      // Cek status success atau content length > 500
      var ok = res && res.status === 'success';
      var contentLen = 0;
      if (typeof res === 'string') contentLen = res.length;
      else if (typeof res === 'object') contentLen = JSON.stringify(res).length;
      if (ok || contentLen > 500) {
        showSuccessAlert('Rencana Aksi Berhasil Ditambahkan');
      } else {
        showMessage('Gagal: ' + (res && res.message ? res.message : JSON.stringify(res)), 'danger', 8000);
      }
    },
    complete: function() {
      $('#submit-tambah').prop('disabled', false).text('Kirim Tambah');
      $("#unit_t").prop('disabled', false);
      $("#kategori_t").prop('disabled', false);
      $("#user_t").prop('disabled', false);
      $("#bulan_t").prop('disabled', false);
    }
  });
});

// ===== Handlers for Ajukan tab (unit_a/kategori_a/user_a/bulan_a) =====
// populate kategori_a when unit_a changes
$(document).on('change', '#unit_a', function() {
  var unit = $(this).val();
  $('#kategori_a').html('<option value="">-- Pilih Kategori --</option>');
  $('#user_a').html('<option value="">-- Pilih User --</option>').prop('disabled', true);
  $('#bulan_a').html('<option value="">-- Pilih Bulan --</option>').prop('disabled', true);
  if (unit && dataUser[unit]) {
    Object.keys(dataUser[unit]).forEach(function(k){ $('#kategori_a').append('<option value="'+k+'">'+k+'</option>'); });
    $('#kategori_a').prop('disabled', false);
  }
});

// populate users when kategori_a changes
$(document).on('change', '#kategori_a', function() {
  var unit = $('#unit_a').val();
  var kategori = $(this).val();
  $('#user_a').html('<option value="">-- Pilih User --</option>');
  $('#bulan_a').html('<option value="">-- Pilih Bulan --</option>').prop('disabled', true);
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(function(nama){
      var u = dataUser[unit][kategori][nama];
      $('#user_a').append('<option value="'+u.username+'">'+nama+'</option>');
    });
    $('#user_a').prop('disabled', false);
  }
});

// when a user_a is selected, call get_mapping.php and populate bulan_a using id_penilaian mapping
$(document).on('change', '#user_a', function() {
  var username = $(this).val();
  var unit = $('#unit_a').val();
  var kategori = $('#kategori_a').val();
  var password = '';
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(function(nama){
      var u = dataUser[unit][kategori][nama]; if (u.username == username) password = u.password;
    });
  }

  $('#bulan_a').html('<option>Loading...</option>');
  // Disable user dropdown saat loading (tambahan agar tidak bisa diklik)
  $('#user_a').prop('disabled', true);

  $.ajax({
    url: 'get_mapping.php', method: 'POST', contentType: 'text/plain; charset=UTF-8', dataType: 'json',
    data: username + ' ' + password,
    success: function(res) {
      $('#bulan_a').html('<option value="" disabled selected>-- Pilih Bulan --</option>');
      
      var ok = res && (res.success === true || (res.status && String(res.status).toLowerCase()==='success'));
      if (!ok) { $('#bulan_a').html('<option>❌ Gagal ambil data</option>'); return; }

      // Process raw response using JavaScript
      if (!res.raw_response) {
        console.error('❌ [AJUKAN] No raw_response found in API response. Response structure:', res);
        $('#bulan_a').html('<option>❌ Raw response tidak tersedia</option>');
        return;
      }
      
      var jsResult = processRawResponseJS(res.raw_response);
      console.log('Ajukan - JS Processing Result:', jsResult);
      
      // Use JavaScript processed mappingIdPenilaiaan for dropdown population
      var idPenMap = jsResult.mappingIdPenilaiaan || {};
      var monthOrder = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      
      // Ajukan should match Isi: only list months that have an id_penilaian
      var addedAjukan = false;
      for (var i=0;i<monthOrder.length;i++){
        var nm = monthOrder[i];
        var val = idPenMap[nm] || '';
        if (!val) continue; // skip months without id_penilaian
        $('#bulan_a').append('<option value="'+val+'">'+nm+'</option>');
        addedAjukan = true;
      }
      if (!addedAjukan) {
        $('#bulan_a').html('<option value="" disabled selected>-- Tidak ada bulan yang dapat diajukan --</option>');
      }
      $('#bulan_a').prop('disabled', false);

      // Set values from minimal response
      var idskp = res.id_skp || '';
      $('#id_skp_a').val(idskp);
      
      var csrfVal = res.csrf_token || '';
      var cookiesVal = res.allCookies || '';
      
      // fallback: extract XSRF-TOKEN from cookies if csrf empty
      if ((!csrfVal || csrfVal === '') && cookiesVal) {
        var m2 = cookiesVal.match(/XSRF-TOKEN=([^;]+)/i);
        if (m2) {
          try { csrfVal = decodeURIComponent(m2[1]); } catch(e) { csrfVal = m2[1]; }
        }
      }
      $('#csrf_token').val(csrfVal);
      $('#allcookies').val(cookiesVal ? cookiesVal : '');
      
      // enable Ajukan submit now that mapping data is present
      $('#submit-ajukan').prop('disabled', false);
    },
    error: function() { $('#bulan_a').html('<option>❌ Gagal ambil data</option>'); },
    complete: function() {
      $('#user_a').prop('disabled', false); // enable kembali setelah selesai
    }
  });
});

// submit ajukan: send selected id_penilaian (value of bulan_a) to tambakrenaksi.php
$(document).on('click', '#submit-ajukan', function(e){
  e.preventDefault();
  $("#alert-success").addClass("d-none"); // sembunyikan alert dulu
  var id_pen = $('#bulan_a').val();
  var idSkp = $('#id_skp_a').val();
  if (!id_pen || !idSkp) { showMessage('Pilih user dan bulan (id_penilaian) terlebih dahulu', 'warning', 6000); return; }
  $('#submit-ajukan').prop('disabled', true).text('Mengirim...');
   $('#user_a').prop('disabled', true);
    $('#bulan_a').prop('disabled', true);
     $('#kategori_a').prop('disabled', true);
     $('#unit_a').prop('disabled', true);
  // send single numeric id_penilaian (as a plain value) instead of a JSON array
  var payload = $.param({ tupoksi: id_pen, bulan: '', id_skp: idSkp, username: $('#user_a').val(), csrf_token: $('#csrf_token').val(), allcookies: $('#allcookies').val() });
  $.ajax({ url: 'ajukan.php', method: 'POST', data: payload, dataType: 'json',
    success: function(res){
      var ok = res && res.status==='success';
      var contentLen = 0;
      if (typeof res === 'string') contentLen = res.length;
      else if (typeof res === 'object') contentLen = JSON.stringify(res).length;
      if (ok || contentLen > 500) {
        showSuccessAlert('Rencana Aksi Berhasil Diajukan');
      } else {
        showMessage('Ajukan gagal: ' + JSON.stringify(res), 'danger', 8000);
      }
    },
  error: function(xhr, st, err){ showMessage('Request error: '+st + (xhr.responseText? '\n'+xhr.responseText : ''), 'danger', 8000); },
    complete: function(){ 
      $('#submit-ajukan').prop('disabled', false).text('Ajukan'); 
      $('#user_a').prop('disabled', false);
      $('#kategori_a').prop('disabled', false);
      $('#bulan_a').prop('disabled', false);
      $('#unit_a').prop('disabled', false);
    }
  });
});

// ===== Handlers for Preview tab (unit_p/kategori_p/user_p/bulan_p) =====
// populate kategori_p when unit_p changes
$(document).on('change', '#unit_p', function() {
  var unit = $(this).val();
  $('#kategori_p').html('<option value="">-- Pilih Kategori --</option>');
  $('#user_p').html('<option value="">-- Pilih User --</option>').prop('disabled', true);
  $('#bulan_p').html('<option value="">-- Pilih Bulan untuk Preview --</option>').prop('disabled', true);
  $('#btn-preview').prop('disabled', true);
  if (unit && dataUser[unit]) {
    Object.keys(dataUser[unit]).forEach(function(k){ $('#kategori_p').append('<option value="'+k+'">'+k+'</option>'); });
    $('#kategori_p').prop('disabled', false);
  }
});

// populate users when kategori_p changes
$(document).on('change', '#kategori_p', function() {
  var unit = $('#unit_p').val();
  var kategori = $(this).val();
  $('#user_p').html('<option value="">-- Pilih User --</option>');
  $('#bulan_p').html('<option value="">-- Pilih Bulan untuk Preview --</option>').prop('disabled', true);
  $('#btn-preview').prop('disabled', true);
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(function(nama){
      var u = dataUser[unit][kategori][nama];
      $('#user_p').append('<option value="'+u.username+'">'+nama+'</option>');
    });
    $('#user_p').prop('disabled', false);
  }
});

// when a user_p is selected, call get_mapping.php and populate bulan_p with available months
$(document).on('change', '#user_p', function() {
  var username = $(this).val();
  var unit = $('#unit_p').val();
  var kategori = $('#kategori_p').val();
  var password = '';
  if (unit && kategori && dataUser[unit][kategori]) {
    Object.keys(dataUser[unit][kategori]).forEach(function(nama){
      var u = dataUser[unit][kategori][nama]; 
      if (u.username == username) password = u.password;
    });
  }

  // Reset preview data
  window.previewBulanData = {};
  window.previewJSResult = null;
  $('#preview-results-container').hide();

  $('#bulan_p').html('<option>🔄 Loading data bulan...</option>');
  $('#user_p').prop('disabled', true); // Disable saat loading
  $('#btn-preview').prop('disabled', true);

  $.ajax({
    url: 'get_mapping.php', method: 'POST', contentType: 'text/plain; charset=UTF-8', dataType: 'json',
    data: username + ' ' + password,
    success: function(res) {
      $('#bulan_p').html('<option value="">-- Pilih Bulan untuk Preview --</option>');
      
      var ok = res && (res.success === true || (res.status && String(res.status).toLowerCase()==='success'));
      if (!ok) { 
        $('#bulan_p').html('<option>❌ Gagal ambil data</option>'); 
        return; 
      }

      // Process raw response using JavaScript
      if (!res.raw_response) {
        console.error('❌ [PREVIEW] No raw_response found in API response');
        $('#bulan_p').html('<option>❌ Raw response tidak tersedia</option>');
        return;
      }
      
      var jsResult = processRawResponseJS(res.raw_response);
      console.log('Preview - JS Processing Result:', jsResult);
      
      if (jsResult.success && jsResult.mappingBulanId) {
        // Populate dropdown dengan semua bulan yang tersedia (sudah diisi, belum diisi, dll)
        var addedPreview = false;
        console.log('📅 Available months for preview:', Object.keys(jsResult.mappingBulanId));
        
        for (let bulanLabel in jsResult.mappingBulanId) {
          let ids = jsResult.mappingBulanId[bulanLabel];
          if (!Array.isArray(ids) || ids.length === 0) {
            console.warn('⚠️ Skipping month with invalid IDs:', bulanLabel, ids);
            continue;
          }
          
          // Create data object and store in a simpler format
          let bulanData = {
            ids: ids,
            label: bulanLabel
          };
          
          // Use a simpler approach - store data in a global object and use index as value
          if (!window.previewBulanData) {
            window.previewBulanData = {};
          }
          let dataKey = 'month_' + Object.keys(window.previewBulanData).length;
          window.previewBulanData[dataKey] = bulanData;
          
          console.log('📝 Adding month option:', bulanLabel, 'with key:', dataKey);
          
          $('#bulan_p').append('<option value="'+dataKey+'">'+bulanLabel+'</option>');
          addedPreview = true;
        }
        
        if (!addedPreview) {
          $('#bulan_p').html('<option value="" disabled>-- Tidak ada data bulan tersedia --</option>');
        } else {
          $('#bulan_p').prop('disabled', false);
        }
      } else {
        console.error('❌ JS processing failed or no mapping data:', jsResult);
        $('#bulan_p').html('<option>❌ JavaScript processing gagal</option>');
      }
      
      // Store JS results untuk digunakan saat preview
      window.previewJSResult = jsResult;
      
      // Display hasil di console untuk monitoring preview data
      if (jsResult && jsResult.success) {
        console.log('=== PREVIEW RENAKSI - DATA SIAP ===');
        console.log('📊 Summary Data Preview:');
        console.log('- Jumlah Indikator Kinerja Unik:', jsResult.jumlahUnikIndikatorKinerja || 0);
        console.log('- Jumlah Mapping Tupoksi:', jsResult.mappingTupoksi ? jsResult.mappingTupoksi.length : 0);
        console.log('- Jumlah Mapping Bulan:', jsResult.mappingBulanId ? Object.keys(jsResult.mappingBulanId).length : 0);
        console.log('- Jumlah Renaksi Data:', jsResult.renaksiBulanId ? jsResult.renaksiBulanId.length : 0);
        
        console.log('\n📅 Mapping Bulan dengan Status (Detail):');
        if (jsResult.mappingBulanId && Object.keys(jsResult.mappingBulanId).length > 0) {
          for (let bulan in jsResult.mappingBulanId) {
            let ids = jsResult.mappingBulanId[bulan];
            console.log(`  ${bulan}: ${ids.length} items`);
            console.log(`    Sample IDs: [${ids.slice(0, 5).join(', ')}${ids.length > 5 ? '...' : ''}]`);
          }
        } else {
          console.log('  Tidak ada data mapping bulan');
        }
        
        console.log('\n📋 Mapping Tupoksi (Sample):');
        if (jsResult.mappingTupoksi && jsResult.mappingTupoksi.length > 0) {
          jsResult.mappingTupoksi.slice(0, 3).forEach((tupoksi, index) => {
            console.log(`  ${index + 1}. ${tupoksi.substring(0, 80)}${tupoksi.length > 80 ? '...' : ''}`);
          });
          if (jsResult.mappingTupoksi.length > 3) {
            console.log(`  ... dan ${jsResult.mappingTupoksi.length - 3} tupoksi lainnya`);
          }
        } else {
          console.log('  Tidak ada data mapping tupoksi');
        }
        
        console.log('\n📝 Renaksi Data (Sample):');
        if (jsResult.renaksiBulanId && jsResult.renaksiBulanId.length > 0) {
          jsResult.renaksiBulanId.slice(0, 3).forEach((renaksi, index) => {
            console.log(`  ${index + 1}. ${renaksi.substring(0, 100)}${renaksi.length > 100 ? '...' : ''}`);
          });
          if (jsResult.renaksiBulanId.length > 3) {
            console.log(`  ... dan ${jsResult.renaksiBulanId.length - 3} renaksi lainnya`);
          }
        } else {
          console.log('  Tidak ada data renaksi');
        }
        
        console.log('\n🔍 Full Data Object (untuk debugging detail):');
        console.log('window.previewJSResult =', jsResult);
        console.log('===== END PREVIEW RENAKSI DATA =====\n');
      }
    },
    error: function() { $('#bulan_p').html('<option>❌ Gagal ambil data</option>'); },
    complete: function() {
      $('#user_p').prop('disabled', false);
    }
  });
});

// Enable preview button when bulan is selected
$(document).on('change', '#bulan_p', function() {
  var bulanVal = $(this).val();
  $('#btn-preview').prop('disabled', !bulanVal);
});

// Handle preview button click
$(document).on('click', '#btn-preview', function(e) {
  e.preventDefault();
  
  var bulanVal = $('#bulan_p').val();
  if (!bulanVal) {
    showMessage('Pilih bulan terlebih dahulu', 'warning', 3000);
    return;
  }
  
  // Set loading state
  var $btn = $(this);
  var originalText = $btn.html();
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memuat Preview...');
  
  console.log('🔍 Preview button clicked. Bulan value:', bulanVal);
  
  try {
    var bulanData = null;
    var selectedIds = [];
    var bulanLabel = '';
    
    // Check if using new format (data key)
    if (bulanVal.startsWith('month_') && window.previewBulanData && window.previewBulanData[bulanVal]) {
      bulanData = window.previewBulanData[bulanVal];
      selectedIds = bulanData.ids || [];
      bulanLabel = bulanData.label || 'Unknown';
      console.log('✅ Using stored data:', bulanData);
    } else {
      // Fallback to old JSON parsing
      var decodedVal = bulanVal.replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
      console.log('📝 Trying to decode as JSON:', decodedVal);
      
      try {
        bulanData = JSON.parse(decodedVal);
        selectedIds = bulanData.ids || [];
        bulanLabel = bulanData.label || 'Unknown';
        console.log('✅ Parsed as JSON:', bulanData);
      } catch (jsonError) {
        console.log('⚠️ JSON parsing failed, using fallback');
        selectedIds = [bulanVal];
        bulanLabel = $('#bulan_p option:selected').text() || 'Unknown';
      }
    }
    
    if (!window.previewJSResult) {
      showMessage('Data belum tersedia. Pilih user terlebih dahulu.', 'warning', 3000);
      return;
    }
    
    console.log('📊 Processing preview with IDs:', selectedIds, 'Label:', bulanLabel);
    
    // Generate preview content dengan timeout untuk loading simulation
    setTimeout(function() {
      try {
        generatePreviewContent(selectedIds, bulanLabel, window.previewJSResult);
        
        // Reset button state setelah preview selesai
        $btn.prop('disabled', false).html(originalText);
      } catch (error) {
        console.error('❌ Error in generatePreviewContent:', error);
        showMessage('Error saat memuat preview: ' + error.message, 'danger', 5000);
        
        // Reset button state jika error
        $btn.prop('disabled', false).html(originalText);
      }
    }, 800); // Loading simulation
    
  } catch (e) {
    console.error('❌ Error processing bulan data:', e);
    console.error('Raw bulan value:', bulanVal);
    showMessage('Error memproses data bulan: ' + e.message, 'danger', 5000);
    
    // Reset button state jika error
    $btn.prop('disabled', false).html(originalText);
  }
});

// Function to generate preview content
function generatePreviewContent(selectedIds, bulanLabel, jsResult) {
  console.log('🎬 Generating preview content for:', bulanLabel, 'IDs:', selectedIds);
  
  // Console logging untuk monitor preview generation
  console.log('=== PREVIEW RENAKSI - GENERATE CONTENT ===');
  console.log('📅 Bulan yang dipilih:', bulanLabel);
  console.log('🔢 Jumlah ID yang difilter:', selectedIds.length);
  console.log('📋 Sample IDs yang akan ditampilkan:', selectedIds.slice(0, 5));
  if (selectedIds.length > 5) {
    console.log('   ... dan', selectedIds.length - 5, 'ID lainnya');
  }
  console.log('📊 Data tersedia di jsResult:', jsResult ? 'Ya' : 'Tidak');
  if (jsResult) {
    console.log('   - Total renaksi data:', jsResult.renaksiBulanId ? jsResult.renaksiBulanId.length : 0);
    console.log('   - Total mapping tupoksi:', jsResult.mappingTupoksi ? jsResult.mappingTupoksi.length : 0);
  }
  console.log('=======================================');
  
  try {
      var html = '';
      
      // Validate inputs
      if (!Array.isArray(selectedIds)) {
        throw new Error('selectedIds is not an array: ' + typeof selectedIds);
      }
      
      if (!jsResult || !jsResult.indikatorData) {
        throw new Error('Invalid jsResult or missing indikatorData');
      }
      
      // Header info
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<div class="alert alert-info border-info bg-dark text-light">';
      html += '<h5 class="text-info"><i class="fas fa-calendar me-2"></i>' + (bulanLabel || 'Unknown Month') + '</h5>';
      html += '<p class="mb-0 text-light">Jumlah Indikator: ' + selectedIds.length + '</p>';
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      // Find matching data from JS results
      var previewData = [];
      if (jsResult.indikatorData && Array.isArray(jsResult.indikatorData)) {
        console.log('🔍 Searching through', jsResult.indikatorData.length, 'indikator items');
        
        selectedIds.forEach(function(id) {
          var matchingItem = jsResult.indikatorData.find(function(item) {
            return item.id == id || item.id === id;
          });
          if (matchingItem) {
            previewData.push(matchingItem);
            console.log('✅ Found matching item for ID', id, ':', matchingItem.indikator_kinerja);
          } else {
            console.warn('⚠️ No matching item found for ID:', id);
          }
        });
      } else {
        console.error('❌ indikatorData is not available or not an array');
      }
      
      console.log('📊 Preview data collected:', previewData.length, 'items');
      
      if (previewData.length > 0) {
        // Summary statistics with better styling - moved to top
        var itemsWithRealisasi = previewData.filter(function(item) { return (parseFloat(item.realisasi) || 0) > 0; }).length;
        var itemsWithRenaksi = previewData.filter(function(item) { return item.renaksi && item.renaksi !== 'Belum diisi'; }).length;
        
        // Determine month status based on mapping bulan logic (same as existing logic)
        var statusValues = previewData.map(function(item) { return item.status || 0; });
        var maxStatus = Math.max(...statusValues);
        
        var monthStatus, monthStatusClass, monthStatusIcon;
        if (maxStatus === 2 || statusValues.includes(2)) {
          monthStatus = 'Sudah Di Nilai';
          monthStatusClass = 'bg-success';
          monthStatusIcon = 'fas fa-award';
        } else if (maxStatus === 1 || statusValues.includes(1)) {
          monthStatus = 'Sudah di Ajukan';
          monthStatusClass = 'bg-info';
          monthStatusIcon = 'fas fa-paper-plane';
        } else {
          // Status 0 - check realisasi for more detailed status
          var hasRealisasi = previewData.some(function(item) { return (parseFloat(item.realisasi) || 0) > 0; });
          if (hasRealisasi) {
            monthStatus = 'Sudah diisi';
            monthStatusClass = 'bg-primary';
            monthStatusIcon = 'fas fa-edit';
          } else {
            monthStatus = 'Belum diisi';
            monthStatusClass = 'bg-warning';
            monthStatusIcon = 'fas fa-clock';
          }
        }
        
        html += '<div class="row mb-4 g-3">';
        html += '<div class="col-md-4">';
        html += '<div class="card ' + monthStatusClass + ' text-white shadow-sm h-100 summary-card">';
        html += '<div class="card-body text-center d-flex flex-column justify-content-center">';
        html += '<i class="' + monthStatusIcon + ' fa-2x mb-2"></i>';
        html += '<h6 class="card-title mb-2">Status Bulan</h6>';
        html += '<h4 class="mb-0">' + monthStatus + '</h4>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-4">';
        html += '<div class="card bg-success text-white shadow-sm h-100 summary-card">';
        html += '<div class="card-body text-center d-flex flex-column justify-content-center">';
        html += '<i class="fas fa-check-circle fa-2x mb-2"></i>';
        html += '<h6 class="card-title mb-2">Sudah Diisi</h6>';
        html += '<h4 class="mb-0">' + itemsWithRealisasi + ' / ' + previewData.length + '</h4>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-4">';
        html += '<div class="card bg-info text-white shadow-sm h-100 summary-card">';
        html += '<div class="card-body text-center d-flex flex-column justify-content-center">';
        html += '<i class="fas fa-clipboard-list fa-2x mb-2"></i>';
        html += '<h6 class="card-title mb-2">Ada Rencana Aksi</h6>';
        html += '<h4 class="mb-0">' + itemsWithRenaksi + ' / ' + previewData.length + '</h4>';
        html += '</div></div></div>';
        html += '</div>';
        
        html += '<div class="table-responsive shadow-sm rounded">';
        html += '<table class="table table-striped table-hover preview-table mb-0">';
        html += '<thead class="table-dark">';
        html += '<tr>';
        html += '<th style="width: 5%;" class="text-center">#</th>';
        html += '<th style="width: 30%;">Indikator Kinerja</th>';
        html += '<th style="width: 25%;">Rencana Aksi</th>';
        html += '<th style="width: 10%;" class="text-center">Realisasi</th>';
        html += '<th style="width: 20%;">Bukti Dukung</th>';
        html += '<th style="width: 10%;" class="text-center">Status</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        previewData.forEach(function(item, index) {
          var realisasi = item.realisasi || 0;
          var renaksi = item.renaksi || 'Belum diisi';
          var indikator = item.indikator_kinerja || 'N/A';
          var buktiDukung = item.bukti_dukung || 'Belum ada';
          
          // Determine status based on realisasi
          var statusClass = 'bg-warning';
          var statusText = 'Belum ada';
          if (realisasi > 0) {
            statusClass = 'bg-success';
            statusText = 'Sudah diisi';
          } else if (renaksi && renaksi !== 'Belum diisi') {
            statusClass = 'bg-success';
            statusText = 'Renaksi ada';
          }
          
          html += '<tr>';
          html += '<td class="text-center fw-bold text-primary">' + (index + 1) + '</td>';
          html += '<td><div class="text-content fw-medium text-light">' + indikator + '</div></td>';
          html += '<td><div class="text-content text-muted">' + renaksi + '</div></td>';
          html += '<td class="text-center text-white fw-bold">' + realisasi + '</td>';
          html += '<td><div class="text-content text-info">' + buktiDukung + '</div></td>';
          html += '<td class="text-center"><span class="badge ' + statusClass + ' status-badge text-wrap">' + statusText + '</span></td>';
          html += '</tr>';
        });
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        
      } else {
        html += '<div class="alert alert-warning border-warning bg-dark text-light">';
        html += '<h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Tidak ada data</h6>';
        html += '<p class="mb-0 text-light">Tidak ditemukan data indikator untuk bulan ini. IDs yang dicari: ' + selectedIds.join(', ') + '</p>';
        html += '</div>';
      }
      
      $('#preview-content').html(html);
      $('#preview-results-container').slideDown();
      
      // Pastikan dropdown user_p tetap aktif setelah preview ditampilkan
      $('#user_p').prop('disabled', false);
      
      showMessage('Preview berhasil dimuat!', 'success', 3000);
      
    } catch (error) {
      console.error('❌ Error in generatePreviewContent:', error);
      $('#preview-content').html('<div class="alert alert-danger border-danger bg-dark text-light"><h6 class="text-danger">Error</h6><p class="text-light mb-0">Terjadi kesalahan: ' + error.message + '</p></div>');
      $('#preview-results-container').slideDown();
      
      // Pastikan dropdown user_p tetap aktif bahkan saat error
      $('#user_p').prop('disabled', false);
      
      // Throw error untuk di-handle di caller
      throw error;
    }
}

// ========= SIMPLE UI ENHANCEMENTS =========

$(document).ready(function() {
  // Simple form validation feedback
  $('.form-select, .form-control').on('invalid', function() {
    $(this).addClass('is-invalid');
    setTimeout(() => $(this).removeClass('is-invalid'), 3000);
  });
  
  // Simple form styling enhancements
  $(document).on('focus', '.form-select, .form-control', function() {
    $(this).closest('.col-12').find('.form-label').addClass('label-focused');
  });
  
  $(document).on('blur', '.form-select, .form-control', function() {
    $(this).closest('.col-12').find('.form-label').removeClass('label-focused');
  });
});

</script>

<!-- Simple Footer -->
<footer class="container-fluid mt-5 py-4">
  <div class="text-center">
    <hr style="background: #404040; border: none; height: 1px; margin: 2rem 0;">
    <div class="footer-content">
      <div class="mb-2">
        <i class="fas fa-code me-2" style="color: #8bc88c;"></i>
        <span style="font-weight: 500; color: #e0e0e0;">Design & Created By JMT</span>
      </div>
      <div style="font-size: 0.85rem; color: #999;">
        <i class="fas fa-calendar me-1"></i>©Copyright 2025
      </div>
    </div>
  </div>
</footer>
</body>
</html>