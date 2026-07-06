import { useState, useEffect } from 'react'
import { Users, Plus, Search, Shield, UserCheck, UserX, Edit, Trash2 } from 'lucide-react'

export default function HrManagement() {
  const [staff, setStaff] = useState([])
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [showAddForm, setShowAddForm] = useState(false)
  const [newStaff, setNewStaff] = useState({
    name: '', email: '', phone: '', role: 'support',
    password: '', department: '', employee_id: ''
  })
  const [message, setMessage] = useState('')

  useEffect(() => {
    fetchStaff()
    fetchStats()
  }, [])

  const fetchStaff = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/hr/staff', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setStaff(data.staff?.data || [])
      }
    } catch (err) {
      console.error('Failed to fetch staff:', err)
    } finally {
      setLoading(false)
    }
  }

  const fetchStats = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/hr/statistics', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setStats(data)
      }
    } catch (err) {
      console.error('Failed to fetch stats:', err)
    }
  }

  const handleCreate = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/hr/staff', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({...newStaff, tenant_id: 1})
      })
      const data = await res.json()
      if (res.ok) {
        setMessage('Staff member created!')
        setShowAddForm(false)
        setNewStaff({ name: '', email: '', phone: '', role: 'support', password: '', department: '', employee_id: '' })
        fetchStaff()
        fetchStats()
      } else {
        setMessage(data.message || 'Failed to create')
      }
    } catch (err) {
      setMessage('Network error')
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Terminate this staff member?')) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`/api/admin/hr/staff/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchStaff()
      fetchStats()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const getRoleColor = (role) => {
    const colors = {
      admin: 'bg-purple-100 text-purple-800',
      agronomist: 'bg-green-100 text-green-800',
      veterinary: 'bg-blue-100 text-blue-800',
      logistics: 'bg-yellow-100 text-yellow-800',
      support: 'bg-gray-100 text-gray-800'
    }
    return colors[role] || colors.support
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Human Resources</h1>
          <p className="text-gray-500 mt-1">Manage staff and team members</p>
        </div>
        <button
          onClick={() => setShowAddForm(true)}
          className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add Staff
        </button>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      {/* Stats */}
      {stats && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="card">
            <p className="text-sm text-gray-500">Total Staff</p>
            <p className="text-2xl font-bold">{stats.total_staff}</p>
          </div>
          <div className="card">
            <p className="text-sm text-gray-500">Active</p>
            <p className="text-2xl font-bold text-green-600">{stats.by_status?.active}</p>
          </div>
          <div className="card">
            <p className="text-sm text-gray-500">Suspended</p>
            <p className="text-2xl font-bold text-yellow-600">{stats.by_status?.suspended}</p>
          </div>
          <div className="card">
            <p className="text-sm text-gray-500">New This Month</p>
            <p className="text-2xl font-bold text-blue-600">{stats.new_this_month}</p>
          </div>
        </div>
      )}

      {/* Add Form */}
      {showAddForm && (
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Add New Staff</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input
              placeholder="Full Name"
              value={newStaff.name}
              onChange={(e) => setNewStaff({...newStaff, name: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
            <input
              placeholder="Email"
              type="email"
              value={newStaff.email}
              onChange={(e) => setNewStaff({...newStaff, email: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
            <input
              placeholder="Phone (255...)"
              value={newStaff.phone}
              onChange={(e) => setNewStaff({...newStaff, phone: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
            <select
              value={newStaff.role}
              onChange={(e) => setNewStaff({...newStaff, role: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            >
              <option value="admin">Admin</option>
              <option value="agronomist">Agronomist</option>
              <option value="veterinary">Veterinary</option>
              <option value="logistics">Logistics</option>
              <option value="support">Support</option>
            </select>
            <input
              placeholder="Password"
              type="password"
              value={newStaff.password}
              onChange={(e) => setNewStaff({...newStaff, password: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
            <input
              placeholder="Department"
              value={newStaff.department}
              onChange={(e) => setNewStaff({...newStaff, department: e.target.value})}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
          </div>
          <div className="flex gap-3 mt-4">
            <button onClick={handleCreate} className="btn-primary">Create</button>
            <button onClick={() => setShowAddForm(false)} className="btn-secondary">Cancel</button>
          </div>
        </div>
      )}

      {/* Staff List */}
      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search staff..."
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Name</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Role</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Department</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="5" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : staff.length === 0 ? (
                <tr><td colSpan="5" className="py-8 text-center text-gray-500">No staff members found</td></tr>
              ) : (
                staff.map((member) => (
                  <tr key={member.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                          <span className="text-green-700 font-semibold">{member.name?.charAt(0)?.toUpperCase()}</span>
                        </div>
                        <div>
                          <p className="font-medium">{member.name}</p>
                          <p className="text-sm text-gray-500">{member.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getRoleColor(member.role)}`}>
                        {member.role}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-sm">{member.department || '-'}</td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        member.status === 'active' ? 'bg-green-100 text-green-800' :
                        member.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {member.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <button
                        onClick={() => handleDelete(member.uuid)}
                        className="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                        title="Terminate"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
