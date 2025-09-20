import React, { useState } from 'react';
import { API_ENDPOINTS } from '../config/api';

const IsiComponent = ({ 
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
      unit: unit,
      kategori: '',
      user: '',
      bulan: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan Kategori  
  const handleKategoriChange = (e) => {
    const kategori = e.target.value;
    setFormData(prev => ({
      ...prev,
      kategori: kategori,
      user: '',
      bulan: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan User
  const handleUserChange = async (e) => {
    const username = e.target.value;
    console.log('🔄 [ISI] handleUserChange called with username:', username);
    
    setFormData(prev => ({
      ...prev,
      user: username,
      bulan: ''
    }));
    
    if (!username) {
      console.log('⚠️ [ISI] No username provided, clearing options');
      setBulanOptions([]);
      return;
    }

    // Cari password dari dataUser
    const { unit, kategori } = formData;
    let password = '';
    if (unit && kategori && dataUser[unit]?.[kategori]) {
      Object.keys(dataUser[unit][kategori]).forEach(nama => {
        const u = dataUser[unit][kategori][nama];
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
      console.log('📡 [ISI] GET_MAPPING API Response:', {
        success: res?.success,
        status: res?.status,
        hasRawResponse: !!res?.raw_response,
        rawResponseLength: res?.raw_response?.length || 0,
        hasIdSkp: !!res?.id_skp,
        hasCsrfToken: !!res?.csrf_token,
        hasAllCookies: !!res?.allCookies
      });
      
      const isSuccess = res && (res.success === true || res.status?.toLowerCase() === 'success');
      
      if (isSuccess) {
        // Set basic data dari API response
        let csrfVal = res.csrf_token || '';
        let cookiesVal = res.allCookies || '';

        // fallback: extract XSRF-TOKEN from cookies if csrf empty
        if ((!csrfVal || csrfVal === '') && cookiesVal) {
          const match = cookiesVal.match(/XSRF-TOKEN=([^;]+)/i);
          if (match) {
            try {
              csrfVal = decodeURIComponent(match[1]); 
            } catch { 
              csrfVal = match[1]; 
            }
          }
        }

        setFormData(prev => ({
          ...prev,
          csrf_token: csrfVal,
          allcookies: cookiesVal
        }));

        // Process raw response dengan JavaScript
        if (res.raw_response) {
          console.log('🔍 [ISI] Processing raw response...');
          console.log('📄 [ISI] Raw Response Data:', res.raw_response.substring(0, 500) + '...');
          const jsResult = processRawResponseJS(res.raw_response);
          console.log('✅ [ISI] Parsing completed, result:', jsResult);
          
          // 📊 LOG: Monitor JavaScript Processing Results
          console.log('📊 [ISI] JavaScript Processing Results:', {
            success: jsResult.success,
            hasIndikatorData: !!jsResult.indikatorData,
            indikatorDataLength: jsResult.indikatorData?.length || 0,
            hasPenilaiaanArr: !!jsResult.penilaiaanArr,
            penilaiaanArrLength: jsResult.penilaiaanArr?.length || 0,
            hasRhkIndikator: !!jsResult.rhkIndikator,
            rhkIndikatorLength: jsResult.rhkIndikator?.length || 0,
            jumlahUnikIndikatorKinerja: jsResult.jumlahUnikIndikatorKinerja || 0,
            mappingTupoksiLength: jsResult.mappingTupoksi?.length || 0,
            mappingBulanIdKeys: jsResult.mappingBulanId ? Object.keys(jsResult.mappingBulanId) : [],
            renaksiBulanIdLength: jsResult.renaksiBulanId?.length || 0,
            buktiDukung: jsResult.buktiDukung || 'N/A'
          });
          
          if (jsResult.success) {
            // Populate bulan dropdown dari JavaScript result
            const options = [];
            if (jsResult.mappingBulanId) {
              for (let bulan in jsResult.mappingBulanId) {
                const canSelect = bulan.includes('Sudah diisi') || bulan.includes('Belum diisi');
                
                // Buat array id yang diurutkan sesuai dengan mappingTupoksi
                const bulanIds = jsResult.mappingBulanId[bulan];
                const bulanIdIndikators = jsResult.mappingBulanIdIndikator[bulan];
                
                let orderedIds = [];
                if (jsResult.mappingTupoksi && bulanIdIndikators) {
                  jsResult.mappingTupoksi.forEach(idIndikator => {
                    const index = bulanIdIndikators.indexOf(idIndikator);
                    if (index !== -1 && bulanIds[index]) {
                      orderedIds.push(bulanIds[index]);
                    }
                  });
                }
                
                options.push({
                  value: JSON.stringify(orderedIds),
                  label: bulan,
                  disabled: !canSelect
                });
              }
            }
            setBulanOptions(options);
            
            // Set hidden inputs dari JavaScript result
            setFormData(prev => ({
              ...prev,
              jumlahUnikIndikatorKinerja: jsResult.jumlahUnikIndikatorKinerja || '',
              bukti_dukung: jsResult.buktiDukung || '',
              rhk: jsResult.renaksiBulanId ? JSON.stringify(jsResult.renaksiBulanId) : ''
            }));
            
          } else {
            setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
          }
        } else {
          setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
        }
      } else {
        setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
      }
    } catch (error) {
      console.error('Error fetching mapping:', error);
      setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
    } finally {
      setIsLoadingBulan(false);
    }
  };

  // Handler untuk perubahan Bulan
  const handleBulanChange = (e) => {
    setFormData(prev => ({
      ...prev,
      bulan: e.target.value
    }));
  };

  // Handler untuk submit form
  const handleSubmit = (e) => {
    e.preventDefault();
    
    const payload = new FormData();
    payload.append('allcookies', formData.allcookies || '');
    payload.append('csrf_token', formData.csrf_token || '');
    payload.append('jumlahUnikIndikatorKinerja', formData.jumlahUnikIndikatorKinerja || '');
    payload.append('bukti_dukung', formData.bukti_dukung || '');
    payload.append('rhk', formData.rhk || '');
    payload.append('cookie', formData.user);
    payload.append('id_indikator', formData.bulan);

    onSubmit(payload);
  };

  // Fungsi untuk memproses raw response (sama seperti di PHP)
  const processRawResponseJS = (rawResponse) => {
    try {
      // Validasi input
      if (!rawResponse || typeof rawResponse !== 'string') {
        console.error('Raw response validation failed:', typeof rawResponse, rawResponse);
        throw new Error('Raw response is null, undefined, or not a string. Type: ' + typeof rawResponse);
      }
      
      // 🔍 LOG: Start raw response processing
      console.log('🔄 [ISI] Starting raw response processing...', {
        responseLength: rawResponse.length,
        firstChars: rawResponse.substring(0, 200)
      });
      
      // Extract penilaiaan_indikator
      const indikatorMatch = rawResponse.match(/var\s+penilaiaan_indikator\s*=\s*(\[[\s\S]*?\]);/i);
      let indikatorData = null;
      let jumlahUnikIndikatorKinerja = 0;

      if (indikatorMatch) {
        try {
          indikatorData = JSON.parse(indikatorMatch[1]);
          
          // 📊 LOG: Extracted penilaiaan_indikator
          console.log('✅ [ISI] Extracted penilaiaan_indikator:', {
            found: true,
            dataType: Array.isArray(indikatorData) ? 'array' : typeof indikatorData,
            length: indikatorData?.length || 0,
            sampleItem: indikatorData?.[0] ? {
              id: indikatorData[0].id,
              indikator_kinerja: indikatorData[0].indikator_kinerja?.substring(0, 50) + '...',
              hasRealisasi: !!indikatorData[0].realisasi,
              hasRenaksi: !!indikatorData[0].renaksi
            } : null
          });
          
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
          
          // 📊 LOG: Processed indikator data
          console.log('📈 [ISI] Processed indikator data:', {
            totalItems: indikatorData.length,
            allIdIndikatorCount: allIndikatorKinerja.length,
            uniqueIdIndikatorCount: jumlahUnikIndikatorKinerja,
            uniqueIds: uniqueIndikatorKinerja.slice(0, 10) // Show first 10 unique IDs
          });
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
          
          // 📊 LOG: Extracted penilaiaan
          console.log('✅ [ISI] Extracted penilaiaan:', {
            found: true,
            dataType: Array.isArray(penilaiaanArr) ? 'array' : typeof penilaiaanArr,
            length: penilaiaanArr?.length || 0,
            sampleItem: penilaiaanArr?.[0] ? {
              id: penilaiaanArr[0].id,
              bulan: penilaiaanArr[0].bulan,
              status: penilaiaanArr[0].status
            } : null
          });
        } catch (e) {
          console.error('Error parsing penilaiaan JSON:', e);
        }
      } else {
        console.warn('⚠️ [ISI] No penilaiaan found in response');
      }

      // Extract rkh_indikator
      const rhkMatch = rawResponse.match(/var\s+rkh_indikator\s*=\s*(\[[\s\S]*?\]);/i);
      let rhkIndikator = null;
      let mappingTupoksi = [];
      if (rhkMatch) {
        try {
          rhkIndikator = JSON.parse(rhkMatch[1]);
          if (Array.isArray(rhkIndikator)) {
            rhkIndikator.forEach(item => {
              if (item.id_indikator) {
                mappingTupoksi.push(item.id_indikator);
              }
            });
            
            // 📊 LOG: Extracted rkh_indikator
            console.log('✅ [ISI] Extracted rkh_indikator:', {
              found: true,
              rhkLength: rhkIndikator.length,
              mappingTupoksiLength: mappingTupoksi.length,
              sampleRhkItem: rhkIndikator[0] ? {
                id_indikator: rhkIndikator[0].id_indikator,
                hasOrder: !!rhkIndikator[0].order
              } : null
            });
          }
        } catch (e) {
          console.error('Error parsing RHK JSON:', e);
        }
      } else {
        console.warn('⚠️ [ISI] No rkh_indikator found in response');
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
        
        // Debug: Renaksi order processing completed
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
        renaksiBulanId,
        buktiDukung,
        
        success: true
      };

      // JavaScript processing completed
      
      return result;

    } catch (error) {
      console.error('JavaScript Processing Error:', error);
      return { success: false, error: error.message };
    }
  };

  return (
        <div id="form-isi" style={{width: '100%'}}>
      <div style={{width: '100%', margin: '0 auto'}}>
        <div className="text-center mb-4">
          <h2>
            <i className="fas fa-edit me-3"></i>
            <strong>Isi Rencana Aksi</strong>
          </h2>
          <p className="lead">
            <i className="fas fa-exclamation-triangle me-2"></i>
            Pastikan rencana aksi telah ditambahkan sebelum mengisi
          </p>
        </div>
        
        <div className="card p-4 mb-4">
          <form onSubmit={handleSubmit} autoComplete="off">
            <input type="hidden" value={formData.allcookies || ''} />
            <input type="hidden" value={formData.csrf_token || ''} />
            <input type="hidden" value={formData.jumlahUnikIndikatorKinerja || ''} />
            <input type="hidden" value={formData.bukti_dukung || ''} />
            <input type="hidden" value={formData.rhk || ''} />
            
            <div className="row g-3 justify-content-center">
              <div className="col-12">
                <label htmlFor="unit" className="form-label">
                  <i className="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select 
                  id="unit" 
                  className="form-select"
                  value={formData.unit || ''}
                  onChange={handleUnitChange}
                  required
                >
                  <option value="" disabled>-- Pilih Unit --</option>
                  {Object.keys(dataUser).map(unit => (
                    <option key={unit} value={unit}>{unit}</option>
                  ))}
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="kategori" className="form-label">
                  <i className="fas fa-tags me-2"></i>Kategori
                </label>
                <select 
                  id="kategori" 
                  className="form-select" 
                  disabled={!formData.unit}
                  value={formData.kategori || ''}
                  onChange={handleKategoriChange}
                  required
                >
                  <option value="">-- Pilih Kategori --</option>
                  {formData.unit && dataUser[formData.unit] && 
                    Object.keys(dataUser[formData.unit]).map(kategori => (
                      <option key={kategori} value={kategori}>{kategori}</option>
                    ))
                  }
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="user" className="form-label">
                  <i className="fas fa-user me-2"></i>Pengguna
                </label>
                <select 
                  id="user" 
                  className="form-select" 
                  disabled={!formData.kategori}
                  value={formData.user || ''}
                  onChange={handleUserChange}
                  required
                >
                  <option value="">-- Pilih User --</option>
                  {formData.unit && formData.kategori && 
                    dataUser[formData.unit]?.[formData.kategori] &&
                    Object.keys(dataUser[formData.unit][formData.kategori]).map(nama => {
                      const userObj = dataUser[formData.unit][formData.kategori][nama];
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
                <label htmlFor="bulan" className="form-label">
                  <i className="fas fa-calendar me-2"></i>Bulan
                </label>
                <select 
                  id="bulan" 
                  className="form-select" 
                  disabled={!formData.user || isLoadingBulan}
                  value={formData.bulan || ''}
                  onChange={handleBulanChange}
                  required
                >
                  <option value="">
                    {isLoadingBulan ? 'Loading...' : '-- Pilih Bulan --'}
                  </option>
                  {bulanOptions.map((option, index) => (
                    <option 
                      key={index} 
                      value={option.value}
                      disabled={option.disabled}
                    >
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
                disabled={!formData.user || !formData.bulan || isLoading}
              >
                <i className="fas fa-save me-2"></i>
                {isLoading ? 'Menyimpan...' : 'Simpan Rencana Aksi'}
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

export default IsiComponent;