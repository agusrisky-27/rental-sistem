import { ref } from 'vue'
import axios from 'axios'

// Assuming backend runs on 8000
const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Request interceptor to add auth token if stored in localStorage (if using sanctum token based, though withCredentials suggests cookie-based)
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const request = async (method, url, data = null, params = null) => {
    loading.value = true
    error.value = null
    try {
      const response = await api({
        method,
        url,
        data,
        params
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Terjadi kesalahan'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchKendaraan = (params) => request('GET', '/api/kendaraan', null, params)
  const fetchPelanggan = (params) => request('GET', '/api/pelanggan', null, params)
  const fetchTransaksi = (params) => request('GET', '/api/transaksi', null, params)
  const fetchPembayaran = (params) => request('GET', '/api/pembayaran', null, params)
  const fetchPengembalian = (params) => request('GET', '/api/pengembalian', null, params)
  const fetchDashboardStats = () => request('GET', '/api/dashboard/stats')

  return {
    loading,
    error,
    api,
    fetchKendaraan,
    fetchPelanggan,
    fetchTransaksi,
    fetchPembayaran,
    fetchPengembalian,
    fetchDashboardStats
  }
}
