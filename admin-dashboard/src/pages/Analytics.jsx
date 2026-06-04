import { useState, useEffect } from 'react'
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell
} from 'recharts'
import { TrendingUp, Users, ShoppingCart, DollarSign, Calendar } from 'lucide-react'

const COLORS = ['#16a34a', '#2563eb', '#f59e0b', '#dc2626', '#8b5cf6']

export default function Analytics() {
  const [period, setPeriod] = useState('daily')
  const [analytics, setAnalytics] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchAnalytics()
  }, [period])

  const fetchAnalytics = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`http://76.13.56.180:8000/api/admin/analytics?period=${period}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setAnalytics(data)
      }
    } catch (err) {
      console.error('Failed to fetch analytics:', err)
    } finally {
      setLoading(false)
    }
  }

  // Demo data for visualization
  const demoRevenueData = [
    { name: 'Mon', revenue: 120000, orders: 12 },
    { name: 'Tue', revenue: 150000, orders: 15 },
    { name: 'Wed', revenue: 180000, orders: 18 },
    { name: 'Thu', revenue: 140000, orders: 14 },
    { name: 'Fri', revenue: 220000, orders: 22 },
    { name: 'Sat', revenue: 280000, orders: 28 },
    { name: 'Sun', revenue: 200000, orders: 20 }
  ]

  const demoCategoryData = [
    { name: 'Fertilizer', value: 35 },
    { name: 'Seeds', value: 25 },
    { name: 'Tools', value: 20 },
    { name: 'Pesticides', value: 15 },
    { name: 'Other', value: 5 }
  ]

  const demoUserGrowth = [
    { name: 'Week 1', users: 120 },
    { name: 'Week 2', users: 180 },
    { name: 'Week 3', users: 250 },
    { name: 'Week 4', users: 320 }
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Analytics</h1>
          <p className="text-gray-500 mt-1">Platform performance insights</p>
        </div>
        <div className="flex items-center gap-2">
          <Calendar className="w-5 h-5 text-gray-400" />
          <select
            value={period}
            onChange={(e) => setPeriod(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
          >
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="card">
          <div className="flex items-center gap-3">
            <div className="p-3 bg-green-50 rounded-xl">
              <DollarSign className="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Total Revenue</p>
              <p className="text-2xl font-bold">TSh 1.29M</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-3">
            <div className="p-3 bg-blue-50 rounded-xl">
              <ShoppingCart className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Total Orders</p>
              <p className="text-2xl font-bold">129</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-3">
            <div className="p-3 bg-purple-50 rounded-xl">
              <Users className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Active Users</p>
              <p className="text-2xl font-bold">320</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-3">
            <div className="p-3 bg-yellow-50 rounded-xl">
              <TrendingUp className="w-6 h-6 text-yellow-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Conversion Rate</p>
              <p className="text-2xl font-bold">12.5%</p>
            </div>
          </div>
        </div>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Chart */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Revenue & Orders</h2>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={demoRevenueData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="revenue" stroke="#16a34a" name="Revenue (TSh)" />
              <Line type="monotone" dataKey="orders" stroke="#2563eb" name="Orders" />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {/* Category Distribution */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Sales by Category</h2>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={demoCategoryData}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                outerRadius={100}
                fill="#8884d8"
                dataKey="value"
              >
                {demoCategoryData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>

        {/* User Growth */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">User Growth</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={demoUserGrowth}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="users" fill="#16a34a" name="New Users" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Top Products */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Top Products</h2>
          <div className="space-y-3">
            {[
              { name: 'NPK Fertilizer 50kg', sales: 45, revenue: 900000 },
              { name: 'Maize Seeds 2kg', sales: 38, revenue: 380000 },
              { name: 'Garden Sprayer', sales: 25, revenue: 625000 },
              { name: 'Organic Compost', sales: 22, revenue: 440000 },
              { name: 'Pesticide 1L', sales: 18, revenue: 360000 }
            ].map((product, i) => (
              <div key={i} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div className="flex items-center gap-3">
                  <span className="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-medium">
                    {i + 1}
                  </span>
                  <span className="font-medium">{product.name}</span>
                </div>
                <div className="text-right">
                  <p className="font-medium">{product.sales} sold</p>
                  <p className="text-sm text-gray-500">TSh {product.revenue.toLocaleString()}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}