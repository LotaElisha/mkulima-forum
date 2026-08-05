import { Outlet, NavLink, useNavigate } from 'react-router-dom'
import {
  LayoutDashboard,
  Users,
  ShoppingCart,
  Shield,
  UserCheck,
  BarChart3,
  Settings,
  LogOut,
  Menu,
  Sprout,
  UserCircle,
  Briefcase,
  CreditCard,
  Package,
  Store,
  TrendingUp,
  ToggleRight,
  Flag,
  LineChart,
  ShieldAlert,
  Brain
} from 'lucide-react'
import { useState } from 'react'

export default function Layout() {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const navigate = useNavigate()

  const handleLogout = () => {
    localStorage.removeItem('admin_token')
    navigate('/login')
  }

  const navItems = [
    { to: '/', icon: LayoutDashboard, label: 'Overview' },
    { to: '/pos', icon: CreditCard, label: 'Field POS' },
    { to: '/catalog', icon: Package, label: 'Catalog' },
    { to: '/users', icon: Users, label: 'Users' },
    { to: '/vendors', icon: Store, label: 'Vendors' },
    { to: '/orders', icon: ShoppingCart, label: 'Orders' },
    { to: '/escrows', icon: Shield, label: 'Escrows' },
    { to: '/kyc', icon: UserCheck, label: 'KYC' },
    { to: '/moderation', icon: Flag, label: 'Moderation' },
    { to: '/market-prices', icon: LineChart, label: 'Market Prices' },
    { to: '/input-safety', icon: ShieldAlert, label: 'Input Safety' },
    { to: '/financial-reports', icon: TrendingUp, label: 'Financial' },
    { to: '/features', icon: ToggleRight, label: 'Feature Flags' },
    { to: '/ai-management', icon: Brain, label: 'AI Management' },
    { to: '/analytics', icon: BarChart3, label: 'Analytics' },
    { to: '/hr', icon: Briefcase, label: 'HR / Staff' },
    { to: '/settings', icon: Settings, label: 'Settings' },
    { to: '/profile', icon: UserCircle, label: 'My Profile' },
  ]

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside
        className={`fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 lg:transform-none ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="h-full flex flex-col">
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center">
                <Sprout className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="font-bold text-lg text-gray-900">MkulimaForum</h1>
                <p className="text-xs text-gray-500">Admin Panel</p>
              </div>
            </div>
          </div>

          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {navItems.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.to === '/'}
                onClick={() => setSidebarOpen(false)}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors ${isActive ? 'bg-green-50 text-green-700 font-medium' : ''}`
                }
              >
                <item.icon className="w-5 h-5" />
                {item.label}
              </NavLink>
            ))}
          </nav>

          <div className="p-4 border-t border-gray-200">
            <button
              onClick={handleLogout}
              className="flex items-center gap-3 px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition-colors w-full"
            >
              <LogOut className="w-5 h-5" />
              Logout
            </button>
          </div>
        </div>
      </aside>

      <div className="flex-1 flex flex-col min-w-0">
        <header className="lg:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
          <button
            onClick={() => setSidebarOpen(true)}
            className="p-2 rounded-lg hover:bg-gray-100"
          >
            <Menu className="w-6 h-6" />
          </button>
          <span className="font-semibold">MkulimaForum Admin</span>
          <div className="w-10" />
        </header>

        <main className="flex-1 p-6 overflow-y-auto">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
