// Konfigurasi API endpoints
export const API_BASE_URL = 'http://192.168.1.177:8008';

export const API_ENDPOINTS = {
  GET_MAPPING: `/get_mapping.php`,
  TAMBAH_RENAKSI: `/tambakrenaksi.php`,
  ISI_RENAKSI: `/ekinprocess.php`,
  AJUKAN_RENAKSI: `/ajukan.php`
};

// Helper function untuk membuat URL endpoint
export const getEndpoint = (endpoint) => {
  return API_ENDPOINTS[endpoint] || endpoint;
};