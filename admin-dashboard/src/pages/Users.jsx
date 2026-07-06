import { useState, useEffect } from 'react'
import { Users, Search, Plus, Edit, Trash2, Eye, UserCheck, UserX, Shield } from 'lucide-react'

export default function UsersPage() {
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [editingUser, setEditingUser] = useState(null)
  const [message, setMessage] = useState('')
  const [filterRole, setFilterRole] = useState('')

  const [formData, setFormData] = useState({
    name: '', email: '', phone: '', password: '',
    role: 'farmer', tenant_id: 1, kyc_status: 'not_submitted', status: 'active'
  })

  useEffect(() => {
    fetchUsers()
  }, [search, filterRole])

  const fetchUsers = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      let url = '/api/admin/users'
      const params = new URLSearchParams()
      if (search) params.append('search', search)
      if (filterRole) params.append('role', filterRole)
      if (params.toString()) url += '?' + params.toString()

      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } })
      if (res.ok) {
        const data = await res.json()
        setUsers(data.users?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleCreate = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/users', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      })
      const data = await res.json()
      if (res.ok) {
        setMessage('User created!')
        setShowForm(false)
        setFormData({ name: '', email: '', phone: '', password: '', role: 'farmer', tenant_id: 1, kyc_status: 'not_submitted', status: 'active' })
        fetchUsers()
      } else {
        setMessage(data.message || 'Failed')
      }
    } catch (err) {
      setMessage('Network error')
    }
  }

  const handleUpdate = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/users/${editingUser.uuid}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      })
      const data = await res.json()
      if (res.ok) {
        setMessage('User updated!')
        setShowForm(false)
        setEditingUser(null)
        fetchUsers()
      } else {
        setMessage(data.message || 'Failed')
      }
    } catch (err) {
      setMessage('Network error')
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this user permanently?')) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`/api/admin/users/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchUsers()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const openEdit = (user) => {
    setEditingUser(user)
    setFormData({
      name: user.name,
      email: user.email,
      phone: user.phone,
      role: user.role,
      kyc_status: user.kyc_status,
      status: user.status,
      tenant_id: user.tenant_id
    })
    setShowForm(true)
  }

  const getRoleColor = (role) => {
    const colors = {
      admin: 'bg-purple-100 text-purple-800',
      superadmin: 'bg-red-100 text-red-800',
      farmer: 'bg-green-100 text-green-800',
      agrodealer: 'bg-blue-100 text-blue-800',
      agronomist: 'bg-yellow-100 text-yellow-800',
      veterinary: 'bg-orange-100 text-orange-800',
      buyer: 'bg-gray-100 text-gray-800',
      seller: 'bg-gray-100 text-gray-800',
    }
    return colors[role] || colors.buyer
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Users Management</h1>
          <p className="text-gray-500 mt-1">Create, edit, and manage all platform users</p>
        </div>
        <button
          onClick={() => { setEditingUser(null); setShowForm(true); setFormData({ name: '', email: '', phone: '', password: '', role: 'farmer', tenant_id: 1, kyc_status: 'not_submitted', status: 'active' }) }}
          className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add User
        </button>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      {showForm && (
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">{editingUser ? 'Edit User' : 'Create New User'}</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input placeholder="Full Name" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
            <input placeholder="Email" type="email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
            <input placeholder="Phone (255...)" value={formData.phone} onChange={(e) => setFormData({...formData, phone: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
            {!editingUser && <input placeholder="Password" type="password" value={formData.password} onChange={(e) => setFormData({...formData, password: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />}
            <select value={formData.role} onChange={(e) => setFormData({...formData, role: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
              <option value="farmer">Farmer</option>
              <option value="agrodealer">Agrodealer</option>
              <option value="agronomist">Agronomist</option>
              <option value="veterinary">Veterinary</option>
              <option value="buyer">Buyer</option>
              <option value="seller">Seller</option>
            </select>
            <select value={formData.kyc_status} onChange={(e) => setFormData({...formData, kyc_status: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
              <option value="not_submitted">Not Submitted</option>
              <option value="pending">Pending</option>
              <option value="verified">Verified</option>
              <option value="rejected">Rejected</option>
            </select>
            <select value={formData.status} onChange={(e) => setFormData({...formData, status: e.target.value})} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="terminated">Terminated</option>
            </select>
          </div>
          <div className="flex gap-3 mt-4">
            <button onClick={editingUser ? handleUpdate : handleCreate} className="btn-primary">{editingUser ? 'Update' : 'Create'}</button>
            <button onClick={() => { setShowForm(false); setEditingUser(null) }} className="btn-secondary">Cancel</button>
          </div>
        </div>
      )}

      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input type="text" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search users..." className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
          </div>
          <select value={filterRole} onChange={(e) => setFilterRole(e.target.value)} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            <option value="">All Roles</option>
            <option value="farmer">Farmers</option>
            <option value="agrodealer">Agrodealers</option>
            <option value="agronomist">Agronomists</option>
            <option value="buyer">Buyers</option>
            <option value="seller">Sellers</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">User</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Role</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">KYC</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="5" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : users.length === 0 ? (
                <tr><td colSpan="5" className="py-8 text-center text-gray-500">No users found</td></tr>
              ) : (
                users.map(user => (
                  <tr key={user.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                          <span className="text-green-700 font-semibold">{user.name?.charAt(0)?.toUpperCase()}</span>
                        </div>
                        <div>
                          <p className="font-medium">{user.name}</p>
                          <p className="text-sm text-gray-500">{user.phone}</p>
                          <p className="text-sm text-gray-400">{user.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getRoleColor(user.role)}`}>{user.role}</span>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        user.kyc_status === 'verified' ? 'bg-green-100 text-green-800' :
                        user.kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        user.kyc_status === 'rejected' ? 'bg-red-100 text-red-800' :
                        'bg-gray-100 text-gray-800'
                      }`}>
                        {user.kyc_status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        user.status === 'active' ? 'bg-green-100 text-green-800' :
                        user.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {user.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <button onClick={() => openEdit(user)} className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit"><Edit className="w-4 h-4" /></button>
                        <button onClick={() => handleDelete(user.uuid)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><Trash2 className="w-4 h-4" /></button>
                      </div>
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
