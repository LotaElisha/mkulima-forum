import { useState, useEffect } from 'react'
import { Package, Search, Plus, Edit, Trash2, Download, AlertTriangle, CheckSquare, Square } from 'lucide-react'

export default function CatalogManager() {
  const [products, setProducts] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState([])
  const [showForm, setShowForm] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [message, setMessage] = useState('')

  useEffect(() => {
    fetchProducts()
    fetchCategories()
  }, [])

  const fetchProducts = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/catalog/products', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setProducts(data.products?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const fetchCategories = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/catalog/categories', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setCategories(data.categories || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const toggleSelect = (uuid) => {
    setSelected(prev =>
      prev.includes(uuid)
        ? prev.filter(id => id !== uuid)
        : [...prev, uuid]
    )
  }

  const toggleSelectAll = () => {
    setSelected(selected.length === products.length ? [] : products.map(p => p.uuid))
  }

  const handleBulkDelete = async () => {
    if (!confirm(`Delete ${selected.length} products?`)) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch('/api/admin/catalog/products/bulk-delete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ uuids: selected })
      })
      setSelected([])
      fetchProducts()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this product?')) return
    try {
      const token = localStorage.getItem('admin_token')
      await fetch(`/api/admin/catalog/products/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      })
      fetchProducts()
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const getStatusColor = (status) => {
    const colors = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      out_of_stock: 'bg-red-100 text-red-800'
    }
    return colors[status] || colors.inactive
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Market Catalog</h1>
          <p className="text-gray-500 mt-1">Manage agricultural inputs across East Africa</p>
        </div>
        <button
          onClick={() => { setEditingProduct(null); setShowForm(true) }}
          className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add Product
        </button>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      {/* Bulk Actions */}
      {selected.length > 0 && (
        <div className="card bg-yellow-50 border-yellow-200 flex items-center justify-between">
          <span>{selected.length} selected</span>
          <div className="flex gap-2">
            <button onClick={handleBulkDelete} className="px-3 py-1 bg-red-600 text-white rounded text-sm">Delete</button>
            <button onClick={() => setSelected([])} className="px-3 py-1 bg-gray-200 rounded text-sm">Clear</button>
          </div>
        </div>
      )}

      <div className="card overflow-hidden">
        <div className="flex gap-4 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search products..."
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="py-3 px-4">
                  <button onClick={toggleSelectAll}>
                    {selected.length === products.length && products.length > 0 ? <CheckSquare className="w-5 h-5" /> : <Square className="w-5 h-5" />}
                  </button>
                </th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Product</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Category</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Price</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Stock</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="7" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : products.length === 0 ? (
                <tr><td colSpan="7" className="py-8 text-center text-gray-500">No products found</td></tr>
              ) : (
                products.map(product => (
                  <tr key={product.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <button onClick={() => toggleSelect(product.uuid)}>
                        {selected.includes(product.uuid) ? <CheckSquare className="w-5 h-5 text-green-600" /> : <Square className="w-5 h-5" />}
                      </button>
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                          <Package className="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                          <p className="font-medium">{product.name}</p>
                          <p className="text-sm text-gray-500">{product.seller?.name}</p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">{product.category?.name}</td>
                    <td className="py-3 px-4 font-medium">TSh {product.price?.toLocaleString()}</td>
                    <td className="py-3 px-4">
                      <span className={product.stock_quantity <= 10 ? 'text-red-600 font-medium' : ''}>
                        {product.stock_quantity}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(product.status)}`}>
                        {product.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <button
                          onClick={() => { setEditingProduct(product); setShowForm(true) }}
                          className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                        >
                          <Edit className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => handleDelete(product.uuid)}
                          className="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
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
