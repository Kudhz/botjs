import React, { useState } from 'react';
import { API_ENDPOINTS } from '../config/api';
import { notifyError } from '../utils/notifications';

const TambahComponent = ({ 
  dataUser, 
  onSubmit, 
  isLoading,
  formData,
  setFormData,
  message
}) => {
  const [bulanOptions, setBulanOptions] = useState([]);
  const [isLoadingBulan, setIsLoadingBulan] = useState(false);

  // Handler untuk perubahan Unit
  const handleUnitChange = (e) => {
    const unit = e.target.value;
    setFormData(prev => ({
      ...prev,
      unit_t: unit,
      kategori_t: '',
      user_t: '',
      bulan_t: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan Kategori  
  const handleKategoriChange = (e) => {
    const kategori = e.target.value;
    setFormData(prev => ({
      ...prev,
      kategori_t: kategori,
      user_t: '',
      bulan_t: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan User
  const handleUserChange = async (e) => {
    const username = e.target.value;
    
    
    setFormData(prev => ({
      ...prev,
      user_t: username,
      bulan_t: ''
    }));
    
    if (!username) {
      
      setBulanOptions([]);
      return;
    }

    // Cari password dari dataUser
    const { unit_t, kategori_t } = formData;
    let password = '';
    if (unit_t && kategori_t && dataUser[unit_t]?.[kategori_t]) {
      Object.keys(dataUser[unit_t][kategori_t]).forEach(nama => {
        const u = dataUser[unit_t][kategori_t][nama];
        if (u.username === username) password = u.password;
      });
    }

    // Ambil mapping bulan
    setIsLoadingBulan(true);
    try {
      const response = await fetch(API_ENDPOINTS.GET_MAPPING, {
        method: 'POST',
        headers: {
          'Content-Type': 'text/plain; charset=UTF-8'
        },
        body: `${username} ${password}`
      });

      const res = await response.json();
      
      // 🔍 LOG: Monitor GET_MAPPING API Response
     
      
      const isSuccess = res && (res.success === true || res.status?.toLowerCase() === 'success');
      
      if (isSuccess) {
        // Set id_skp
        setFormData(prev => ({
          ...prev,
          id_skp_t: res.id_skp || '',
          csrf_token: res.csrf_token || '',
          allcookies: res.allCookies || ''
        }));

        // Process raw response
        if (res.raw_response) {
        //   console.log('🔍 [TAMBAH] Processing raw response...');
        //   console.log('📄 [TAMBAH] Raw Response Data:', res.raw_response.substring(0, 500) + '...');
          const jsResult = processRawResponseJS(res.raw_response);
          console.log('✅ [TAMBAH] Parsing completed, result:', jsResult);
          
          // 📊 LOG: Monitor JavaScript Processing Results
          
          
          if (jsResult.success) {
            // Populate bulan dropdown
            const monthOrder = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const monthMapNum = { 
              'Januari':1,'Februari':2,'Maret':3,'April':4,'Mei':5,'Juni':6,
              'Juli':7,'Agustus':8,'September':9,'Oktober':10,'November':11,'Desember':12 
            };
            
            const options = [];
            if (jsResult.mappingIdPenilaiaan1) {
              monthOrder.forEach(nm => {
                const val = jsResult.mappingIdPenilaiaan1[nm];
                if (val) {
                  const bulanNum = monthMapNum[nm];
                  options.push({ value: bulanNum, label: nm });
                }
              });
            }
            
            setBulanOptions(options);
            
            // Set tupoksi
            setFormData(prev => ({
              ...prev,
              tupoksi: JSON.stringify(jsResult.mappingTupoksi || [])
            }));
          } else {
            // JS processing failed
            console.warn('JavaScript processing failed:', jsResult.error);
            setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
          }
        } else {
          // No raw_response
          console.warn('No raw_response found in API response');
          setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
        }
      } else {
        // API request failed (sukses = false)
        console.warn('API request failed - sukses = false:', res);
        setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
      }
    } catch (error) {
      console.error('Error fetching mapping:', error);
    } finally {
      setIsLoadingBulan(false);
    }
  };

  // Handler untuk perubahan Bulan
  const handleBulanChange = (e) => {
    setFormData(prev => ({
      ...prev,
      bulan_t: e.target.value
    }));
  };

  // Handler untuk submit form
  const handleSubmit = (e) => {
    e.preventDefault();
    
    const { tupoksi, bulan_t, id_skp_t, user_t, csrf_token, allcookies } = formData;
    
    if (!tupoksi || !bulan_t || !id_skp_t) {
      notifyError('Pastikan Tupoksi, Bulan, dan ID SKP sudah terisi.');
      return;
    }

    const payload = {
      tupoksi,
      bulan: bulan_t,
      id_skp: id_skp_t,
      username: user_t,
      csrf_token,
      allcookies
    };

    onSubmit(payload);
  };

  // Fungsi untuk memproses raw response (sama seperti di PHP)
  const processRawResponseJS = (rawResponse) => {
    try {
      if (!rawResponse || typeof rawResponse !== 'string') {
        throw new Error('Raw response is null, undefined, or not a string');
      }
      
      // Extract penilaiaan_indikator
      const indikatorMatch = rawResponse.match(/var\s+penilaiaan_indikator\s*=\s*(\[[\s\S]*?\]);/i);
      let indikatorData = null;
      let jumlahUnikIndikatorKinerja = 0;

      if (indikatorMatch) {
        try {
          indikatorData = JSON.parse(indikatorMatch[1]);
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
          console.log('Unique indikator kinerja:', jumlahUnikIndikatorKinerja);
        } catch (e) {
          console.error('Error parsing indikator JSON:', e);
        }
      }

      // Extract penilaiaan
      const penilaiaanMatch = rawResponse.match(/var\s+penilaiaan\s*=\s*(\[[\s\S]*?\]);/i);
      let penilaiaanArr = null;
      if (penilaiaanMatch) {
        try {
          penilaiaanArr = JSON.parse(penilaiaanMatch[1]);
          // Found penilaiaan data
        } catch (e) {
          console.error('Error parsing penilaiaan JSON:', e);
        }
      }

      // Extract rkh_indikator
      const rhkMatch = rawResponse.match(/var\s+rkh_indikator\s*=\s*(\[[\s\S]*?\]);/i);
      let rhkIndikator = null;
      let mappingTupoksi = [];
      if (rhkMatch) {
        try {
          rhkIndikator = JSON.parse(rhkMatch[1]);
          // Found RHK indikator data
          if (Array.isArray(rhkIndikator)) {
            rhkIndikator.forEach(item => {
              if (item.id_indikator) {
                mappingTupoksi.push(item.id_indikator);
              }
            });
          }
        } catch (e) {
          console.error('Error parsing RHK JSON:', e);
        }
      }

      // Mapping bulan
      const bulan = {
        1: "Januari", 2: "Februari", 3: "Maret", 4: "April",
        5: "Mei", 6: "Juni", 7: "Juli", 8: "Agustus",
        9: "September", 10: "Oktober", 11: "November", 12: "Desember"
      };

      // Generate mapping id_penilaian1 (bulan berikutnya)
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

      return {
        indikatorData,
        penilaiaanArr,
        rhkIndikator,
        jumlahUnikIndikatorKinerja,
        mappingTupoksi,
        mappingIdPenilaiaan1,
        success: true
      };

    } catch (error) {
      console.error('JavaScript Processing Error:', error);
      return { success: false, error: error.message };
    }
  };

  return (
    <div id="form-renaksi" style={{width: '100%'}}>
      <div style={{width: '100%', margin: '0 auto'}}>
        <div className="text-center mb-4">
          <h2>
            <i className="fas fa-plus-circle me-3"></i>
            <strong>Tambah Rencana Aksi</strong>
          </h2>
          <p className="lead">
            <i className="fas fa-info-circle me-2"></i>
            Bulan yang tersedia adalah bulan yang belum memiliki rencana aksi
          </p>
        </div>
        
        <div className="card p-4 mb-4">
          <form onSubmit={handleSubmit} autoComplete="off">
            <input type="hidden" value={formData.tupoksi || ''} />
            <input type="hidden" value={formData.id_skp_t || ''} />
            <input type="hidden" value="2025" />

            <div className="row g-3 justify-content-center">
              <div className="col-12">
                <label htmlFor="unit_t" className="form-label">
                  <i className="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select 
                  id="unit_t" 
                  className="form-select"
                  value={formData.unit_t || ''}
                  onChange={handleUnitChange}
                >
                  <option value="">-- Pilih Unit --</option>
                  {Object.keys(dataUser).map(unit => (
                    <option key={unit} value={unit}>{unit}</option>
                  ))}
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="kategori_t" className="form-label">
                  <i className="fas fa-tags me-2"></i>Kategori
                </label>
                <select 
                  id="kategori_t" 
                  className="form-select" 
                  disabled={!formData.unit_t}
                  value={formData.kategori_t || ''}
                  onChange={handleKategoriChange}
                >
                  <option value="">-- Pilih Kategori --</option>
                  {formData.unit_t && dataUser[formData.unit_t] && 
                    Object.keys(dataUser[formData.unit_t]).map(kategori => (
                      <option key={kategori} value={kategori}>{kategori}</option>
                    ))
                  }
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="user_t" className="form-label">
                  <i className="fas fa-user me-2"></i>Pengguna
                </label>
                <select 
                  id="user_t" 
                  className="form-select" 
                  disabled={!formData.kategori_t}
                  value={formData.user_t || ''}
                  onChange={handleUserChange}
                >
                  <option value="">-- Pilih User --</option>
                  {formData.unit_t && formData.kategori_t && 
                    dataUser[formData.unit_t]?.[formData.kategori_t] &&
                    Object.keys(dataUser[formData.unit_t][formData.kategori_t]).map(nama => {
                      const userObj = dataUser[formData.unit_t][formData.kategori_t][nama];
                      return (
                        <option key={userObj.username} value={userObj.username}>
                          {nama}
                        </option>
                      );
                    })
                  }
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="bulan_t" className="form-label">
                  <i className="fas fa-calendar me-2"></i>Bulan
                </label>
                <select 
                  id="bulan_t" 
                  className="form-select" 
                  disabled={!formData.user_t || isLoadingBulan}
                  value={formData.bulan_t || ''}
                  onChange={handleBulanChange}
                >
                  <option value="">
                    {isLoadingBulan ? 'Loading...' : '-- Pilih Bulan --'}
                  </option>
                  {bulanOptions.map(option => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            
            <div className="mt-4 text-center">
              <button 
                type="submit" 
                className="btn btn-success btn-lg px-5"
                disabled={!formData.user_t || !formData.bulan_t || isLoading}
              >
                <i className="fas fa-plus me-2"></i>
                {isLoading ? 'Mengirim...' : 'Tambah Rencana Aksi'}
              </button>
            </div>
          </form>
        </div>
        
        {message && (
          <div className={`alert ${message.type === 'success' ? 'alert-success' : 'alert-danger'} mt-3 text-center`}>
            {message.text}
          </div>
        )}
      </div>
    </div>
  );
};

export default TambahComponent;