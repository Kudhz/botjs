import React, { useState } from 'react';
import { API_ENDPOINTS } from '../config/api';

const PreviewComponent = ({ 
  dataUser, 
  formData,
  setFormData
}) => {
  const [bulanOptions, setBulanOptions] = useState([]);
  const [isLoadingBulan, setIsLoadingBulan] = useState(false);
  const [isLoadingPreview, setIsLoadingPreview] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [showPreview, setShowPreview] = useState(false);
  const [previewBulanData, setPreviewBulanData] = useState({});
  const [previewJSResult, setPreviewJSResult] = useState(null);

  // Handler untuk perubahan Unit
  const handleUnitChange = (e) => {
    const unit = e.target.value;
    setFormData(prev => ({
      ...prev,
      unit_p: unit,
      kategori_p: '',
      user_p: '',
      bulan_p: ''
    }));
    setBulanOptions([]);
    setShowPreview(false);
  };

  // Handler untuk perubahan Kategori  
  const handleKategoriChange = (e) => {
    const kategori = e.target.value;
    setFormData(prev => ({
      ...prev,
      kategori_p: kategori,
      user_p: '',
      bulan_p: ''
    }));
    setBulanOptions([]);
    setShowPreview(false);
  };

  // Handler untuk perubahan User
  const handleUserChange = async (e) => {
    const username = e.target.value;
    console.log('🔄 [PREVIEW] handleUserChange called with username:', username);
    
    setFormData(prev => ({
      ...prev,
      user_p: username,
      bulan_p: ''
    }));
    
    // Reset preview data
    setPreviewBulanData({});
    setPreviewJSResult(null);
    setShowPreview(false);
    
    if (!username) {
      setBulanOptions([]);
      return;
    }

    // Cari password dari dataUser
    const { unit_p, kategori_p } = formData;
    let password = '';
    if (unit_p && kategori_p && dataUser[unit_p]?.[kategori_p]) {
      Object.keys(dataUser[unit_p][kategori_p]).forEach(nama => {
        const u = dataUser[unit_p][kategori_p][nama];
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
      console.log('📡 [PREVIEW] GET_MAPPING API Response:', {
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
          console.error('[PREVIEW] No raw_response found in API response');
          setBulanOptions([{value: '', label: '❌ Raw response tidak tersedia', disabled: true}]);
          return;
        }
        
        console.log('🔍 [PREVIEW] Processing raw response...');
        const jsResult = processRawResponseJS(res.raw_response);
        
        // 📊 LOG: Monitor JavaScript Processing Results
        console.log('📊 [PREVIEW] JavaScript Processing Results:', {
          success: jsResult.success,
          hasIndikatorData: !!jsResult.indikatorData,
          indikatorDataLength: jsResult.indikatorData?.length || 0,
          hasPenilaiaanArr: !!jsResult.penilaiaanArr,
          penilaiaanArrLength: jsResult.penilaiaanArr?.length || 0,
          hasRhkIndikator: !!jsResult.rhkIndikator,
          rhkIndikatorLength: jsResult.rhkIndikator?.length || 0,
          jumlahUnikIndikatorKinerja: jsResult.jumlahUnikIndikatorKinerja || 0,
          mappingTupoksiLength: jsResult.mappingTupoksi?.length || 0,
          hasMappingBulanId: !!jsResult.mappingBulanId,
          mappingBulanIdKeys: jsResult.mappingBulanId ? Object.keys(jsResult.mappingBulanId) : [],
          renaksiBulanIdLength: jsResult.renaksiBulanId?.length || 0,
          buktiDukung: jsResult.buktiDukung || 'N/A'
        });
        
        if (jsResult.success && jsResult.mappingBulanId) {
          // Populate dropdown dengan semua bulan yang tersedia (sudah diisi, belum diisi, dll)
          const options = [];
          console.log('📅 [PREVIEW] Available months for preview:', Object.keys(jsResult.mappingBulanId));
          
          const tempBulanData = {};
          for (let bulanLabel in jsResult.mappingBulanId) {
            const ids = jsResult.mappingBulanId[bulanLabel];
            if (!Array.isArray(ids) || ids.length === 0) {
              console.warn('⚠️ Skipping month with invalid IDs:', bulanLabel, ids);
              continue;
            }
            
            // Create data object and store in a simpler format
            const bulanData = {
              ids: ids,
              label: bulanLabel
            };
            
            // Use a simpler approach - store data in a global object and use index as value
            const dataKey = 'month_' + Object.keys(tempBulanData).length;
            tempBulanData[dataKey] = bulanData;
            
            console.log('📝 [PREVIEW] Adding month option:', { bulanLabel, dataKey });
            
            options.push({
              value: dataKey,
              label: bulanLabel,
              disabled: false
            });
          }
          
          if (options.length === 0) {
            setBulanOptions([{value: '', label: '-- Tidak ada data bulan tersedia --', disabled: true}]);
          } else {
            setBulanOptions(options);
          }
          
          setPreviewBulanData(tempBulanData);
        } else {
          console.error('[PREVIEW] JS processing failed or no mapping data:', jsResult);
          setBulanOptions([{value: '', label: '❌ JavaScript processing gagal', disabled: true}]);
        }
        
        // Store JS results untuk digunakan saat preview
        setPreviewJSResult(jsResult);
        
        // 🎯 LOG: Detailed preview data monitoring
        if (jsResult && jsResult.success) {
          console.log('🎯 [PREVIEW] === RENAKSI DATA SIAP ===');
          console.log('📊 [PREVIEW] Summary Data Preview:', {
            jumlahIndikatorKinerjaUnik: jsResult.jumlahUnikIndikatorKinerja || 0,
            jumlahMappingTupoksi: jsResult.mappingTupoksi ? jsResult.mappingTupoksi.length : 0,
            jumlahMappingBulan: jsResult.mappingBulanId ? Object.keys(jsResult.mappingBulanId).length : 0,
            jumlahRenaksiData: jsResult.renaksiBulanId ? jsResult.renaksiBulanId.length : 0,
            mappingBulanKeys: jsResult.mappingBulanId ? Object.keys(jsResult.mappingBulanId).slice(0, 5) : [],
            sampleTupoksi: jsResult.mappingTupoksi ? jsResult.mappingTupoksi.slice(0, 5) : [],
            sampleRenaksi: jsResult.renaksiBulanId ? jsResult.renaksiBulanId.slice(0, 3) : []
          });
          console.log('🎯 [PREVIEW] === END RENAKSI DATA ===');
        }
        
      } else {
        setBulanOptions([{value: '', label: '❌ Gagal ambil data', disabled: true}]);
      }
    } catch (error) {
      console.error('Error fetching mapping:', error);
      setBulanOptions([{value: '', label: '❌ Gagal ambil data', disabled: true}]);
    } finally {
      setIsLoadingBulan(false);
    }
  };

  // Handler untuk perubahan Bulan
  const handleBulanChange = (e) => {
    setFormData(prev => ({
      ...prev,
      bulan_p: e.target.value
    }));
    
    // Jika preview sudah ditampilkan dan user berubah, sembunyikan preview lama
    if (showPreview) {
      setShowPreview(false);
      console.log('Pilihan bulan berubah. Klik "Tampilkan Preview" untuk melihat data terbaru.');
    }
  };

  // Handler untuk tampilkan preview
  const handleShowPreview = (e) => {
    e.preventDefault();
    
    const bulanVal = formData.bulan_p;
    if (!bulanVal) {
      alert('Pilih bulan terlebih dahulu');
      return;
    }
    
    setIsLoadingPreview(true);
    
    console.log('🔍 Preview button clicked. Bulan value:', bulanVal);
    
    try {
      let bulanData = null;
      let selectedIds = [];
      let bulanLabel = '';
      
      // Check if using new format (data key)
      if (bulanVal.startsWith('month_') && previewBulanData && previewBulanData[bulanVal]) {
        bulanData = previewBulanData[bulanVal];
        selectedIds = bulanData.ids || [];
        bulanLabel = bulanData.label || 'Unknown';
        console.log('✅ Using stored data:', bulanData);
      } else {
        // Fallback to old JSON parsing
        const decodedVal = bulanVal.replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        console.log('📝 Trying to decode as JSON:', decodedVal);
        
        try {
          bulanData = JSON.parse(decodedVal);
          selectedIds = bulanData.ids || [];
          bulanLabel = bulanData.label || 'Unknown';
          console.log('✅ Parsed as JSON:', bulanData);
        } catch {
          console.log('⚠️ JSON parsing failed, using fallback');
          selectedIds = [bulanVal];
          bulanLabel = bulanOptions.find(opt => opt.value === bulanVal)?.label || 'Unknown';
        }
      }
      
      if (!previewJSResult) {
        alert('Data belum tersedia. Pilih user terlebih dahulu.');
        return;
      }
      
      console.log('📊 Processing preview with IDs:', selectedIds, 'Label:', bulanLabel);
      
      // Generate preview content dengan timeout untuk loading simulation
      setTimeout(() => {
        try {
          const generatedPreview = generatePreviewContent(selectedIds, bulanLabel, previewJSResult);
          setPreviewData(generatedPreview);
          setShowPreview(true);
          setIsLoadingPreview(false);
        } catch (error) {
          console.error('❌ Error in generatePreviewContent:', error);
          alert('Error saat memuat preview: ' + error.message);
          setIsLoadingPreview(false);
        }
      }, 800); // Loading simulation
      
    } catch (e) {
      console.error('❌ Error processing bulan data:', e);
      console.error('Raw bulan value:', bulanVal);
      alert('Error memproses data bulan: ' + e.message);
      setIsLoadingPreview(false);
    }
  };

  // Function to generate preview content
  const generatePreviewContent = (selectedIds, bulanLabel, jsResult) => {
    // 🔍 LOG: Monitor preview content generation
    console.log('🎬 [PREVIEW] === GENERATE CONTENT ===');
    console.log('🎬 [PREVIEW] Generating preview content:', {
      bulanLabel,
      selectedIdsLength: selectedIds.length,
      sampleIds: selectedIds.slice(0, 5),
      hasMoreIds: selectedIds.length > 5,
      additionalIds: selectedIds.length > 5 ? selectedIds.length - 5 : 0,
      hasJsResult: !!jsResult,
      totalRenaksiData: jsResult?.renaksiBulanId?.length || 0,
      totalMappingTupoksi: jsResult?.mappingTupoksi?.length || 0
    });
    console.log('🎬 [PREVIEW] =======================================');
    
    try {
        // Validate inputs
        if (!Array.isArray(selectedIds)) {
          throw new Error('selectedIds is not an array: ' + typeof selectedIds);
        }
        
        if (!jsResult || !jsResult.indikatorData) {
          throw new Error('Invalid jsResult or missing indikatorData');
        }
        
        // Find matching data from JS results
        const previewData = [];
        if (jsResult.indikatorData && Array.isArray(jsResult.indikatorData)) {
          console.log('🔍 Searching through', jsResult.indikatorData.length, 'indikator items');
          
          selectedIds.forEach(id => {
            const matchingItem = jsResult.indikatorData.find(item => {
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
          // Summary statistics with better styling
          const itemsWithRealisasi = previewData.filter(item => (parseFloat(item.realisasi) || 0) > 0).length;
          const itemsWithRenaksi = previewData.filter(item => item.renaksi && item.renaksi !== 'Belum diisi').length;
          
          // Determine month status based on mapping bulan logic
          const statusValues = previewData.map(item => item.status || 0);
          const maxStatus = Math.max(...statusValues);
          
          let monthStatus, monthStatusClass, monthStatusIcon;
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
            const hasRealisasi = previewData.some(item => (parseFloat(item.realisasi) || 0) > 0);
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
          
          return {
            bulanLabel,
            previewData,
            itemsWithRealisasi,
            itemsWithRenaksi,
            monthStatus,
            monthStatusClass,
            monthStatusIcon
          };
          
        } else {
          return {
            bulanLabel,
            previewData: [],
            error: 'Tidak ditemukan data indikator untuk bulan ini. IDs yang dicari: ' + selectedIds.join(', ')
          };
        }
        
      } catch (error) {
        console.error('❌ Error in generatePreviewContent:', error);
        throw error;
      }
  };

  // Fungsi untuk memproses raw response (sama seperti di komponen lain)
  const processRawResponseJS = (rawResponse) => {
    try {
      if (!rawResponse || typeof rawResponse !== 'string') {
        throw new Error('Raw response is null, undefined, or not a string');
      }

      // 🔍 LOG: Start raw response processing
      console.log('� [PREVIEW] Starting raw response processing...', {
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
        } catch (e) {
          console.error('❌ Error parsing indikator JSON:', e);
        }
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
      }

      // Mapping bulan
      const bulan = {
        1: "Januari", 2: "Februari", 3: "Maret", 4: "April",
        5: "Mei", 6: "Juni", 7: "Juli", 8: "Agustus",
        9: "September", 10: "Oktober", 11: "November", 12: "Desember"
      };

      // Process mapping bulan dengan status
      let mappingBulanId = {};
      let mappingBulanIdIndikator = {};
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

      // Extract renaksi data
      const renaksiBulanId = [];
      if (Array.isArray(indikatorData)) {
        const renaksiByIdIndikator = {};
        
        indikatorData.forEach(item => {
          if (item.id_indikator && item.renaksi && item.id) {
            renaksiByIdIndikator[item.id_indikator] = item.renaksi;
          }
        });
        
        mappingTupoksi.forEach(idIndikator => {
          if (renaksiByIdIndikator[idIndikator]) {
            renaksiBulanId.push(renaksiByIdIndikator[idIndikator]);
          } else {
            renaksiBulanId.push('');
          }
        });
      }

      return {
        indikatorData,
        penilaiaanArr,
        rhkIndikator,
        jumlahUnikIndikatorKinerja,
        mappingTupoksi,
        mappingBulanId,
        mappingBulanIdIndikator,
        renaksiBulanId,
        success: true
      };

    } catch (error) {
      console.error('❌ JavaScript Processing Error:', error);
      return { success: false, error: error.message };
    }
  };

  return (
    <div id="preview-renaksi" style={{width: '100%'}}>
      <div style={{width: '100%', margin: '0 auto'}}>
        <div className="text-center mb-4">
          <h2>
            <i className="fas fa-eye me-3"></i>
            <strong>Preview Isi Renaksi</strong>
          </h2>
          <p className="lead">
            <i className="fas fa-search me-2"></i>
            Lihat detail rencana aksi yang sudah diisi berdasarkan bulan
          </p>
        </div>
        
        <div className="card p-4 mb-4">
          <form autoComplete="off">
            <div className="row g-3 justify-content-center">
              <div className="col-12">
                <label htmlFor="unit_p" className="form-label">
                  <i className="fas fa-building me-2"></i>Unit Kerja
                </label>
                <select 
                  id="unit_p" 
                  className="form-select"
                  value={formData.unit_p || ''}
                  onChange={handleUnitChange}
                >
                  <option value="">-- Pilih Unit --</option>
                  {Object.keys(dataUser).map(unit => (
                    <option key={unit} value={unit}>{unit}</option>
                  ))}
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="kategori_p" className="form-label">
                  <i className="fas fa-tags me-2"></i>Kategori
                </label>
                <select 
                  id="kategori_p" 
                  className="form-select" 
                  disabled={!formData.unit_p}
                  value={formData.kategori_p || ''}
                  onChange={handleKategoriChange}
                >
                  <option value="">-- Pilih Kategori --</option>
                  {formData.unit_p && dataUser[formData.unit_p] && 
                    Object.keys(dataUser[formData.unit_p]).map(kategori => (
                      <option key={kategori} value={kategori}>{kategori}</option>
                    ))
                  }
                </select>
              </div>
              
              <div className="col-12">
                <label htmlFor="user_p" className="form-label">
                  <i className="fas fa-user me-2"></i>Pengguna
                </label>
                <select 
                  id="user_p" 
                  className="form-select" 
                  disabled={!formData.kategori_p}
                  value={formData.user_p || ''}
                  onChange={handleUserChange}
                >
                  <option value="">-- Pilih User --</option>
                  {formData.unit_p && formData.kategori_p && 
                    dataUser[formData.unit_p]?.[formData.kategori_p] &&
                    Object.keys(dataUser[formData.unit_p][formData.kategori_p]).map(nama => {
                      const userObj = dataUser[formData.unit_p][formData.kategori_p][nama];
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
                <label htmlFor="bulan_p" className="form-label">
                  <i className="fas fa-calendar me-2"></i>Bulan
                </label>
                <select 
                  id="bulan_p" 
                  className="form-select" 
                  disabled={!formData.user_p || isLoadingBulan}
                  value={formData.bulan_p || ''}
                  onChange={handleBulanChange}
                >
                  <option value="">
                    {isLoadingBulan ? '🔄 Loading data bulan...' : '-- Pilih Bulan untuk Preview --'}
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
                type="button" 
                className="btn btn-info btn-lg px-5"
                disabled={!formData.user_p || !formData.bulan_p || isLoadingPreview}
                onClick={handleShowPreview}
              >
                {isLoadingPreview ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-2"></span>
                    Memuat Preview...
                  </>
                ) : (
                  <>
                    <i className="fas fa-eye me-2"></i>
                    Tampilkan Preview
                  </>
                )}
              </button>
            </div>
          </form>
        </div>

        {/* Preview Results Container */}
        {showPreview && previewData && (
          <div className="mt-4">
            <div className="card">
              <div 
                className="card-header d-flex justify-content-between align-items-center" 
                style={{
                  background: 'linear-gradient(45deg, #6b9b6d, #8bc88c)', 
                  color: 'white', 
                  borderRadius: '16px 16px 0 0'
                }}
              >
                <span>
                  <i className="fas fa-chart-line me-2"></i>
                  <strong>Hasil Preview Rencana Aksi</strong>
                </span>
                <button 
                  type="button" 
                  className="btn btn-sm btn-outline-light rounded-pill" 
                  onClick={() => setShowPreview(false)}
                >
                  <i className="fas fa-times"></i>
                </button>
              </div>
              <div className="card-body p-4">
                <div id="preview-content">
                  {/* Header info */}
                  <div className="row mb-4">
                    <div className="col-12">
                      <div className="alert alert-info border-info bg-dark text-light">
                        <h5 className="text-info">
                          <i className="fas fa-calendar me-2"></i>
                          {previewData.bulanLabel || 'Unknown Month'}
                        </h5>
                        <p className="mb-0 text-light">
                          Jumlah Indikator: {previewData.previewData ? previewData.previewData.length : 0}
                        </p>
                      </div>
                    </div>
                  </div>

                  {previewData.error ? (
                    <div className="alert alert-warning border-warning bg-dark text-light">
                      <h6 className="text-warning">
                        <i className="fas fa-exclamation-triangle me-2"></i>
                        Tidak ada data
                      </h6>
                      <p className="mb-0 text-light">{previewData.error}</p>
                    </div>
                  ) : previewData.previewData && previewData.previewData.length > 0 ? (
                    <>
                      {/* Summary statistics */}
                      <div className="row mb-4 g-3">
                        <div className="col-md-4">
                          <div className={`card ${previewData.monthStatusClass} text-white shadow-sm h-100 summary-card`}>
                            <div className="card-body text-center d-flex flex-column justify-content-center">
                              <i className={`${previewData.monthStatusIcon} fa-2x mb-2`}></i>
                              <h6 className="card-title mb-2">Status Bulan</h6>
                              <h4 className="mb-0">{previewData.monthStatus}</h4>
                            </div>
                          </div>
                        </div>
                        
                        <div className="col-md-4">
                          <div className="card bg-success text-white shadow-sm h-100 summary-card">
                            <div className="card-body text-center d-flex flex-column justify-content-center">
                              <i className="fas fa-check-circle fa-2x mb-2"></i>
                              <h6 className="card-title mb-2">Sudah Diisi</h6>
                              <h4 className="mb-0">
                                {previewData.itemsWithRealisasi} / {previewData.previewData.length}
                              </h4>
                            </div>
                          </div>
                        </div>
                        
                        <div className="col-md-4">
                          <div className="card bg-info text-white shadow-sm h-100 summary-card">
                            <div className="card-body text-center d-flex flex-column justify-content-center">
                              <i className="fas fa-clipboard-list fa-2x mb-2"></i>
                              <h6 className="card-title mb-2">Ada Rencana Aksi</h6>
                              <h4 className="mb-0">
                                {previewData.itemsWithRenaksi} / {previewData.previewData.length}
                              </h4>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Data table */}
                      <div className="table-responsive shadow-sm rounded">
                        <table className="table table-striped table-hover preview-table mb-0">
                          <thead className="table-dark">
                            <tr>
                              <th style={{width: '5%'}} className="text-center">#</th>
                              <th style={{width: '30%'}}>Indikator Kinerja</th>
                              <th style={{width: '25%'}}>Rencana Aksi</th>
                              <th style={{width: '10%'}} className="text-center">Realisasi</th>
                              <th style={{width: '20%'}}>Bukti Dukung</th>
                              <th style={{width: '10%'}} className="text-center">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            {previewData.previewData.map((item, index) => {
                              const realisasi = item.realisasi || 0;
                              const renaksi = item.renaksi || 'Belum diisi';
                              const indikator = item.indikator_kinerja || 'N/A';
                              const buktiDukung = item.bukti_dukung || 'Belum ada';
                              
                              // Determine status based on realisasi
                              let statusClass = 'bg-warning';
                              let statusText = 'Belum ada';
                              if (realisasi > 0) {
                                statusClass = 'bg-success';
                                statusText = 'Sudah diisi';
                              } else if (renaksi && renaksi !== 'Belum diisi') {
                                statusClass = 'bg-success';
                                statusText = 'Renaksi ada';
                              }
                              
                              return (
                                <tr key={index}>
                                  <td className="text-center fw-bold text-primary">{index + 1}</td>
                                  <td>
                                    <div className="text-content fw-medium text-light">{indikator}</div>
                                  </td>
                                  <td>
                                    <div className="text-content text-muted">{renaksi}</div>
                                  </td>
                                  <td className="text-center text-white fw-bold">{realisasi}</td>
                                  <td>
                                    <div className="text-content text-info">{buktiDukung}</div>
                                  </td>
                                  <td className="text-center">
                                    <span className={`badge ${statusClass} status-badge text-wrap`}>
                                      {statusText}
                                    </span>
                                  </td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </div>
                    </>
                  ) : (
                    <div className="alert alert-warning border-warning bg-dark text-light">
                      <h6 className="text-warning">
                        <i className="fas fa-exclamation-triangle me-2"></i>
                        Tidak ada data
                      </h6>
                      <p className="mb-0 text-light">
                        Tidak ditemukan data untuk bulan yang dipilih.
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default PreviewComponent;