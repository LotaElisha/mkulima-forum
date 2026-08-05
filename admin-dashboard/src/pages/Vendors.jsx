import { useState, useEffect } from 'react'
import { Store, Search, Star, MapPin, Plus, Edit, Trash2, UserCheck, UserX, CheckCircle, XCircle, AlertTriangle, Eye } from 'lucide-react'

const KYC_COLORS = {
  verified:     'bg-green-100 text-green-800',
  pending:      'bg-yellow-100 text-yellow-800',
  rejected:     'bg-red-100 text-red-800',
  not_submitted:'bg-gray-100 text-gray-600',
}
const STATUS_COLORS = {
  active:     'bg-green-100 text-green-800',
  suspended:  'bg-yellow-100 text-yellow-800',
  terminated: 'bg-red-100 text-red-800',
}

export default function Vendors() {
  const [vendors, setVendors] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const [filterKyc, setFilterKyc] = useState('')
  const [message, setMessage] = useState({ text: '', type: '' })

  // Edit modal
  const [editVendor, setEditVendor] = useState(null)
  const [editForm, setEditForm] = useState({})
  const [saving, setSaving] = useState(false)

  // Detail view
  const [viewVendor, setViewVendor] = useState(null)
  const [viewStats, setViewStats] = useState(null)

  useEffect(() => {
    fetchVendors()
  }, [search, filterStatus, filterKyc])

  const fetchVendors = async () => {
    setLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      const params = new URLSearchParams()
      if (search)       params.append('search', search)
      if (filterStatus) params.append('status', filterStatus)
      if (filterKyc)    params.append('kyc_status', filterKyc)
      const res = await fetch(`/api/admin/vendors?${params}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setVendors(data.vendors?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const notify = (text, type = 'success') => {
    setMessage({ text, type })
    setTimeout(() => setMessage({ text: '', type: '' }), 4000)
  }

  const openEdit = (vendor) => {
    setEditVendor(vendor)
    setEditForm({
      name: vendor.name || '',
      email: vendor.email || '',
      phone: vendor.phone || '',
      status: vendor.status || 'active',
      kyc_status: vendor.kyc_status || 'not_submitted',
    })
  }

  const handleUpdate = async () => {
    setSaving(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${editVendor.uuid}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
        body: JSON.stringify(editForm)
      })
      const data = await res.json()
      if (res.ok) {
        notify('Vendor updated successfully')
        setEditVendor(null)
        fetchVendors()
      } else {
        notify(data.message || 'Update failed', 'error')
      }
    } catch {
      notify('Network error', 'error')
    } finally {
      setSaving(false)
    }
  }

  const handleSuspend = async (uuid) => {
    if (!confirm('Suspend this vendor? All products will be deactivated.')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}/suspend`, {
        method: 'POST', headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) { notify('Vendor suspended'); fetchVendors() }
    } catch { notify('Failed', 'error') }
  }

  const handleReactivate = async (uuid) => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}/reactivate`, {
        method: 'POST', headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) { notify('Vendor reactivated'); fetchVendors() }
    } catch { notify('Failed', 'error') }
  }

  const handleDelete = async (uuid, name) => {
    if (!confirm(`Permanently delete vendor "${name}" and all their products? This cannot be undone.`)) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}`, {
        method: 'DELETE', headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) { notify('Vendor deleted'); fetchVendors() }
      else { const d = await res.json(); notify(d.message || 'Failed', 'error') }
    } catch { notify('Network error', 'error') }
  }

  const openView = async (vendor) => {
    setViewVendor(vendor)
    setViewStats(null)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${vendor.uuid}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setViewStats(data.stats)
      }
    } catch {}
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Vendors & Partners</h1>
          <p className="text-gray-500 mt-1">Manage agrodealers, agrovets, and suppliers</p>
        </div>
      </div>

      {/* Feedback message */}
      {message.text && (
        <div className={`p-4 rounded-lg border text-sm font-medium ${
          message.type === 'error'
            ? 'bg-red-50 border-red-200 text-red-700'
            : 'bg-green-50 border-green-200 text-green-700'
        }`}>
          {message.text}
        </div>
      )}

      {/* Filters */}
      <div className="card">
        <div className="flex flex-wrap gap-3">
          <div className="relative flex-1 min-w-48">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search vendors..."
              className="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-sm"
            />
          </div>
          <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="terminated">Terminated</option>
          </select>
          <select value={filterKyc} onChange={(e) => setFilterKyc(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-sm">
            <option value="">All KYC</option>
            <option value="verified">Verified</option>
            <option value="pending">Pending</option>
            <option value="rejected">Rejected</option>
            <option value="not_submitted">Not Submitted</option>
          </select>
        </div>
      </div>

      {/* Table */}
      <div className="card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50">
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Vendor</th>
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rating</th>
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">KYC</th>
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th className="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="py-12 text-center">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" />
                </td></tr>
              ) : vendors.length === 0 ? (
                <tr><td colSpan="6" className="py-12 text-center text-gray-400">
                  <Store className="w-10 h-10 mx-auto mb-2 opacity-30" />
                  <p>No vendors found</p>
                </td></tr>
              ) : vendors.map(vendor => (
                <tr key={vendor.uuid} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        <span className="text-blue-700 font-bold text-sm">{vendor.name?.charAt(0)?.toUpperCase()}</span>
                      </div>
                      <div>
                        <p className="font-medium text-gray-900 text-sm">{vendor.name}</p>
                        <p className="text-xs text-gray-500">{vendor.phone}</p>
                        {vendor.email && <p className="text-xs text-gray-400">{vendor.email}</p>}
                      </div>
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <span className="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {vendor.role}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-1">
                      <Star className="w-3.5 h-3.5 text-yellow-500 fill-yellow-500" />
                      <span className="text-sm font-medium">{Number(vendor.products_avg_rating || 0).toFixed(1)}</span>
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${KYC_COLORS[vendor.kyc_status] || KYC_COLORS.not_submitted}`}>
                      {vendor.kyc_status?.replace('_', ' ') || 'Not submitted'}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${STATUS_COLORS[vendor.status] || STATUS_COLORS.terminated}`}>
                      {vendor.status}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-1">
                      <button onClick={() => openView(vendor)}
                        className="p-1.5 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="View details">
                        <Eye className="w-3.5 h-3.5" />
                      </button>
                      <button onClick={() => openEdit(vendor)}
                        className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                        <Edit className="w-3.5 h-3.5" />
                      </button>
                      {vendor.status === 'active' ? (
                        <button onClick={() => handleSuspend(vendor.uuid)}
                          className="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Suspend">
                          <UserX className="w-3.5 h-3.5" />
                        </button>
                      ) : (
                        <button onClick={() => handleReactivate(vendor.uuid)}
                          className="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Reactivate">
                          <UserCheck className="w-3.5 h-3.5" />
                        </button>
                      )}
                      <button onClick={() => handleDelete(vendor.uuid, vendor.name)}
                        className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete permanently">
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Edit Modal */}
      {editVendor && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="bg-blue-700 text-white px-6 py-4">
              <h2 className="text-lg font-bold">Edit Vendor</h2>
              <p className="text-blue-100 text-sm">{editVendor.name}</p>
            </div>
            <div className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input value={editForm.name} onChange={(e) => setEditForm({...editForm, name: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value={editForm.email} onChange={(e) => setEditForm({...editForm, email: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input value={editForm.phone} onChange={(e) => setEditForm({...editForm, phone: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm" />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select value={editForm.status} onChange={(e) => setEditForm({...editForm, status: e.target.value})}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="terminated">Terminated</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">KYC Status</label>
                  <select value={editForm.kyc_status} onChange={(e) => setEditForm({...editForm, kyc_status: e.target.value})}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    <option value="not_submitted">Not Submitted</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>
            </div>
            <div className="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
              <button onClick={() => setEditVendor(null)}
                className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button onClick={handleUpdate} disabled={saving}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* View Details Modal */}
      {viewVendor && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="bg-green-700 text-white px-6 py-4 flex items-center justify-between">
              <div>
                <h2 className="text-lg font-bold">{viewVendor.name}</h2>
                <p className="text-green-100 text-sm">{viewVendor.role} • {viewVendor.phone}</p>
              </div>
              <span className={`px-2 py-1 rounded-full text-xs font-medium ${KYC_COLORS[viewVendor.kyc_status] || 'bg-gray-100 text-gray-600'}`}>
                KYC: {viewVendor.kyc_status}
              </span>
            </div>
            <div className="p-6">
              {viewStats ? (
                <div className="grid grid-cols-2 gap-4">
                  {[
                    { label: 'Total Products', value: viewStats.total_products, color: 'text-blue-600' },
                    { label: 'Total Orders', value: viewStats.total_orders, color: 'text-purple-600' },
                    { label: 'Revenue (TZS)', value: `${Number(viewStats.total_revenue || 0).toLocaleString()}`, color: 'text-green-600' },
                    { label: 'Avg Rating', value: Number(viewStats.avg_rating || 0).toFixed(1) + ' ★', color: 'text-yellow-600' },
                  ].map(item => (
                    <div key={item.label} className="bg-gray-50 rounded-lg p-4">
                      <p className="text-xs text-gray-500 mb-1">{item.label}</p>
                      <p className={`text-xl font-bold ${item.color}`}>{item.value}</p>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="flex items-center justify-center py-8">
                  <div className="w-6 h-6 border-2 border-green-600 border-t-transparent rounded-full animate-spin" />
                </div>
              )}
            </div>
            <div className="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
              <div className="flex gap-2">
                {viewVendor.status === 'active' ? (
                  <button onClick={() => { handleSuspend(viewVendor.uuid); setViewVendor(null) }}
                    className="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-medium hover:bg-yellow-200">
                    Suspend
                  </button>
                ) : (
                  <button onClick={() => { handleReactivate(viewVendor.uuid); setViewVendor(null) }}
                    className="px-3 py-1.5 bg-green-100 text-green-800 rounded-lg text-sm font-medium hover:bg-green-200">
                    Reactivate
                  </button>
                )}
              </div>
              <button onClick={() => setViewVendor(null)}
                className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
