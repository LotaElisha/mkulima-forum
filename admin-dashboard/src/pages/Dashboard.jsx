import { useEffect, useState } from 'react'
import {
  Users,
  ShoppingCart,
  DollarSign,
  Shield,
  TrendingUp,
  TrendingDown,
  Activity
} from 'lucide-react'

export default function Dashboard() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchStats()
  }, [])

  const fetchStats = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/dashboard', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setStats(data)
      }
    } catch (err) {
      console.error('Failed to fetch stats:', err)
    } finally {
      setLoading(false)
    }
  }

  const statCards = [
    {
      title: 'Total Users',
      value: stats?.total_users || 0,
      change: '+12%',
      trend: 'up',
      icon: Users,
      color: 'blue'
    },
    {
      title: 'Total Orders',
      value: stats?.total_orders || 0,
      change: '+8%',
      trend: 'up',
      icon: ShoppingCart,
      color: 'green'
    },
    {
      title: 'Total Revenue',
      value: `TSh ${(stats?.total_revenue || 0).toLocaleString()}`,
      change: '+15%',
      trend: 'up',
      icon: DollarSign,
      color: 'yellow'
    },
    {
      title: 'Active Escrows',
      value: stats?.active_escrows || 0,
      change: '-3%',
      trend: 'down',
      icon: Shield,
      color: 'purple'
    }
  ]

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-500 mt-1">Overview of your platform performance</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statCards.map((card, index) => (
          <div key={index} className="card">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm text-gray-500">{card.title}</p>
                <p className="text-2xl font-bold text-gray-900 mt-1">{card.value}</p>
                <div className="flex items-center gap-1 mt-2">
                  {card.trend === 'up' ? (
                    <TrendingUp className="w-4 h-4 text-green-500" />
                  ) : (
                    <TrendingDown className="w-4 h-4 text-red-500" />
                  )}
                  <span className={`text-sm font-medium ${card.trend === 'up' ? 'text-green-600' : 'text-red-600'}`}>
                    {card.change}
                  </span>
                  <span className="text-sm text-gray-400">vs last month</span>
                </div>
              </div>
              <div className={`p-3 rounded-xl bg-${card.color}-50`}>
                <card.icon className={`w-6 h-6 text-${card.color}-600`} />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Recent Activity & Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card">
          <div className="flex items-center gap-2 mb-4">
            <Activity className="w-5 h-5 text-green-600" />
            <h2 className="text-lg font-semibold">Recent Activity</h2>
          </div>
          <div className="space-y-4">
            {[
              { action: 'New user registered', detail: 'John Doe - +255712345678', time: '2 min ago' },
              { action: 'Order placed', detail: 'Order #1234 - TSh 45,000', time: '5 min ago' },
              { action: 'KYC submitted', detail: 'Jane Smith - ID verified', time: '12 min ago' },
              { action: 'Escrow released', detail: 'Order #1230 - TSh 30,000', time: '1 hour ago' },
              { action: 'Product listed', detail: 'Organic Fertilizer - 50kg', time: '2 hours ago' }
            ].map((item, i) => (
              <div key={i} className="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0">
                <div className="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                <div className="flex-1">
                  <p className="text-sm font-medium text-gray-900">{item.action}</p>
                  <p className="text-sm text-gray-500">{item.detail}</p>
                </div>
                <span className="text-xs text-gray-400">{item.time}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Quick Actions</h2>
          <div className="grid grid-cols-2 gap-3">
            {[
              { label: 'View Pending KYC', count: stats?.pending_kyc || 0, color: 'yellow' },
              { label: 'Active Orders', count: stats?.active_orders || 0, color: 'blue' },
              { label: 'Escrow Holdings', count: stats?.escrow_holdings || 0, color: 'purple' },
              { label: 'New Today', count: stats?.new_today || 0, color: 'green' }
            ].map((action, i) => (
              <div key={i} className="p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                <p className="text-2xl font-bold text-gray-900">{action.count}</p>
                <p className="text-sm text-gray-600 mt-1">{action.label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
