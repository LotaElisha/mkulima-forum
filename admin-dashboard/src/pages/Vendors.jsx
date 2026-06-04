import { useState, useEffect } from 'react'
import { Store, Search, Star, MapPin, Phone, Shield, AlertTriangle, CheckCircle } from 'lucide-react'

export default function Vendors() {
  const [vendors, setVendors] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')

  useEffect(() => {
    fetchVendors()
  }, [])

  const fetchVendors = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/vendors', {
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

  const handleSuspend = async (uuid) => {
    if (!confirm('Suspend this vendor? All products will be deactivated.')) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`http://76.13.56.180:8000/api/admin/vendors/${uuid}/suspend`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchVendors()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const handleReactivate = async (uuid) => {
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`http://76.13.56.180:8000/api/admin/vendors/${uuid}/reactivate`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchVendors()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Vendors & Partners</h1>
        <p className="text-gray-500 mt-1">Manage agrodealers, agrovets, and suppliers</p>
      </div>

      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search vendors..."
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Vendor</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Store</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Rating</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">KYC</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : vendors.length === 0 ? (
                <tr><td colSpan="6" className="py-8 text-center text-gray-500">No vendors found</td></tr>
              ) : (
                vendors.map(vendor => (
                  <tr key={vendor.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                          <Store className="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                          <p className="font-medium">{vendor.name}</p>
                          <p className="text-sm text-gray-500">{vendor.phone}</p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <p>{vendor.store_name || '-'}</p>
                      <p className="text-sm text-gray-500 flex items-center gap-1">
                        <MapPin className="w-3 h-3" />
                        {vendor.store_location || 'No location'}
                      </p>
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-1">
                        <Star className="w-4 h-4 text-yellow-500 fill-yellow-500" />
                        <span className="font-medium">{vendor.rating_avg || 0}</span>
                        <span className="text-sm text-gray-500">({vendor.rating_count || 0})</span>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        vendor.kyc_status === 'verified' ? 'bg-green-100 text-green-800' :
                        vendor.kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {vendor.kyc_status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        vendor.status === 'active' ? 'bg-green-100 text-green-800' :
                        vendor.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {vendor.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      {vendor.status === 'active' ? (
                        <button
                          onClick={() => handleSuspend(vendor.uuid)}
                          className="px-3 py-1 bg-yellow-100 text-yellow-800 rounded text-sm hover:bg-yellow-200"
                        >
                          Suspend
                        </button>
                      ) : (
                        <button
                          onClick={() => handleReactivate(vendor.uuid)}
                          className="px-3 py-1 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200"
                        >
                          Reactivate
                        </button>
                      )}
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
