import { useState, useEffect } from 'react'
import { BarChart3, TrendingUp, TrendingDown, DollarSign, Users, ShoppingCart } from 'lucide-react'

export default function FinancialReports() {
  const [data, setData] = useState(null)
  const [period, setPeriod] = useState('30')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchReports()
  }, [period])

  const fetchReports = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`http://76.13.56.180:8000/api/admin/financial-reports?period=${period}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const result = await res.json()
        setData(result)
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Financial Reports</h1>
          <p className="text-gray-500 mt-1">Revenue analytics and transaction insights</p>
        </div>
        <select
          value={period}
          onChange={(e) => setPeriod(e.target.value)}
          className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
        >
          <option value="7">Last 7 Days</option>
          <option value="30">Last 30 Days</option>
          <option value="90">Last 90 Days</option>
        </select>
      </div>

      {/* Summary Cards */}
      {data?.summary && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="card">
            <div className="flex items-center gap-3 mb-2">
              <DollarSign className="w-5 h-5 text-green-600" />
              <span className="text-sm text-gray-500">Total Revenue</span>
            </div>
            <p className="text-2xl font-bold">TSh {data.summary.total_revenue?.toLocaleString()}</p>
          </div>
          <div className="card">
            <div className="flex items-center gap-3 mb-2">
              <ShoppingCart className="w-5 h-5 text-blue-600" />
              <span className="text-sm text-gray-500">Total Orders</span>
            </div>
            <p className="text-2xl font-bold">{data.summary.total_orders}</p>
          </div>
          <div className="card">
            <div className="flex items-center gap-3 mb-2">
              <TrendingUp className="w-5 h-5 text-purple-600" />
              <span className="text-sm text-gray-500">Avg Order</span>
            </div>
            <p className="text-2xl font-bold">TSh {Math.round(data.summary.avg_order_value)?.toLocaleString()}</p>
          </div>
          <div className="card">
            <div className="flex items-center gap-3 mb-2">
              <Users className="w-5 h-5 text-orange-600" />
              <span className="text-sm text-gray-500">New Users</span>
            </div>
            <p className="text-2xl font-bold">{data.summary.new_registrations}</p>
          </div>
        </div>
      )}

      {/* Revenue by Channel */}
      {data?.revenue_by_channel && (
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Revenue by Channel</h2>
          <div className="grid grid-cols-2 gap-4">
            <div className="p-4 bg-blue-50 rounded-lg">
              <p className="text-sm text-gray-500">Online Sales</p>
              <p className="text-2xl font-bold text-blue-700">TSh {data.revenue_by_channel.online?.toLocaleString()}</p>
            </div>
            <div className="p-4 bg-green-50 rounded-lg">
              <p className="text-sm text-gray-500">POS Sales</p>
              <p className="text-2xl font-bold text-green-700">TSh {data.revenue_by_channel.pos?.toLocaleString()}</p>
            </div>
          </div>
        </div>
      )}

      {/* Registration Timeline */}
      {data?.registration_timeline && data.registration_timeline.length > 0 && (
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Registration Timeline</h2>
          <div className="space-y-2">
            {data.registration_timeline.map(day => (
              <div key={day.date} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span>{day.date}</span>
                <div className="flex gap-4">
                  <span className="text-sm">Total: {day.registrations}</span>
                  <span className="text-sm text-green-600">Farmers: {day.farmers}</span>
                  <span className="text-sm text-blue-600">Dealers: {day.agrodealers}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
