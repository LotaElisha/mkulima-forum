import { useState, useEffect } from 'react'
import { TrendingUp, TrendingDown, Minus, Plus, Trash2 } from 'lucide-react'

const EMPTY_FORM = {
  commodity: '',
  market: '',
  region: '',
  min_price: '',
  max_price: '',
  unit: 'gunia la kg 100',
  price_date: new Date().toISOString().slice(0, 10),
  source: '',
}

export default function MarketPricesPage() {
  const [prices, setPrices] = useState([])
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState(EMPTY_FORM)

  useEffect(() => {
    fetchPrices()
  }, [])

  const fetchPrices = async () => {
    setLoading(true)
    try {
      const res = await fetch('/api/market-prices?per_page=50')
      if (res.ok) {
        const data = await res.json()
        setPrices(data.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/market-prices', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...form,
          min_price: parseFloat(form.min_price),
          max_price: parseFloat(form.max_price),
        }),
      })
      const data = await res.json()
      if (res.ok) {
        setMessage(data.message || 'Price recorded')
        setForm(EMPTY_FORM)
        setShowForm(false)
        fetchPrices()
      } else {
        setMessage(data.message || 'Validation failed')
      }
    } catch {
      setMessage('Failed to record price')
    }
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this price record?')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/market-prices/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` },
      })
      const data = await res.json()
      setMessage(data.message || 'Deleted')
      fetchPrices()
    } catch {
      setMessage('Failed to delete')
    }
  }

  const TrendIcon = ({ trend }) =>
    trend === 'up' ? (
      <TrendingUp size={16} className="text-green-600" />
    ) : trend === 'down' ? (
      <TrendingDown size={16} className="text-red-600" />
    ) : (
      <Minus size={16} className="text-gray-400" />
    )

  return (
    <div className="p-6">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold">Market Prices</h1>
        <button
          onClick={() => setShowForm(!showForm)}
          className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          <Plus size={16} /> Record price
        </button>
      </div>

      {message && (
        <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800">
          {message}
        </div>
      )}

      {showForm && (
        <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-4 mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
          {[
            ['commodity', 'Commodity (e.g. mahindi)'],
            ['market', 'Market (e.g. Kariakoo)'],
            ['region', 'Region'],
            ['unit', 'Unit'],
            ['min_price', 'Min price (TZS)'],
            ['max_price', 'Max price (TZS)'],
            ['price_date', 'Price date'],
            ['source', 'Source (optional)'],
          ].map(([key, label]) => (
            <label key={key} className="text-sm">
              <span className="text-gray-600">{label}</span>
              <input
                type={key.includes('price') && key !== 'price_date' ? 'number' : key === 'price_date' ? 'date' : 'text'}
                required={key !== 'source'}
                value={form[key]}
                onChange={(e) => setForm({ ...form, [key]: e.target.value })}
                className="mt-1 w-full border rounded-lg px-3 py-2"
              />
            </label>
          ))}
          <div className="col-span-full">
            <button type="submit" className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
              Save
            </button>
          </div>
        </form>
      )}

      {loading ? (
        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600 mx-auto mt-16" />
      ) : prices.length === 0 ? (
        <div className="text-center text-gray-500 mt-16">
          No prices recorded yet. Record the first market price above.
        </div>
      ) : (
        <div className="bg-white rounded-xl shadow overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-left text-gray-600">
              <tr>
                <th className="p-3">Commodity</th>
                <th className="p-3">Market</th>
                <th className="p-3">Region</th>
                <th className="p-3">Min–Max (TZS)</th>
                <th className="p-3">Unit</th>
                <th className="p-3">Date</th>
                <th className="p-3">Trend</th>
                <th className="p-3">Source</th>
                <th className="p-3"></th>
              </tr>
            </thead>
            <tbody>
              {prices.map((p) => (
                <tr key={p.uuid} className="border-t">
                  <td className="p-3 font-medium">{p.commodity}</td>
                  <td className="p-3">{p.market}</td>
                  <td className="p-3">{p.region}</td>
                  <td className="p-3">
                    {Number(p.min_price).toLocaleString()} – {Number(p.max_price).toLocaleString()}
                  </td>
                  <td className="p-3">{p.unit}</td>
                  <td className="p-3">
                    {p.price_date}
                    {p.is_stale && (
                      <span className="ml-2 text-xs text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">stale</span>
                    )}
                  </td>
                  <td className="p-3"><TrendIcon trend={p.trend} /></td>
                  <td className="p-3 text-gray-500">{p.source || '—'}</td>
                  <td className="p-3">
                    <button onClick={() => handleDelete(p.uuid)} className="text-red-500 hover:text-red-700">
                      <Trash2 size={16} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
