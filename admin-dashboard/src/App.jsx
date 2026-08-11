import { lazy, Suspense, useEffect, useState } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/Layout'
import Login from './pages/Login'
import ErrorBoundary from './components/ErrorBoundary'
import { AuthContext, RequireRole } from './components/AuthContext'

const Dashboard = lazy(() => import('./pages/Dashboard'))
const Users = lazy(() => import('./pages/Users'))
const Orders = lazy(() => import('./pages/Orders'))
const Escrows = lazy(() => import('./pages/Escrows'))
const KycVerification = lazy(() => import('./pages/KycVerification'))
const Analytics = lazy(() => import('./pages/Analytics'))
const Settings = lazy(() => import('./pages/Settings'))
const AdminProfile = lazy(() => import('./pages/AdminProfile'))
const HrManagement = lazy(() => import('./pages/HrManagement'))
const PosTerminal = lazy(() => import('./pages/PosTerminal'))
const CatalogManager = lazy(() => import('./pages/CatalogManager'))
const Vendors = lazy(() => import('./pages/Vendors'))
const FinancialReports = lazy(() => import('./pages/FinancialReports'))
const FeatureFlags = lazy(() => import('./pages/FeatureFlags'))
const Moderation = lazy(() => import('./pages/Moderation'))
const MarketPrices = lazy(() => import('./pages/MarketPrices'))
const InputSafety = lazy(() => import('./pages/InputSafety'))
const AiManagement = lazy(() => import('./pages/AiManagement'))
const AiProviders = lazy(() => import('./pages/AiProviders'))
const FarmManagement = lazy(() => import('./pages/FarmManagement'))
const MkulimaVerify = lazy(() => import('./pages/MkulimaVerify'))
const CommunityHub = lazy(() => import('./pages/CommunityHub'))

export function ProtectedRoute({ children }) {
  const [isAuth, setIsAuth] = useState(false)
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const verify = async () => {
      try {
        const res = await fetch('/api/auth/me')
        if (res.ok) {
          const data = await res.json()
          const ok = data.user?.role === 'admin' || data.user?.role === 'superadmin'
          setIsAuth(ok)
          setUser(ok ? data.user : null)
        } else {
          setIsAuth(false)
        }
      } catch {
        setIsAuth(false)
      }
      setLoading(false)
    }
    verify()
  }, [])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return isAuth
    ? <AuthContext.Provider value={user}>{children}</AuthContext.Provider>
    : <Navigate to="/login" replace />
}

function App() {
  return (
    <Suspense fallback={<div className="min-h-screen flex items-center justify-center"><div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600" /></div>}>
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/" element={
        <ProtectedRoute>
          <ErrorBoundary>
            <Layout />
          </ErrorBoundary>
        </ProtectedRoute>
      }>
        <Route index element={<Dashboard />} />
        <Route path="farms" element={<FarmManagement />} />
        <Route path="verify" element={<MkulimaVerify />} />
        <Route path="community-hub" element={<CommunityHub />} />
        <Route path="users" element={<Users />} />
        <Route path="orders" element={<Orders />} />
        <Route path="escrows" element={<Escrows />} />
        <Route path="kyc" element={<KycVerification />} />
        <Route path="analytics" element={<Analytics />} />
        <Route path="settings" element={<Settings />} />
        <Route path="profile" element={<AdminProfile />} />
        <Route path="hr" element={
          <RequireRole roles={['admin', 'superadmin']}><HrManagement /></RequireRole>
        } />
        <Route path="pos" element={<PosTerminal />} />
        <Route path="catalog" element={<CatalogManager />} />
        <Route path="vendors" element={<Vendors />} />
        <Route path="moderation" element={<Moderation />} />
        <Route path="market-prices" element={<MarketPrices />} />
        <Route path="input-safety" element={<InputSafety />} />
        <Route path="financial-reports" element={
          <RequireRole roles={['admin', 'superadmin']}><FinancialReports /></RequireRole>
        } />
        <Route path="features" element={
          <RequireRole roles={['admin', 'superadmin']}><FeatureFlags /></RequireRole>
        } />
        <Route path="ai-management" element={<AiManagement />} />
        <Route path="ai-providers" element={
          <RequireRole roles={['admin', 'superadmin']}><AiProviders /></RequireRole>
        } />
      </Route>
    </Routes>
    </Suspense>
  )
}

export default App
