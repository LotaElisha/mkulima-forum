import { useState, useEffect } from 'react'
import { ShoppingCart, Plus, Minus, Trash2, Receipt, Search, CreditCard, Smartphone, Banknote } from 'lucide-react'

export default function PosTerminal() {
  const [products, setProducts] = useState([])
  const [cart, setCart] = useState([])
  const [search, setSearch] = useState('')
  const [customer, setCustomer] = useState({ name: '', phone: '' })
  const [paymentMethod, setPaymentMethod] = useState('cash')
  const [vatRate, setVatRate] = useState(18)
  const [discount, setDiscount] = useState(0)
  const [notes, setNotes] = useState('')
  const [location, setLocation] = useState('')
  const [receipt, setReceipt] = useState(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    fetchProducts()
  }, [])

  const fetchProducts = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/pos/products', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setProducts(data.products || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    }
  }

  const addToCart = (product) => {
    const existing = cart.find(item => item.product_id === product.id)
    if (existing) {
      setCart(cart.map(item =>
        item.product_id === product.id
          ? { ...item, quantity: item.quantity + 1 }
          : item
      ))
    } else {
      setCart([...cart, {
        product_id: product.id,
        name: product.name,
        unit_price: product.price,
        quantity: 1
      }])
    }
  }

  const updateQty = (productId, delta) => {
    setCart(cart.map(item => {
      if (item.product_id === productId) {
        const newQty = Math.max(1, item.quantity + delta)
        return { ...item, quantity: newQty }
      }
      return item
    }))
  }

  const removeFromCart = (productId) => {
    setCart(cart.filter(item => item.product_id !== productId))
  }

  const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0)
  const vatAmount = subtotal * (vatRate / 100)
  const total = subtotal + vatAmount - discount

  const handleCheckout = async () => {
    if (cart.length === 0) return
    if (!customer.name || !customer.phone) {
      alert('Please enter customer details')
      return
    }
    setLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/pos/orders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          customer_name: customer.name,
          customer_phone: customer.phone,
          items: cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price
          })),
          payment_method: paymentMethod,
          vat_rate: vatRate,
          discount: discount,
          notes: notes,
          location: location
        })
      })
      const data = await res.json()
      if (res.ok) {
        setReceipt(data.receipt)
        setCart([])
        setCustomer({ name: '', phone: '' })
      } else {
        alert(data.message || 'Checkout failed')
      }
    } catch (err) {
      alert('Network error')
    } finally {
      setLoading(false)
    }
  }

  const filteredProducts = products.filter(p =>
    p.name?.toLowerCase().includes(search.toLowerCase()) ||
    p.description?.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Field POS Terminal</h1>
        <p className="text-gray-500 mt-1">Process sales at agricultural trade shows and markets</p>
      </div>

      {receipt && (
        <div className="card bg-green-50 border-green-200">
          <div className="flex items-center gap-2 mb-4">
            <Receipt className="w-6 h-6 text-green-600" />
            <h2 className="text-lg font-bold text-green-800">Receipt Generated!</h2>
          </div>
          <div className="bg-white p-4 rounded-lg">
            <p><strong>Order:</strong> {receipt.order_number}</p>
            <p><strong>Customer:</strong> {receipt.customer}</p>
            <p><strong>Total:</strong> TSh {receipt.total?.toLocaleString()}</p>
            <p><strong>Payment:</strong> {receipt.payment_method}</p>
          </div>
          <button onClick={() => setReceipt(null)} className="btn-secondary mt-4">Close</button>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Products */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Products</h2>
          <div className="relative mb-4">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search products..."
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
            />
          </div>
          <div className="space-y-2 max-h-96 overflow-y-auto">
            {filteredProducts.length === 0 ? (
              <p className="text-gray-500 text-center py-8">No products available</p>
            ) : (
              filteredProducts.map(product => (
                <div
                  key={product.id}
                  onClick={() => addToCart(product)}
                  className="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-green-50 cursor-pointer transition-colors"
                >
                  <div>
                    <p className="font-medium">{product.name}</p>
                    <p className="text-sm text-gray-500">Stock: {product.stock_quantity} {product.unit}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="font-bold text-green-700">TSh {product.price?.toLocaleString()}</span>
                    <Plus className="w-5 h-5 text-green-600" />
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Cart */}
        <div className="space-y-4">
          <div className="card">
            <h2 className="text-lg font-semibold mb-4 flex items-center gap-2">
              <ShoppingCart className="w-5 h-5" />
              Cart ({cart.length} items)
            </h2>

            {cart.length === 0 ? (
              <p className="text-gray-500 text-center py-8">Cart is empty</p>
            ) : (
              <div className="space-y-3">
                {cart.map(item => (
                  <div key={item.product_id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div className="flex-1">
                      <p className="font-medium">{item.name}</p>
                      <p className="text-sm text-gray-500">TSh {item.unit_price?.toLocaleString()} each</p>
                    </div>
                    <div className="flex items-center gap-3">
                      <button onClick={() => updateQty(item.product_id, -1)} className="p-1 hover:bg-gray-200 rounded">
                        <Minus className="w-4 h-4" />
                      </button>
                      <span className="font-medium w-8 text-center">{item.quantity}</span>
                      <button onClick={() => updateQty(item.product_id, 1)} className="p-1 hover:bg-gray-200 rounded">
                        <Plus className="w-4 h-4" />
                      </button>
                      <button onClick={() => removeFromCart(item.product_id)} className="p-1 text-red-600 hover:bg-red-50 rounded ml-2">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Customer Info */}
          <div className="card">
            <h2 className="text-lg font-semibold mb-4">Customer</h2>
            <div className="space-y-3">
              <input
                placeholder="Customer Name"
                value={customer.name}
                onChange={(e) => setCustomer({...customer, name: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
              <input
                placeholder="Phone (255...)"
                value={customer.phone}
                onChange={(e) => setCustomer({...customer, phone: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
              <input
                placeholder="Location (optional)"
                value={location}
                onChange={(e) => setLocation(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
            </div>
          </div>

          {/* Payment */}
          <div className="card">
            <h2 className="text-lg font-semibold mb-4">Payment</h2>
            <div className="grid grid-cols-3 gap-2 mb-4">
              {[
                { id: 'cash', icon: Banknote, label: 'Cash' },
                { id: 'm-pesa', icon: Smartphone, label: 'M-Pesa' },
                { id: 'tigo_pesa', icon: Smartphone, label: 'Tigo Pesa' },
              ].map(method => (
                <button
                  key={method.id}
                  onClick={() => setPaymentMethod(method.id)}
                  className={`p-3 rounded-lg border-2 flex flex-col items-center gap-1 transition-colors ${
                    paymentMethod === method.id
                      ? 'border-green-500 bg-green-50 text-green-700'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <method.icon className="w-5 h-5" />
                  <span className="text-sm">{method.label}</span>
                </button>
              ))}
            </div>

            <div className="space-y-2">
              <div className="flex justify-between">
                <span>Subtotal:</span>
                <span>TSh {subtotal.toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span>VAT ({vatRate}%):</span>
                <span>TSh {vatAmount.toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span>Discount:</span>
                <input
                  type="number"
                  value={discount}
                  onChange={(e) => setDiscount(Number(e.target.value))}
                  className="w-24 px-2 py-1 border border-gray-300 rounded text-right"
                />
              </div>
              <div className="flex justify-between text-xl font-bold border-t pt-2">
                <span>Total:</span>
                <span className="text-green-700">TSh {total.toLocaleString()}</span>
              </div>
            </div>

            <button
              onClick={handleCheckout}
              disabled={loading || cart.length === 0}
              className="w-full mt-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-300 transition-colors font-semibold"
            >
              {loading ? 'Processing...' : 'Complete Sale'}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
