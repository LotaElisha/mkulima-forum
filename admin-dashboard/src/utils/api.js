import axios from 'axios'

const API_BASE = import.meta.env.VITE_API_URL || 'http://76.13.56.180:8000/api'

const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token')
      window.location.href = '/admin/login'
    }
    return Promise.reject(error)
  }
)

export const authApi = {
  login: (email, password) => api.post('/auth/login', { email, password }),
  me: () => api.get('/auth/me')
}

export const dashboardApi = {
  getStats: () => api.get('/admin/dashboard'),
  getAnalytics: (period = 'daily') => api.get(`/admin/analytics?period=${period}`)
}

export const usersApi = {
  getUsers: (page = 1, search = '') => api.get(`/admin/users?page=${page}&search=${search}`),
  getUser: (id) => api.get(`/admin/users/${id}`),
  updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
  deleteUser: (id) => api.delete(`/admin/users/${id}`)
}

export const ordersApi = {
  getOrders: (page = 1, status = '') => api.get(`/admin/orders?page=${page}&status=${status}`),
  getOrder: (id) => api.get(`/admin/orders/${id}`),
  updateOrder: (id, data) => api.put(`/admin/orders/${id}`, data)
}

export const escrowApi = {
  getEscrows: (page = 1, status = '') => api.get(`/admin/escrows?page=${page}&status=${status}`),
  releaseEscrow: (id) => api.post(`/admin/escrows/${id}/release`),
  refundEscrow: (id) => api.post(`/admin/escrows/${id}/refund`)
}

export const kycApi = {
  getPending: (page = 1) => api.get(`/admin/kyc/pending?page=${page}`),
  approveKyc: (id) => api.post(`/admin/kyc/${id}/approve`),
  rejectKyc: (id, reason) => api.post(`/admin/kyc/${id}/reject`, { reason })
}

export default api
