import { useState, useEffect } from 'react'
import { Store, Search, Star, MapPin, Plus, Edit, Trash2, UserCheck, UserX, CheckCircle, XCircle, AlertTriangle, Eye, Building2, Phone, Mail, FileText, X } from 'lucide-react'

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

const ROLE_LABELS = {
  agrodealer: 'Agrodealer',
  seller: 'Seller / Agrovet',
  supplier: 'Supplier',
  partner: 'Partner',
}

export default function Vendors() {
  const [vendors, setVendors] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const [filterRole, setFilterRole] = useState('')
  const [filterKyc, setFilterKyc] = useState('')
  const [message, setMessage] = useState({ text: '', type: '' })

  // Create Modal
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [createForm, setCreateForm] = useState({
    name: '',
    phone: '',
    email: '',
    role: 'agrodealer',
    store_name: '',
    store_location: '',
    business_license: '',
    store_description: '',
    kyc_status: 'verified',
  })
  const [creating, setCreating] = useState(false)

  // Edit modal
  const [editVendor, setEditVendor] = useState(null)
  const [editForm, setEditForm] = useState({})
  const [saving, setSaving] = useState(false)

  // Detail view
  const [viewVendor, setViewVendor] = useState(null)
  const [viewStats, setViewStats] = useState(null)

  useEffect(() => {
    fetchVendors()
  }, [search, filterStatus, filterKyc, filterRole])

  const fetchVendors = async () => {
    setLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      const params = new URLSearchParams()
      if (search)       params.append('search', search)
      if (filterStatus) params.append('status', filterStatus)
      if (filterKyc)    params.append('kyc_status', filterKyc)
      if (filterRole)   params.append('role', filterRole)
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

  const handleCreateVendor = async (e) => {
    e.preventDefault()
    setCreating(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/vendors', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(createForm),
      })
      const data = await res.json()
      if (res.ok) {
        notify('Vendor / Partner registered successfully!')
        setShowCreateModal(false)
        setCreateForm({
          name: '',
          phone: '',
          email: '',
          role: 'agrodealer',
          store_name: '',
          store_location: '',
          business_license: '',
          store_description: '',
          kyc_status: 'verified',
        })
        fetchVendors()
      } else {
        notify(data.message || 'Failed to create vendor', 'error')
      }
    } catch (err) {
      notify('Network error creating vendor', 'error')
    } finally {
      setCreating(false)
    }
  }

  const openEdit = (vendor) => {
    setEditVendor(vendor)
    setEditForm({
      name: vendor.name || '',
      email: vendor.email || '',
      phone: vendor.phone || '',
      store_name: vendor.store_name || '',
      store_location: vendor.store_location || '',
      business_license: vendor.business_license || '',
      store_description: vendor.store_description || '',
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
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(editForm)
      })
      if (res.ok) {
        notify('Vendor updated successfully')
        setEditVendor(null)
        fetchVendors()
      } else {
        notify('Failed to update vendor', 'error')
      }
    } catch (err) {
      notify('Network error', 'error')
    } finally {
      setSaving(false)
    }
  }

  const handleSuspend = async (uuid) => {
    if (!confirm('Are you sure you want to suspend this vendor? All their products will be deactivated.')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}/suspend`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        notify('Vendor suspended and products deactivated')
        fetchVendors()
      }
    } catch (err) {
      notify('Failed to suspend vendor', 'error')
    }
  }

  const handleReactivate = async (uuid) => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}/reactivate`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        notify('Vendor reactivated')
        fetchVendors()
      }
    } catch (err) {
      notify('Failed to reactivate vendor', 'error')
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('PERMANENT ACTION: Delete this vendor and their products completely?')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        notify('Vendor deleted permanently')
        fetchVendors()
      }
    } catch (err) {
      notify('Failed to delete vendor', 'error')
    }
  }

  const openView = async (vendor) => {
    setViewVendor(vendor)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/vendors/${vendor.uuid}`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setViewStats(data.stats)
      }
    } catch (err) {
      console.error(err)
    }
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <Store className="w-7 h-7 text-emerald-600" /> Vendors & Partners Management
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            Manage agrodealers, agrovets, suppliers, and agricultural partners across Tanzania
          </p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-md transition-all"
        >
          <Plus className="w-4 h-4" /> Add Vendor / Partner
        </button>
      </div>

      {message.text && (
        <div className={`p-4 rounded-xl text-sm font-medium ${message.type === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`}>
          {message.text}
        </div>
      )}

      {/* Filter Bar */}
      <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-wrap gap-3 items-center justify-between">
        <div className="relative flex-1 min-w-[240px]">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Search by name, store, phone, license..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
        </div>
        <div className="flex gap-2 flex-wrap">
          <select
            value={filterRole}
            onChange={(e) => setFilterRole(e.target.value)}
            className="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">All Roles</option>
            <option value="agrodealer">Agrodealers</option>
            <option value="seller">Agrovets / Sellers</option>
            <option value="supplier">Suppliers</option>
            <option value="partner">Partners</option>
          </select>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="terminated">Terminated</option>
          </select>
          <select
            value={filterKyc}
            onChange={(e) => setFilterKyc(e.target.value)}
            className="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">All KYC</option>
            <option value="verified">KYC Verified</option>
            <option value="pending">KYC Pending</option>
            <option value="rejected">KYC Rejected</option>
          </select>
        </div>
      </div>

      {/* Vendor List Table */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-gray-500">Loading vendors & partners...</div>
        ) : vendors.length === 0 ? (
          <div className="p-8 text-center text-gray-500">No vendors found matching your criteria.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm text-gray-600">
              <thead className="bg-gray-50 text-gray-700 uppercase font-semibold text-xs border-b border-gray-100">
                <tr>
                  <th className="px-6 py-4">Vendor / Store</th>
                  <th className="px-6 py-4">Role</th>
                  <th className="px-6 py-4">Contact</th>
                  <th className="px-6 py-4">License / Location</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {vendors.map((vendor) => (
                  <tr key={vendor.uuid} className="hover:bg-gray-50/50 transition-colors">
                    <td className="px-6 py-4 font-medium text-gray-900">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center font-bold text-emerald-700">
                          {vendor.store_name?.[0] || vendor.name?.[0] || 'V'}
                        </div>
                        <div>
                          <div className="font-semibold text-gray-900">{vendor.store_name || vendor.name}</div>
                          <div className="text-xs text-gray-400">{vendor.name}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100">
                        {ROLE_LABELS[vendor.role] || vendor.role}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-xs space-y-0.5">
                        <div className="flex items-center gap-1 text-gray-700 font-medium">
                          <Phone className="w-3 h-3 text-gray-400" /> {vendor.phone}
                        </div>
                        {vendor.email && (
                          <div className="flex items-center gap-1 text-gray-400">
                            <Mail className="w-3 h-3 text-gray-400" /> {vendor.email}
                          </div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-xs space-y-0.5">
                        {vendor.store_location && (
                          <div className="flex items-center gap-1 text-gray-700">
                            <MapPin className="w-3 h-3 text-emerald-600" /> {vendor.store_location}
                          </div>
                        )}
                        {vendor.business_license ? (
                          <div className="text-emerald-700 font-mono text-[11px]">
                            Lic: {vendor.business_license}
                          </div>
                        ) : (
                          <div className="text-gray-400 text-[11px]">No License</div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex flex-col gap-1">
                        <span className={`w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold ${STATUS_COLORS[vendor.status] || 'bg-gray-100'}`}>
                          {vendor.status}
                        </span>
                        <span className={`w-fit px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${KYC_COLORS[vendor.kyc_status] || 'bg-gray-100'}`}>
                          KYC: {vendor.kyc_status}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button onClick={() => openView(vendor)} className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-emerald-600">
                          <Eye className="w-4 h-4" />
                        </button>
                        <button onClick={() => openEdit(vendor)} className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-blue-600">
                          <Edit className="w-4 h-4" />
                        </button>
                        {vendor.status === 'active' ? (
                          <button onClick={() => handleSuspend(vendor.uuid)} className="p-1.5 hover:bg-yellow-50 rounded-lg text-yellow-600" title="Suspend">
                            <UserX className="w-4 h-4" />
                          </button>
                        ) : (
                          <button onClick={() => handleReactivate(vendor.uuid)} className="p-1.5 hover:bg-green-50 rounded-lg text-green-600" title="Reactivate">
                            <UserCheck className="w-4 h-4" />
                          </button>
                        )}
                        <button onClick={() => handleDelete(vendor.uuid)} className="p-1.5 hover:bg-red-50 rounded-lg text-red-500" title="Delete">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Onboard Vendor Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
              <h3 className="text-lg font-bold text-gray-900 flex items-center gap-2">
                <Building2 className="w-5 h-5 text-emerald-600" /> Onboard Vendor / Partner
              </h3>
              <button onClick={() => setShowCreateModal(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreateVendor} className="space-y-4 text-sm">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Contact Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. John Kibo"
                  value={createForm.name}
                  onChange={(e) => setCreateForm({ ...createForm, name: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Phone Number *</label>
                  <input
                    type="text"
                    required
                    placeholder="2557XXXXXXXX"
                    value={createForm.phone}
                    onChange={(e) => setCreateForm({ ...createForm, phone: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Role *</label>
                  <select
                    value={createForm.role}
                    onChange={(e) => setCreateForm({ ...createForm, role: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="agrodealer">Agrodealer</option>
                    <option value="seller">Agrovet / Seller</option>
                    <option value="supplier">Supplier</option>
                    <option value="partner">Partner</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Store / Business Name</label>
                <input
                  type="text"
                  placeholder="e.g. Kibo Agrovet Supplies"
                  value={createForm.store_name}
                  onChange={(e) => setCreateForm({ ...createForm, store_name: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Location / City</label>
                  <input
                    type="text"
                    placeholder="e.g. Arusha Town"
                    value={createForm.store_location}
                    onChange={(e) => setCreateForm({ ...createForm, store_location: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Business License #</label>
                  <input
                    type="text"
                    placeholder="e.g. TZ-AGR-99812"
                    value={createForm.business_license}
                    onChange={(e) => setCreateForm({ ...createForm, business_license: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                <input
                  type="email"
                  placeholder="vendor@domain.co.tz"
                  value={createForm.email}
                  onChange={(e) => setCreateForm({ ...createForm, email: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Store Description / Products Offered</label>
                <textarea
                  rows="2"
                  placeholder="Authorized distributor of seeds, fertilizers, pesticides..."
                  value={createForm.store_description}
                  onChange={(e) => setCreateForm({ ...createForm, store_description: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                ></textarea>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 text-sm hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={creating}
                  className="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md disabled:opacity-50"
                >
                  {creating ? 'Registering...' : 'Register Vendor / Partner'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Edit Vendor Modal */}
      {editVendor && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 className="text-lg font-bold text-gray-900 mb-4">Edit Vendor Details</h3>
            <div className="space-y-3 text-sm">
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1">Contact Name</label>
                <input
                  type="text"
                  value={editForm.name}
                  onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-200 rounded-xl"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1">Store Name</label>
                <input
                  type="text"
                  value={editForm.store_name}
                  onChange={(e) => setEditForm({ ...editForm, store_name: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-200 rounded-xl"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1">Location</label>
                <input
                  type="text"
                  value={editForm.store_location}
                  onChange={(e) => setEditForm({ ...editForm, store_location: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-200 rounded-xl"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1">KYC Status</label>
                <select
                  value={editForm.kyc_status}
                  onChange={(e) => setEditForm({ ...editForm, kyc_status: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-200 rounded-xl bg-white"
                >
                  <option value="verified">Verified</option>
                  <option value="pending">Pending</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
            </div>
            <div className="flex justify-end gap-3 mt-6">
              <button onClick={() => setEditVendor(null)} className="px-4 py-2 border border-gray-200 rounded-xl text-gray-600">
                Cancel
              </button>
              <button onClick={handleUpdate} disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-xl font-medium shadow-md">
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* View Detail Modal */}
      {viewVendor && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-gray-100">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-xl font-bold text-gray-900">{viewVendor.store_name || viewVendor.name}</h3>
                <p className="text-sm text-gray-500">{ROLE_LABELS[viewVendor.role] || viewVendor.role} • {viewVendor.store_location || 'Tanzania'}</p>
              </div>
              <button onClick={() => setViewVendor(null)} className="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            {viewStats && (
              <div className="grid grid-cols-3 gap-3 my-4">
                <div className="bg-emerald-50 p-3 rounded-2xl text-center">
                  <div className="text-xs text-emerald-700 font-medium">Products</div>
                  <div className="text-lg font-bold text-emerald-900">{viewStats.total_products}</div>
                </div>
                <div className="bg-blue-50 p-3 rounded-2xl text-center">
                  <div className="text-xs text-blue-700 font-medium">Orders</div>
                  <div className="text-lg font-bold text-blue-900">{viewStats.total_orders}</div>
                </div>
                <div className="bg-amber-50 p-3 rounded-2xl text-center">
                  <div className="text-xs text-amber-700 font-medium">Rating</div>
                  <div className="text-lg font-bold text-amber-900 flex items-center justify-center gap-1">
                    <Star className="w-4 h-4 fill-amber-400 text-amber-400" /> {Number(viewStats.avg_rating).toFixed(1)}
                  </div>
                </div>
              </div>
            )}

            <div className="space-y-2 text-sm text-gray-600 bg-gray-50 p-4 rounded-2xl border border-gray-100">
              <div><strong className="text-gray-900">Phone:</strong> {viewVendor.phone}</div>
              <div><strong className="text-gray-900">Email:</strong> {viewVendor.email || 'N/A'}</div>
              <div><strong className="text-gray-900">Business License:</strong> {viewVendor.business_license || 'N/A'}</div>
              <div><strong className="text-gray-900">KYC Status:</strong> {viewVendor.kyc_status}</div>
              <div><strong className="text-gray-900">Account Status:</strong> {viewVendor.status}</div>
            </div>

            <div className="mt-6 flex justify-end">
              <button onClick={() => setViewVendor(null)} className="px-5 py-2 bg-gray-900 text-white rounded-xl font-medium">
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
