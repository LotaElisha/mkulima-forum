import { Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import Layout from './components/Layout'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import Users from './pages/Users'
import Orders from './pages/Orders'
import Escrows from './pages/Escrows'
import KycVerification from './pages/KycVerification'
import Analytics from './pages/Analytics'
import Settings from './pages/Settings'
import AdminProfile from './pages/AdminProfile'
import HrManagement from './pages/HrManagement'
import PosTerminal from './pages/PosTerminal'
import CatalogManager from './pages/CatalogManager'
import Vendors from './pages/Vendors'
import FinancialReports from './pages/FinancialReports'

function ProtectedRoute({ children }) {
  const token = localStorage.getItem('admin_token')
  const [isAuth, setIsAuth] = useState(!!token)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const verify = async () => {
      if (!token) {
        setIsAuth(false)
        setLoading(false)
        return
      }
      try {
        const res = await fetch('http://76.13.56.180:8000/api/auth/me', {
          headers: { Authorization: `Bearer ${token}` }
        })
        if (res.ok) {
          const data = await res.json()
          setIsAuth(data.user?.role === 'admin' || data.user?.role === 'superadmin')
        } else {
          setIsAuth(false)
          localStorage.removeItem('admin_token')
        }
      } catch {
        setIsAuth(false)
      }
      setLoading(false)
    }
    verify()
  }, [token])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return isAuth ? children : <Navigate to="/admin/login" replace />
}

function App() {
  return (
    <Routes>
      <Route path="/admin/login" element={<Login />} />
      <Route path="/admin" element={
        <ProtectedRoute>
          <Layout />
        </ProtectedRoute>
      }>
        <Route index element={<Dashboard />} />
        <Route path="users" element={<Users />} />
        <Route path="orders" element={<Orders />} />
        <Route path="escrows" element={<Escrows />} />
        <Route path="kyc" element={<KycVerification />} />
        <Route path="analytics" element={<Analytics />} />
        <Route path="settings" element={<Settings />} />
        <Route path="profile" element={<AdminProfile />} />
        <Route path="hr" element={<HrManagement />} />
        <Route path="pos" element={<PosTerminal />} />
        <Route path="catalog" element={<CatalogManager />} />
        <Route path="vendors" element={<Vendors />} />
        <Route path="financial-reports" element={<FinancialReports />} />
      </Route>
    </Routes>
  )
}

export default App
