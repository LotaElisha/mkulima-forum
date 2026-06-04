import { useState, useEffect } from 'react'
import { ShoppingCart, Search, Edit, Trash2, Package, Truck, CheckCircle, XCircle, Clock } from 'lucide-react'

export default function OrdersPage() {
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const [editingOrder, setEditingOrder] = useState(null)
  const [showEditForm, setShowEditForm] = useState(false)
  const [message, setMessage] = useState('')
  const [editData, setEditData] = useState({ status: '', payment_status: '', notes: '' })

  useEffect(() => {
    fetchOrders()
  }, [search, filterStatus])

  const fetchOrders = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      let url = 'http://76.13.56.180:8000/api/admin/orders'
      const params = new URLSearchParams()
      if (filterStatus) params.append('status', filterStatus)
      if (params.toString()) url += '?' + params.toString()

      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } })
      if (res.ok) {
        const data = await res.json()
        setOrders(data.orders?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleUpdate = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`http://76.13.56.180:8000/api/admin/orders/${editingOrder.uuid}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(editData)
      })
      const data = await res.json()
      if (res.ok) {
        setMessage('Order updated!')
        setShowEditForm(false)
        setEditingOrder(null)
        fetchOrders()
      } else {
        setMessage(data.message || 'Failed')
      }
    } catch (err) {
      setMessage('Network error')
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this order permanently?')) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`http://76.13.56.180:8000/api/admin/orders/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchOrders()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const openEdit = (order) => {
    setEditingOrder(order)
    setEditData({
      status: order.status,
      payment_status: order.payment_status || 'pending',
      notes: order.notes || ''
    })
    setShowEditForm(true)
  }

  const getStatusIcon = (status) => {
    switch (status) {
      case 'completed': case 'delivered': return <CheckCircle className="w-4 h-4 text-green-600" />
      case 'cancelled': case 'refunded': return <XCircle className="w-4 h-4 text-red-600" />
      case 'shipped': return <Truck className="w-4 h-4 text-blue-600" />
      case 'processing': return <Package className="w-4 h-4 text-purple-600" />
      default: return <Clock className="w-4 h-4 text-yellow-600" />
    }
  }

  const getStatusColor = (status) => {
    const colors = {
      pending: 'bg-yellow-100 text-yellow-800',
      paid: 'bg-blue-100 text-blue-800',
      processing: 'bg-purple-100 text-purple-800',
      shipped: 'bg-indigo-100 text-indigo-800',
      delivered: 'bg-green-100 text-green-800',
      completed: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800',
      refunded: 'bg-gray-100 text-gray-800',
    }
    return colors[status] || colors.pending
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Orders Management</h1>
        <p className="text-gray-500 mt-1">View, update, and manage all orders</p>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      {showEditForm && editingOrder && (
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Edit Order #{editingOrder.uuid?.substring(0, 8)}</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select value={editData.status} onChange={(e) => setEditData({...editData, status: e.target.value})} className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
              <select value={editData.payment_status} onChange={(e) => setEditData({...editData, payment_status: e.target.value})} className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <input value={editData.notes} onChange={(e) => setEditData({...editData, notes: e.target.value})} className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
            </div>
          </div>
          <div className="flex gap-3 mt-4">
            <button onClick={handleUpdate} className="btn-primary">Update Order</button>
            <button onClick={() => { setShowEditForm(false); setEditingOrder(null) }} className="btn-secondary">Cancel</button>
          </div>
        </div>
      )}

      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input type="text" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search orders..." className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none" />
          </div>
          <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Order</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Customer</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Total</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Date</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : orders.length === 0 ? (
                <tr><td colSpan="6" className="py-8 text-center text-gray-500">No orders found</td></tr>
              ) : (
                orders.map(order => (
                  <tr key={order.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-2">
                        {getStatusIcon(order.status)}
                        <span className="font-mono text-sm">#{order.uuid?.substring(0, 8)}</span>
                      </div>
                      <p className="text-xs text-gray-500 mt-1">{order.source === 'pos' ? 'POS' : 'Online'}</p>
                    </td>
                    <td className="py-3 px-4">
                      <p className="font-medium">{order.buyer?.name || 'Unknown'}</p>
                      <p className="text-sm text-gray-500">{order.buyer?.phone}</p>
                    </td>
                    <td className="py-3 px-4 font-medium">TSh {order.total?.toLocaleString()}</td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(order.status)}`}>
                        {order.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-sm text-gray-500">{new Date(order.created_at).toLocaleDateString()}</td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <button onClick={() => openEdit(order)} className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit"><Edit className="w-4 h-4" /></button>
                        <button onClick={() => handleDelete(order.uuid)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><Trash2 className="w-4 h-4" /></button>
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
