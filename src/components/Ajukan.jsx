import React, { useState } from 'react';
import { API_ENDPOINTS } from '../config/api';
import { notifyError } from '../utils/notifications';

const AjukanComponent = ({ 
  dataUser, 
  onSubmit, 
  isLoading,
  formData,
  setFormData,
  message
}) => {
  console.log('🎯 [AJUKAN] Component rendered');
  const [bulanOptions, setBulanOptions] = useState([]);
  const [isLoadingBulan, setIsLoadingBulan] = useState(false);

  // Handler untuk perubahan Unit
  const handleUnitChange = (e) => {
    const unit = e.target.value;
    setFormData(prev => ({
      ...prev,
      unit_a: unit,
      kategori_a: '',
      user_a: '',
      bulan_a: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan Kategori  
  const handleKategoriChange = (e) => {
    const kategori = e.target.value;
    setFormData(prev => ({
      ...prev,
      kategori_a: kategori,
      user_a: '',
      bulan_a: ''
    }));
    setBulanOptions([]);
  };

  // Handler untuk perubahan User
  const handleUserChange = async (e) => {
    const username = e.target.value;
    console.log('🔄 [AJUKAN] handleUserChange called with username:', username);
    
    setFormData(prev => ({
      ...prev,
      user_a: username,
      bulan_a: ''
    }));
    
    if (!username) {
      console.log('⚠️ [AJUKAN] No username provided, clearing options');
      setBulanOptions([]);
      return;
    }

    // Cari password dari dataUser
    const { unit_a, kategori_a } = formData;
    console.log('🔍 [AJUKAN] Looking for password with:', { unit_a, kategori_a, username });
    
    let password = '';
    if (unit_a && kategori_a && dataUser[unit_a]?.[kategori_a]) {
      Object.keys(dataUser[unit_a][kategori_a]).forEach(nama => {
        const u = dataUser[unit_a][kategori_a][nama];
        if (u.username === username) password = u.password;
      });
    }
    
    console.log('🔑 [AJUKAN] Password found:', password ? 'Yes' : 'No');

    // Ambil mapping bulan
    console.log('📡 [AJUKAN] Starting GET_MAPPING request...');
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
      console.log('📡 [AJUKAN] GET_MAPPING API Response:', {
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
        // Process raw response using JavaScript
        if (!res.raw_response) {
          console.error('[AJUKAN] No raw_response found in API response. Response structure:', res);
          setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
          return;
        }
        
        console.log('🔍 [AJUKAN] Processing raw response...');
        console.log('📄 [AJUKAN] Raw Response Data:', res.raw_response.substring(0, 500) + '...');
        const jsResult = processRawResponseJS(res.raw_response);
        console.log('✅ [AJUKAN] Parsing completed, result:', jsResult);
        
        // 📊 LOG: Monitor JavaScript Processing Results
        console.log('📊 [AJUKAN] JavaScript Processing Results:', {
          success: jsResult.success,
          hasIndikatorData: !!jsResult.indikatorData,
          indikatorDataLength: jsResult.indikatorData?.length || 0,
          hasPenilaiaanArr: !!jsResult.penilaiaanArr,
          penilaiaanArrLength: jsResult.penilaiaanArr?.length || 0,
          hasMappingIdPenilaiaan: !!jsResult.mappingIdPenilaiaan,
          mappingIdPenilaiaanKeys: jsResult.mappingIdPenilaiaan ? Object.keys(jsResult.mappingIdPenilaiaan) : []
        });
        
        if (jsResult.success) {
          // Use JavaScript processed mappingIdPenilaiaan for dropdown population
          const idPenMap = jsResult.mappingIdPenilaiaan || {};
          const monthOrder = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
          
          // Ajukan should match Isi: only list months that have an id_penilaian
          const options = [];
          monthOrder.forEach(nm => {
            const val = idPenMap[nm];
            if (val) { // skip months without id_penilaian
              options.push({ value: val, label: nm });
            }
          });
          
          if (options.length === 0) {
            setBulanOptions([{value: '', label: '-- Tidak ada bulan yang dapat diajukan --', disabled: true}]);
          } else {
            setBulanOptions(options);
          }

          // Set values from minimal response
          const idskp = res.id_skp || '';
          setFormData(prev => ({
            ...prev,
            id_skp_a: idskp
          }));
          
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
        } else {
          console.error('JS processing failed:', jsResult);
          setBulanOptions([{value: '', label: '❌ Gagal ambil data bulan', disabled: true}]);
        }
      } else {
        // API request failed (sukses = false)
        console.warn('API request failed - sukses = false:', res);
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
      bulan_a: e.target.value
    }));
  };

  // Handler untuk submit form
  const handleSubmit = (e) => {
    e.preventDefault();
    
    const { bulan_a, id_skp_a, user_a, csrf_token, allcookies } = formData;
    
    if (!bulan_a || !id_skp_a) {
      notifyError('Pilih user dan bulan (id_penilaian) terlebih dahulu');
      return;
    }

    // Send single numeric id_penilaian (as a plain value) instead of a JSON array
    const payload = {
      tupoksi: bulan_a,  // id_penilaian
      bulan: '',
      id_skp: id_skp_a,
      username: user_a,
      csrf_token,
      allcookies
    };

    onSubmit(payload);
  };

  // Fungsi untuk memproses raw response (sama seperti di IsiComponent)
  const processRawResponseJS = (rawResponse) => {
    try {
      if (!rawResponse || typeof rawResponse !== 'string') {
        console.error('Raw response validation failed:', typeof rawResponse, rawResponse);
        throw new Error('Raw response is null, undefined, or not a string. Type: ' + typeof rawResponse);
      }
      
      // 🔍 LOG: Start raw response processing
      console.log('🔄 [AJUKAN] Starting raw response processing...', {
        responseLength: rawResponse.length,
        firstChars: rawResponse.substring(0, 200)
      });
      
      // Processing raw response with JavaScript
      
      // Extract penilaiaan_indikator
      const indikatorMatch = rawResponse.match(/var\s+penilaiaan_indikator\s*=\s*(\[[\s\S]*?\]);/i);
      let indikatorData = null;

      if (indikatorMatch) {
        try {
          indikatorData = JSON.parse(indikatorMatch[1]);
          
          // 📊 LOG: Extracted penilaiaan_indikator
          console.log('✅ [AJUKAN] Extracted penilaiaan_indikator:', {
            found: true,
            dataType: Array.isArray(indikatorData) ? 'array' : typeof indikatorData,
            length: indikatorData?.length || 0,
            sampleItem: indikatorData?.[0] ? {
              id: indikatorData[0].id,
              indikator_kinerja: indikatorData[0].indikator_kinerja?.substring(0, 50) + '...'
            } : null
          });
        } catch (e) {
          console.error('Error parsing indikator JSON:', e);
        }
      } else {
        console.warn('⚠️ [AJUKAN] No penilaiaan_indikator found in response');
      }

      // Extract penilaiaan
      const penilaiaanMatch = rawResponse.match(/var\s+penilaiaan\s*=\s*(\[[\s\S]*?\]);/i);
      let penilaiaanArr = null;
      if (penilaiaanMatch) {
        try {
          penilaiaanArr = JSON.parse(penilaiaanMatch[1]);
          
          // 📊 LOG: Extracted penilaiaan
          console.log('✅ [AJUKAN] Extracted penilaiaan:', {
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
        console.warn('No penilaiaan found in response');
      }

      // Mapping bulan
      const bulan = {
        1: "Januari", 2: "Februari", 3: "Maret", 4: "April",
        5: "Mei", 6: "Juni", 7: "Juli", 8: "Agustus",
        9: "September", 10: "Oktober", 11: "November", 12: "Desember"
      };

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

      const result = {
        indikatorData,
        penilaiaanArr,
        mappingIdPenilaiaan,
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
    <div id="form-ajukan" style={{width: '100%'}}>
      <div style={{width: '100%', margin: '0 auto'}}>
        <div className="text-center mb-4">
          <h2>
            <i className="fas fa-paper-plane me-3"></i>
            <strong>Kirim Rencana Aksi</strong>
          </h2>
          <p className="lead">
            <i className="fas fa-check-circle me-2"></i>
            Hanya menampilkan rencana aksi yang sudah diisi lengkap
          </p>
        </div>
        
        <div className="card p-4 mb-4">
          <form onSubmit={handleSubmit} autoComplete="off">
            <input type="hidden" value={formData.id_skp_a || ''} />
            
            <div className="row g-3 justify-content-center">
              <div className="col-12">
                <label htmlFor="unit_a" className="form-label">
                  <i className="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select 
                  id="unit_a" 
                  className="form-select"
                  value={formData.unit_a || ''}
                  onChange={handleUnitChange}
                >
                  <option value="">-- Pilih Unit --</option>
                  {Object.keys(dataUser).map(unit => (
                    <option key={unit} value={unit}>{unit}</option>
                  ))}
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="kategori_a" className="form-label">
                  <i className="fas fa-tags me-2"></i>Kategori
                </label>
                <select 
                  id="kategori_a" 
                  className="form-select" 
                  disabled={!formData.unit_a}
                  value={formData.kategori_a || ''}
                  onChange={handleKategoriChange}
                >
                  <option value="">-- Pilih Kategori --</option>
                  {formData.unit_a && dataUser[formData.unit_a] && 
                    Object.keys(dataUser[formData.unit_a]).map(kategori => (
                      <option key={kategori} value={kategori}>{kategori}</option>
                    ))
                  }
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="user_a" className="form-label">
                  <i className="fas fa-user me-2"></i>Pengguna
                </label>
                <select 
                  id="user_a" 
                  className="form-select" 
                  disabled={!formData.kategori_a}
                  value={formData.user_a || ''}
                  onChange={handleUserChange}
                >
                  <option value="">-- Pilih User --</option>
                  {formData.unit_a && formData.kategori_a && 
                    dataUser[formData.unit_a]?.[formData.kategori_a] &&
                    Object.keys(dataUser[formData.unit_a][formData.kategori_a]).map(nama => {
                      const userObj = dataUser[formData.unit_a][formData.kategori_a][nama];
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
                <label htmlFor="bulan_a" className="form-label">
                  <i className="fas fa-calendar me-2"></i>Bulan
                </label>
                <select 
                  id="bulan_a" 
                  className="form-select" 
                  disabled={!formData.user_a || isLoadingBulan}
                  value={formData.bulan_a || ''}
                  onChange={handleBulanChange}
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
                disabled={!formData.user_a || !formData.bulan_a || isLoading}
              >
                <i className="fas fa-paper-plane me-2"></i>
                {isLoading ? 'Mengirim...' : 'Kirim Rencana Aksi'}
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

export default AjukanComponent;