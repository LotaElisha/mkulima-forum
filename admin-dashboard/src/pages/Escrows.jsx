import { useState, useEffect } from 'react'
import { Shield, CheckCircle, XCircle, DollarSign, AlertTriangle } from 'lucide-react'

export default function EscrowsPage() {
  const [escrows, setEscrows] = useState([])
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [filterStatus, setFilterStatus] = useState('')

  useEffect(() => {
    fetchEscrows()
  }, [filterStatus])

  const fetchEscrows = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      let url = '/api/admin/escrows'
      if (filterStatus) url += `?status=${filterStatus}`
      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } })
      if (res.ok) {
        const data = await res.json()
        setEscrows(data.escrows?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleRelease = async (uuid) => {
    if (!confirm('Release funds to seller?')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/escrows/${uuid}/release`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      const data = await res.json()
      setMessage(data.message || 'Funds released')
      fetchEscrows()
    } catch (err) {
      setMessage('Failed to release')
    }
  }

  const handleRefund = async (uuid) => {
    const reason = prompt('Refund reason:')
    if (!reason) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/escrows/${uuid}/refund`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ reason })
      })
      const data = await res.json()
      setMessage(data.message || 'Buyer refunded')
      fetchEscrows()
    } catch (err) {
      setMessage('Failed to refund')
    }
  }

  const getStatusColor = (status) => {
    const colors = {
      held: 'bg-yellow-100 text-yellow-800',
      released: 'bg-green-100 text-green-800',
      refunded: 'bg-red-100 text-red-800',
      pending: 'bg-blue-100 text-blue-800',
    }
    return colors[status] || colors.pending
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Escrow Management</h1>
        <p className="text-gray-500 mt-1">Manage escrow transactions and fund releases</p>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="card">
          <div className="flex items-center gap-3 mb-2">
            <Shield className="w-5 h-5 text-yellow-600" />
            <span className="text-sm text-gray-500">Held</span>
          </div>
          <p className="text-2xl font-bold">{escrows.filter(e => e.status === 'held').length}</p>
        </div>
        <div className="card">
          <div className="flex items-center gap-3 mb-2">
            <CheckCircle className="w-5 h-5 text-green-600" />
            <span className="text-sm text-gray-500">Released</span>
          </div>
          <p className="text-2xl font-bold">{escrows.filter(e => e.status === 'released').length}</p>
        </div>
        <div className="card">
          <div className="flex items-center gap-3 mb-2">
            <XCircle className="w-5 h-5 text-red-600" />
            <span className="text-sm text-gray-500">Refunded</span>
          </div>
          <p className="text-2xl font-bold">{escrows.filter(e => e.status === 'refunded').length}</p>
        </div>
        <div className="card">
          <div className="flex items-center gap-3 mb-2">
            <DollarSign className="w-5 h-5 text-blue-600" />
            <span className="text-sm text-gray-500">Total Held</span>
          </div>
          <p className="text-2xl font-bold">TSh {escrows.filter(e => e.status === 'held').reduce((sum, e) => sum + (e.amount || 0), 0).toLocaleString()}</p>
        </div>
      </div>

      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            <option value="">All Status</option>
            <option value="held">Held</option>
            <option value="released">Released</option>
            <option value="refunded">Refunded</option>
            <option value="pending">Pending</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Order</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Buyer</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Seller</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Amount</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : escrows.length === 0 ? (
                <tr><td colSpan="6" className="py-8 text-center text-gray-500">No escrow transactions</td></tr>
              ) : (
                escrows.map(escrow => (
                  <tr key={escrow.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 font-mono text-sm">#{escrow.order?.uuid?.substring(0, 8)}</td>
                    <td className="py-3 px-4">{escrow.buyer?.name}</td>
                    <td className="py-3 px-4">{escrow.seller?.name}</td>
                    <td className="py-3 px-4 font-medium">TSh {escrow.amount?.toLocaleString()}</td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(escrow.status)}`}>
                        {escrow.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      {escrow.status === 'held' && (
                        <div className="flex gap-2">
                          <button onClick={() => handleRelease(escrow.uuid)} className="px-3 py-1 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200">Release</button>
                          <button onClick={() => handleRefund(escrow.uuid)} className="px-3 py-1 bg-red-100 text-red-800 rounded text-sm hover:bg-red-200">Refund</button>
                        </div>
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
