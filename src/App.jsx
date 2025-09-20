import React, { useState, useEffect } from 'react';
import TambahComponent from './components/TambahComponent';
import IsiComponent from './components/IsiComponent';
import AjukanComponent from './components/AjukanComponent';
import PreviewComponent from './components/PreviewComponent';
import { dataUser, showMessage } from './data/userData';
import { API_ENDPOINTS } from './config/api';
import logger from './utils/logger';
import './styles.css';

const App = () => {
  const [activeTab, setActiveTab] = useState('tambah');
  const [isLoading, setIsLoading] = useState(false);
  const [message, setMessage] = useState(null);
  
  // State untuk form data yang berbeda untuk setiap komponen
  const [formData, setFormData] = useState({
    // Tambah form
    unit_t: '',
    kategori_t: '',
    user_t: '',
    bulan_t: '',
    tupoksi: '',
    id_skp_t: '',
    tahun: '2025',
    
    // Isi form
    unit: '',
    kategori: '',
    user: '',
    bulan: '',
    allcookies: '',
    csrf_token: '',
    jumlahUnikIndikatorKinerja: '',
    bukti_dukung: '',
    rhk: '',
    
    // Ajukan form
    unit_a: '',
    kategori_a: '',
    user_a: '',
    bulan_a: '',
    id_skp_a: '',
    
    // Preview form
    unit_p: '',
    kategori_p: '',
    user_p: '',
    bulan_p: ''
  });

  // Clear message after some time
  useEffect(() => {
    if (message) {
      const timer = setTimeout(() => {
        setMessage(null);
      }, 6000);
      return () => clearTimeout(timer);
    }
  }, [message]);

  // Handler untuk mengubah tab aktif
  const handleTabChange = (tab) => {
    setActiveTab(tab);
    setMessage(null); // Clear message saat berganti tab
  };

  // Handler untuk submit Tambah Rencana Aksi
  const handleTambahSubmit = async (payload) => {
    setIsLoading(true);
    try {
      const formBody = new URLSearchParams(payload).toString();
      
      const response = await fetch(API_ENDPOINTS.TAMBAH_RENAKSI, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: formBody
      });

      const result = await response.json();
      
      // Cek status success atau content length > 500
      const isSuccess = result && result.status === 'success';
      const contentLen = JSON.stringify(result).length;
      
      if (isSuccess || contentLen > 500) {
        setMessage({ text: 'Rencana Aksi Berhasil Ditambahkan', type: 'success' });
        showMessage('Rencana Aksi Berhasil Ditambahkan', 'success');
      } else {
        const errorMsg = result && result.message ? result.message : JSON.stringify(result);
        setMessage({ text: 'Gagal: ' + errorMsg, type: 'danger' });
        showMessage('Gagal: ' + errorMsg, 'danger', 8000);
      }
    } catch (error) {
      logger.error('Error submitting tambah:', error);
      setMessage({ text: 'Gagal mengirim data: ' + error.message, type: 'danger' });
      showMessage('Gagal mengirim data: ' + error.message, 'danger', 8000);
    } finally {
      setIsLoading(false);
    }
  };

  // Handler untuk submit Isi Rencana Aksi
  const handleIsiSubmit = async (formData) => {
    setIsLoading(true);
    try {
      const response = await fetch(API_ENDPOINTS.ISI_RENAKSI, {
        method: 'POST',
        body: formData // FormData object
      });

      const result = await response.text(); // Get as text first to check content
      
      let parsedResult;
      try {
        parsedResult = JSON.parse(result);
      } catch {
        parsedResult = result; // Keep as string if not JSON
      }

      // Check success conditions
      const httpStatus = response.status;
      let isSuccess = false;
      
      try {
        if (parsedResult && typeof parsedResult === 'object') {
          if ('success' in parsedResult) {
            isSuccess = parsedResult.success === true;
          } else if ('status' in parsedResult) {
            isSuccess = String(parsedResult.status).toLowerCase() === 'success';
          }
        }
      } catch {
        isSuccess = false;
      }

      // Check content length
      const contentLen = result.length;
      
      if (isSuccess || contentLen > 500) {
        setMessage({ text: 'Rencana Aksi Berhasil di Isi', type: 'success' });
        showMessage('Rencana Aksi Berhasil di Isi', 'success');
      } else {
        const msg = 'Gagal mengirim data!' + (httpStatus ? ` (status: ${httpStatus})` : '');
        setMessage({ text: msg, type: 'danger' });
        showMessage(msg, 'danger', 8000);
      }
    } catch (error) {
      logger.error('Error submitting isi:', error);
      let msg = 'Gagal mengirim data!';
      if (error.name === 'TypeError' && error.message.includes('fetch')) {
        msg += ' (network error)';
      }
      setMessage({ text: msg, type: 'danger' });
      showMessage(msg, 'danger', 8000);
    } finally {
      setIsLoading(false);
    }
  };

  // Handler untuk submit Ajukan Rencana Aksi
  const handleAjukanSubmit = async (payload) => {
    setIsLoading(true);
    try {
      const formBody = new URLSearchParams(payload).toString();
      
      const response = await fetch(API_ENDPOINTS.AJUKAN_RENAKSI, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: formBody
      });

      const result = await response.json();
      
      const isSuccess = result && result.status === 'success';
      const contentLen = JSON.stringify(result).length;
      
      if (isSuccess || contentLen > 500) {
        setMessage({ text: 'Rencana Aksi Berhasil Diajukan', type: 'success' });
        showMessage('Rencana Aksi Berhasil Diajukan', 'success');
      } else {
        setMessage({ text: 'Ajukan gagal: ' + JSON.stringify(result), type: 'danger' });
        showMessage('Ajukan gagal: ' + JSON.stringify(result), 'danger', 8000);
      }
    } catch (error) {
      logger.error('Error submitting ajukan:', error);
      const errorMsg = 'Request error: ' + error.message;
      setMessage({ text: errorMsg, type: 'danger' });
      showMessage(errorMsg, 'danger', 8000);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="app-container">
      {/* Fixed message container */}
      <div className="message-container" id="message-container"></div>

      {/* Brand Header */}
      <div style={{width: '100%', maxWidth: '1200px'}}>
        <div className="text-center mb-2">
          <div className="brand-container">
            <div className="main-title">
              <i className="fas fa-robot me-3"></i>Bot Automation
            </div>
            <div className="sub-title">
              Rencana Aksi SKP E-Kinerja
            </div>
            <div className="brand-line"></div>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="mb-2" style={{width: '100%', maxWidth: '1200px'}}>
        <div style={{display: 'flex', justifyContent: 'center'}}>
          <ul className="nav nav-pills nav-fill gap-3 flex-row flex-wrap justify-content-center" role="tablist">
            <li className="nav-item" role="presentation">
              <button 
                className={`nav-link ${activeTab === 'tambah' ? 'active' : ''}`}
                type="button" 
                role="tab"
                onClick={() => handleTabChange('tambah')}
              >
                <i className="fas fa-plus-circle me-2"></i>
                <span>Tambah Renaksi</span>
              </button>
            </li>
            <li className="nav-item" role="presentation">
              <button 
                className={`nav-link ${activeTab === 'isi' ? 'active' : ''}`}
                type="button" 
                role="tab"
                onClick={() => handleTabChange('isi')}
              >
                <i className="fas fa-edit me-2"></i>
                <span>Isi Rencana Aksi</span>
              </button>
            </li>
            <li className="nav-item" role="presentation">
              <button 
                className={`nav-link ${activeTab === 'ajukan' ? 'active' : ''}`}
                type="button" 
                role="tab"
                onClick={() => handleTabChange('ajukan')}
              >
                <i className="fas fa-paper-plane me-2"></i>
                <span>Kirim Renaksi</span>
              </button>
            </li>
            <li className="nav-item" role="presentation">
              <button 
                className={`nav-link ${activeTab === 'preview' ? 'active' : ''}`}
                type="button" 
                role="tab"
                onClick={() => handleTabChange('preview')}
              >
                <i className="fas fa-eye me-2"></i>
                <span>Preview Renaksi</span>
              </button>
            </li>
          </ul>
        </div>
      </nav>

      {/* Separator */}
      <div style={{width: '100%', maxWidth: '1200px', padding: '0', marginBottom: '1.5rem'}}>
        <hr />
      </div>

      {/* Main Content */}
      <div style={{
        width: '100%', 
        maxWidth: '1000px',
        display: 'flex',
        justifyContent: 'center'
      }}>
        <div style={{width: '100%'}}>
          <div id="main-content">
              {activeTab === 'tambah' && (
                <TambahComponent 
                  dataUser={dataUser}
                  onSubmit={handleTambahSubmit}
                  isLoading={isLoading}
                  formData={formData}
                  setFormData={setFormData}
                  message={message}
                />
              )}
              
              {activeTab === 'isi' && (
                <IsiComponent 
                  dataUser={dataUser}
                  onSubmit={handleIsiSubmit}
                  isLoading={isLoading}
                  formData={formData}
                  setFormData={setFormData}
                  message={message}
                />
              )}
              
              {activeTab === 'ajukan' && (
                <AjukanComponent 
                  dataUser={dataUser}
                  onSubmit={handleAjukanSubmit}
                  isLoading={isLoading}
                  formData={formData}
                  setFormData={setFormData}
                  message={message}
                />
              )}
          
              {activeTab === 'preview' && (
                <PreviewComponent 
                  dataUser={dataUser}
                  formData={formData}
                  setFormData={setFormData}
                />
              )}
            </div>
        </div>
      </div>

      {/* Footer */}
      <footer style={{width: '100%', maxWidth: '1200px', marginTop: '3rem', padding: '2rem 0'}}>
        <div className="text-center">
          <hr style={{background: '#404040', border: 'none', height: '1px', margin: '2rem 0'}} />
          <div className="footer-content">
            <div className="mb-2">
              <i className="fas fa-code me-2" style={{color: '#8bc88c'}}></i>
              <span style={{fontWeight: '500', color: '#e0e0e0'}}>Design & Created By JMT</span>
            </div>
            <div style={{fontSize: '0.85rem', color: '#999'}}>
              <i className="fas fa-calendar me-1"></i>©Copyright 2025
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}

export default App;
