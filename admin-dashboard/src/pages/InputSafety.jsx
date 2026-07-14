import { useState, useEffect, useCallback } from 'react'
import {
  ShieldAlert, CheckCircle, XCircle, Plus, Trash2, Edit,
  Eye, Image, Upload, Search, FileText, AlertTriangle, Check
} from 'lucide-react'

const EMPTY_INPUT = {
  name: '',
  type: 'pesticide',
  registration_number: '',
  manufacturer: '',
  distributor: '',
  status: 'registered',
  source: '',
  source_date: '',
}

const TYPES = ['pesticide', 'herbicide', 'fungicide', 'insecticide', 'fertilizer', 'vet_product', 'seed']

const TYPE_BADGES = {
  pesticide: 'bg-red-100 text-red-800 border-red-200',
  herbicide: 'bg-orange-100 text-orange-800 border-orange-200',
  fungicide: 'bg-blue-100 text-blue-800 border-blue-200',
  insecticide: 'bg-purple-100 text-purple-800 border-purple-200',
  fertilizer: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  vet_product: 'bg-indigo-100 text-indigo-800 border-indigo-200',
  seed: 'bg-amber-100 text-amber-800 border-amber-200',
}

export default function InputSafetyPage() {
  const [tab, setTab] = useState('alerts')
  const [alerts, setAlerts] = useState([])
  const [registry, setRegistry] = useState([])
  const [registryQuery, setRegistryQuery] = useState('')
  const [alertStatus, setAlertStatus] = useState('pending')
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState({ text: '', type: '' })
  
  // Registry forms
  const [showForm, setShowForm] = useState(false)
  const [editInput, setEditInput] = useState(null)
  const [form, setForm] = useState(EMPTY_INPUT)
  const [saving, setSaving] = useState(false)

  // AI Label Scanner
  const [scanImage, setScanImage] = useState(null)
  const [scanPreview, setScanPreview] = useState(null)
  const [scanning, setScanning] = useState(false)
  const [scanResult, setScanResult] = useState(null)

  // Fullscreen photo modal
  const [activePhoto, setActivePhoto] = useState(null)

  const token = () => localStorage.getItem('admin_token')

  const notify = (text, type = 'success') => {
    setMessage({ text, type })
    setTimeout(() => setMessage({ text: '', type: '' }), 5000)
  }

  const fetchAlerts = useCallback(async () => {
    setLoading(true)
    try {
      const res = await fetch(`/api/admin/input-alerts?status=${alertStatus}`, {
        headers: { Authorization: `Bearer ${token()}` },
      })
      if (res.ok) setAlerts((await res.json()).data || [])
    } catch {
      notify('Failed to load alerts', 'error')
    } finally {
      setLoading(false)
    }
  }, [alertStatus])

  const fetchRegistry = useCallback(async () => {
    setLoading(true)
    try {
      const q = registryQuery ? `?q=${encodeURIComponent(registryQuery)}` : ''
      const res = await fetch(`/api/admin/inputs${q}`, {
        headers: { Authorization: `Bearer ${token()}` },
      })
      if (res.ok) setRegistry((await res.json()).data || [])
    } catch {
      notify('Failed to load registry', 'error')
    } finally {
      setLoading(false)
    }
  }, [registryQuery])

  useEffect(() => {
    if (tab === 'alerts') fetchAlerts()
    else if (tab === 'registry') fetchRegistry()
  }, [tab, fetchAlerts, fetchRegistry])

  const review = async (uuid, decision) => {
    const notes = prompt(decision === 'confirmed'
      ? 'Umehakiki? Weka maelezo ya uthibitisho (Verification Notes):'
      : 'Kwa nini inakataliwa? (Reason for dismissal):')
    if (notes === null) return // Canceled

    try {
      const res = await fetch(`/api/admin/input-alerts/${uuid}/review`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token()}` },
        body: JSON.stringify({ decision, notes }),
      })
      const data = await res.json()
      if (res.ok) {
        notify(data.message || 'Alert updated')
        fetchAlerts()
      } else {
        notify(data.message || 'Review failed', 'error')
      }
    } catch {
      notify('Network error', 'error')
    }
  }

  const saveInput = async (e) => {
    e.preventDefault()
    setSaving(true)
    try {
      const url = editInput ? `/api/admin/inputs/${editInput.uuid}` : '/api/admin/inputs'
      const method = editInput ? 'PUT' : 'POST'
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token()}` },
        body: JSON.stringify(form),
      })
      const data = await res.json()
      if (res.ok) {
        notify(editInput ? 'Entry updated successfully' : 'Entry added to registry successfully')
        setForm(EMPTY_INPUT)
        setShowForm(false)
        setEditInput(null)
        fetchRegistry()
      } else {
        notify(data.message || 'Failed to save registry entry', 'error')
      }
    } catch {
      notify('Network error', 'error')
    } finally {
      setSaving(false)
    }
  }

  const openEdit = (input) => {
    setEditInput(input)
    setForm({
      name: input.name || '',
      type: input.type || 'pesticide',
      registration_number: input.registration_number || '',
      manufacturer: input.manufacturer || '',
      distributor: input.distributor || '',
      status: input.status || 'registered',
      source: input.source || '',
      source_date: input.source_date || '',
    })
    setShowForm(true)
  }

  const deleteInput = async (uuid) => {
    if (!confirm('Ondoa bidhaa hii kwenye orodha ya usajili? (Permanently delete this entry?)')) return
    try {
      const res = await fetch(`/api/admin/inputs/${uuid}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token()}` },
      })
      if (res.ok) {
        notify('Registry entry removed')
        fetchRegistry()
      } else {
        notify('Failed to delete entry', 'error')
      }
    } catch {
      notify('Network error', 'error')
    }
  }

  // AI Scanner upload & run
  const handleImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
      setScanImage(file)
      const reader = new FileReader()
      reader.onloadend = () => {
        setScanPreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
  }

  const runScanner = async () => {
    if (!scanImage) return
    setScanning(true)
    setScanResult(null)
    try {
      const formData = new FormData()
      formData.append('image', scanImage)
      
      const res = await fetch('/api/inputs/check-label', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token()}` },
        body: formData
      })
      const data = await res.json()
      if (res.ok) {
        setScanResult(data)
      } else {
        notify(data.message || 'AI label scanning failed', 'error')
      }
    } catch (err) {
      notify('Failed to connect to scanner', 'error')
    } finally {
      setScanning(false)
    }
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-4">
        <div className="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center border border-red-200">
          <ShieldAlert className="w-6 h-6 text-red-600" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Input Safety (Kagua Dawa)</h1>
          <p className="text-gray-500 text-sm">Verify medicine/chemical registrations, check alerts, and run AI counterfeit analysis</p>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 border-b border-gray-200 overflow-x-auto">
        <button
          onClick={() => setTab('alerts')}
          className={`flex items-center gap-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors ${
            tab === 'alerts'
              ? 'border-red-600 text-red-700'
              : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
          }`}
        >
          <ShieldAlert className="w-4 h-4" />
          Counterfeit Alerts
        </button>
        <button
          onClick={() => setTab('registry')}
          className={`flex items-center gap-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors ${
            tab === 'registry'
              ? 'border-green-600 text-green-700'
              : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
          }`}
        >
          <FileText className="w-4 h-4" />
          Official Registry
        </button>
        <button
          onClick={() => setTab('scanner')}
          className={`flex items-center gap-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors ${
            tab === 'scanner'
              ? 'border-blue-600 text-blue-700'
              : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
          }`}
        >
          <Upload className="w-4 h-4" />
          AI Label Scanner
        </button>
      </div>

      {/* Notification Banner */}
      {message.text && (
        <div className={`p-4 rounded-lg border text-sm font-medium ${
          message.type === 'error'
            ? 'bg-red-50 border-red-200 text-red-700'
            : 'bg-green-50 border-green-200 text-green-700'
        }`}>
          {message.text}
        </div>
      )}

      {/* Counterfeit Alerts Tab */}
      {tab === 'alerts' && (
        <div className="space-y-4">
          <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <span className="text-sm font-medium text-gray-700">Filter Alerts</span>
            <select
              value={alertStatus}
              onChange={(e) => setAlertStatus(e.target.value)}
              className="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none"
            >
              <option value="pending">Pending Review</option>
              <option value="confirmed">Confirmed Fake / Banned</option>
              <option value="dismissed">Dismissed</option>
            </select>
          </div>

          {loading ? (
            <div className="flex justify-center py-12">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-red-600" />
            </div>
          ) : alerts.length === 0 ? (
            <div className="card text-center py-12 text-gray-500">
              <ShieldAlert className="w-12 h-12 mx-auto mb-2 text-gray-300" />
              <p className="font-medium">No alerts in this queue.</p>
            </div>
          ) : (
            <div className="space-y-4">
              {alerts.map((a) => (
                <div key={a.uuid} className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col md:flex-row gap-5">
                  {/* Report Details */}
                  <div className="flex-1 space-y-3 min-w-0">
                    <div className="flex items-center gap-2 flex-wrap">
                      <h3 className="font-bold text-gray-900 text-base">{a.product_name}</h3>
                      <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-gray-100 text-gray-700 border">
                        {a.product_type || 'Unknown'}
                      </span>
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                      <div><span className="font-semibold text-gray-700">Location:</span> {a.region}{a.district ? `, ${a.district}` : ''}</div>
                      <div><span className="font-semibold text-gray-700">Dealer:</span> {a.dealer_name || 'Not provided'}</div>
                      <div><span className="font-semibold text-gray-700">Reg No:</span> {a.registration_number || 'N/A'}</div>
                      <div><span className="font-semibold text-gray-700">Batch No:</span> {a.batch_number || 'N/A'}</div>
                      <div className="col-span-2"><span className="font-semibold text-gray-700">Reporter:</span> {a.reporter?.name || 'Anonymous'} ({a.reporter?.role || 'user'})</div>
                    </div>

                    <div>
                      <p className="text-xs font-semibold text-gray-700 mb-1">Issue Description:</p>
                      <p className="text-sm text-gray-800 bg-red-50/50 border border-red-100 p-3 rounded-lg leading-relaxed">{a.description}</p>
                    </div>

                    {a.admin_notes && (
                      <div className="bg-amber-50 border border-amber-100 p-3 rounded-lg text-sm text-amber-900">
                        <span className="font-semibold">Reviewer Notes:</span> {a.admin_notes}
                      </div>
                    )}
                  </div>

                  {/* Photo & Actions */}
                  <div className="md:w-60 flex flex-col justify-between gap-4 shrink-0 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-5">
                    {a.photo_path ? (
                      <div className="relative group overflow-hidden rounded-lg border bg-gray-50 aspect-video md:aspect-auto md:h-32 flex items-center justify-center">
                        <img
                          src={`/storage/${a.photo_path}`}
                          alt="Report attachment"
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                        />
                        <button
                          onClick={() => setActivePhoto(`/storage/${a.photo_path}`)}
                          className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-1.5 text-white font-medium text-xs transition-opacity"
                        >
                          <Eye className="w-4 h-4" /> View Fullscreen
                        </button>
                      </div>
                    ) : (
                      <div className="rounded-lg border border-dashed border-gray-200 bg-gray-50 h-32 flex flex-col items-center justify-center text-gray-400">
                        <Image className="w-8 h-8 opacity-40 mb-1" />
                        <span className="text-xs">No image uploaded</span>
                      </div>
                    )}

                    {a.status === 'pending' && (
                      <div className="flex gap-2">
                        <button
                          onClick={() => review(a.uuid, 'confirmed')}
                          className="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors"
                        >
                          <CheckCircle className="w-4 h-4" /> Confirm
                        </button>
                        <button
                          onClick={() => review(a.uuid, 'dismissed')}
                          className="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors border"
                        >
                          <XCircle className="w-4 h-4" /> Dismiss
                        </button>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Official Registry Tab */}
      {tab === 'registry' && (
        <div className="space-y-4">
          <div className="flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                value={registryQuery}
                onChange={(e) => setRegistryQuery(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && fetchRegistry()}
                placeholder="Search name, active ingredient, or registration number…"
                className="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-sm"
              />
            </div>
            <button
              onClick={() => {
                setEditInput(null)
                setForm(EMPTY_INPUT)
                setShowForm(!showForm)
              }}
              className="flex items-center justify-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 text-sm font-semibold transition-colors"
            >
              <Plus className="w-4 h-4" /> Add Registry Entry
            </button>
          </div>

          {/* Add/Edit Form */}
          {showForm && (
            <form onSubmit={saveInput} className="bg-white rounded-xl shadow-sm border border-green-200 p-5 space-y-4">
              <h3 className="font-bold text-gray-900 border-b pb-2 text-sm">
                {editInput ? 'Edit Registry Product' : 'Register New Crop/Vet Input'}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Product Name</label>
                  <input
                    required
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                    placeholder="e.g. RoundUp, DuduAll 450 EC"
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Input Type</label>
                  <select
                    value={form.type}
                    onChange={(e) => setForm({ ...form, type: e.target.value })}
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  >
                    {TYPES.map((t) => <option key={t} value={t}>{t.replace('_', ' ')}</option>)}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Registration No.</label>
                  <input
                    value={form.registration_number}
                    onChange={(e) => setForm({ ...form, registration_number: e.target.value })}
                    placeholder="e.g. IP/2023/0890"
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Manufacturer</label>
                  <input
                    value={form.manufacturer}
                    onChange={(e) => setForm({ ...form, manufacturer: e.target.value })}
                    placeholder="e.g. Syngenta, CropLife"
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Local Distributor</label>
                  <input
                    value={form.distributor}
                    onChange={(e) => setForm({ ...form, distributor: e.target.value })}
                    placeholder="e.g. Agrovet Tanzania Ltd"
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                  <select
                    value={form.status}
                    onChange={(e) => setForm({ ...form, status: e.target.value })}
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  >
                    <option value="registered">Registered (Approved)</option>
                    <option value="banned">Banned (Strictly Prohibited)</option>
                    <option value="withdrawn">Withdrawn (Inactive)</option>
                  </select>
                </div>
                <div className="md:col-span-2">
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Official Registry Source List</label>
                  <input
                    required
                    value={form.source}
                    onChange={(e) => setForm({ ...form, source: e.target.value })}
                    placeholder="e.g. TPRI Registered Pesticides list 2026 Edition"
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1">Source Update Date</label>
                  <input
                    type="date"
                    value={form.source_date}
                    onChange={(e) => setForm({ ...form, source_date: e.target.value })}
                    className="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  type="submit"
                  disabled={saving}
                  className="px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg text-sm font-semibold transition-colors disabled:opacity-50"
                >
                  {saving ? 'Saving...' : 'Save Product'}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false)
                    setEditInput(null)
                  }}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          )}

          {loading ? (
            <div className="flex justify-center py-12">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600" />
            </div>
          ) : registry.length === 0 ? (
            <div className="card text-center py-12 text-gray-500">
              <FileText className="w-12 h-12 mx-auto mb-2 text-gray-300" />
              <p className="font-medium">No registry entries found.</p>
            </div>
          ) : (
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead className="bg-gray-50 border-b text-left text-gray-600 font-semibold">
                    <tr>
                      <th className="p-4">Name</th>
                      <th className="p-4">Type</th>
                      <th className="p-4">Reg. Number</th>
                      <th className="p-4">Manufacturer</th>
                      <th className="p-4">Status</th>
                      <th className="p-4">Source</th>
                      <th className="p-4">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {registry.map((i) => (
                      <tr key={i.uuid} className="hover:bg-gray-50 transition-colors">
                        <td className="p-4 font-bold text-gray-900">{i.name}</td>
                        <td className="p-4">
                          <span className={`px-2.5 py-0.5 rounded-full text-xs font-medium uppercase border ${TYPE_BADGES[i.type] || 'bg-gray-100 text-gray-700'}`}>
                            {i.type?.replace('_', ' ')}
                          </span>
                        </td>
                        <td className="p-4 font-mono text-gray-700">{i.registration_number || '—'}</td>
                        <td className="p-4 text-gray-700">{i.manufacturer || '—'}</td>
                        <td className="p-4">
                          <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                            i.status === 'registered' ? 'bg-green-100 text-green-800'
                              : i.status === 'banned' ? 'bg-red-100 text-red-800'
                              : 'bg-amber-100 text-amber-800'
                          }`}>
                            {i.status}
                          </span>
                        </td>
                        <td className="p-4 text-gray-500 text-xs">{i.source}</td>
                        <td className="p-4">
                          <div className="flex gap-1">
                            <button
                              onClick={() => openEdit(i)}
                              className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                              title="Edit product"
                            >
                              <Edit className="w-4 h-4" />
                            </button>
                            <button
                              onClick={() => deleteInput(i.uuid)}
                              className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                              title="Delete permanently"
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* AI Label Scanner Tab */}
      {tab === 'scanner' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Uploader Card */}
          <div className="lg:col-span-5 card space-y-4">
            <h3 className="font-bold text-gray-900 text-sm">Upload Label Image</h3>
            <p className="text-gray-500 text-xs">Upload a photograph of the agricultural input / medicine label to simulate label extraction & verification</p>

            <div className="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-blue-500 hover:bg-blue-50/20 transition-all text-center relative cursor-pointer group">
              <input
                type="file"
                accept="image/*"
                onChange={handleImageChange}
                className="absolute inset-0 opacity-0 cursor-pointer"
              />
              {scanPreview ? (
                <div className="space-y-3">
                  <img src={scanPreview} alt="Preview" className="max-h-48 mx-auto rounded-lg shadow-sm object-cover border" />
                  <p className="text-xs text-gray-600 font-semibold">Change image</p>
                </div>
              ) : (
                <div className="space-y-2 text-gray-400 py-6">
                  <Upload className="w-10 h-10 mx-auto text-gray-300 group-hover:text-blue-500 transition-colors" />
                  <p className="text-sm font-semibold text-gray-600">Select or Drag label photo</p>
                  <p className="text-xs">Supports PNG, JPG, JPEG up to 10MB</p>
                </div>
              )}
            </div>

            <button
              onClick={runScanner}
              disabled={!scanImage || scanning}
              className="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {scanning ? (
                <>
                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                  Analyzing label with Gemini...
                </>
              ) : (
                <>
                  <Eye className="w-4 h-4" /> Run AI Verification
                </>
              )}
            </button>
          </div>

          {/* Results Card */}
          <div className="lg:col-span-7 card flex flex-col justify-start min-h-[300px]">
            <h3 className="font-bold text-gray-900 border-b pb-2 mb-4 text-sm">AI Verification Verdict</h3>
            {scanning ? (
              <div className="flex-1 flex flex-col items-center justify-center text-gray-500 py-12">
                <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3" />
                <p className="text-sm font-medium">Extracting text & matching registry...</p>
              </div>
            ) : scanResult ? (
              <div className="space-y-4">
                {/* Verdict alert */}
                <div className={`p-4 rounded-xl border flex gap-3 ${
                  scanResult.verdict === 'found_registered' ? 'bg-green-50 border-green-200 text-green-800' :
                  scanResult.verdict === 'banned' ? 'bg-red-50 border-red-200 text-red-800' :
                  scanResult.verdict === 'withdrawn' ? 'bg-amber-50 border-amber-200 text-amber-800' :
                  'bg-red-50 border-red-200 text-red-800'
                }`}>
                  {scanResult.verdict === 'found_registered' ? (
                    <CheckCircle className="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                  ) : (
                    <AlertTriangle className="w-5 h-5 shrink-0 mt-0.5 text-red-600" />
                  )}
                  <div>
                    <h4 className="font-bold text-sm uppercase">Verdict: {scanResult.verdict?.replace('_', ' ')}</h4>
                    <p className="text-xs mt-0.5 opacity-90">{scanResult.guidance}</p>
                  </div>
                </div>

                {/* Extracted attributes */}
                <div className="bg-gray-50 rounded-xl p-4 space-y-3">
                  <h4 className="font-bold text-gray-800 text-xs uppercase tracking-wide">Extracted by AI Label scan</h4>
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p className="text-xs text-gray-500">Product Name</p>
                      <p className="font-semibold">{scanResult.extracted?.product_name || '—'}</p>
                    </div>
                    <div>
                      <p className="text-xs text-gray-500">Registration Number</p>
                      <p className="font-semibold font-mono text-xs">{scanResult.extracted?.registration_number || '—'}</p>
                    </div>
                    <div className="col-span-2">
                      <p className="text-xs text-gray-500">Manufacturer</p>
                      <p className="font-semibold">{scanResult.extracted?.manufacturer || '—'}</p>
                    </div>
                  </div>

                  {/* Label Warning messages */}
                  {scanResult.extracted?.label_warnings?.length > 0 && (
                    <div className="border-t pt-3 mt-2">
                      <p className="text-xs font-semibold text-red-700 mb-1">Quality Warning Flags:</p>
                      <ul className="list-disc list-inside text-xs text-red-800 space-y-0.5">
                        {scanResult.extracted.label_warnings.map((w, index) => (
                          <li key={index}>{w}</li>
                        ))}
                      </ul>
                    </div>
                  )}
                </div>

                {/* Registry Cross-reference details */}
                {scanResult.registry_match ? (
                  <div className="border border-green-100 rounded-xl p-4 bg-green-50/20 space-y-2">
                    <h4 className="font-bold text-green-800 text-xs uppercase tracking-wide">Matched Registry Record</h4>
                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <div><span className="font-semibold text-gray-600">Offical Name:</span> {scanResult.registry_match.name}</div>
                      <div><span className="font-semibold text-gray-600">Type:</span> {scanResult.registry_match.type}</div>
                      <div><span className="font-semibold text-gray-600">Approved Reg:</span> {scanResult.registry_match.registration_number}</div>
                      <div><span className="font-semibold text-gray-600">Distributor:</span> {scanResult.registry_match.distributor || '—'}</div>
                      <div className="col-span-2"><span className="font-semibold text-gray-600">Approved List Source:</span> {scanResult.registry_match.source}</div>
                    </div>
                  </div>
                ) : (
                  <div className="border border-dashed border-red-200 rounded-xl p-4 bg-red-50/10 text-center py-6">
                    <AlertTriangle className="w-8 h-8 text-red-500 mx-auto mb-1 opacity-70" />
                    <p className="text-xs text-red-800 font-semibold">No Matching Registry Entry Found</p>
                    <p className="text-[10px] text-gray-500 mt-0.5">The registration number extracted from this label does not match any approved products.</p>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex-1 flex flex-col items-center justify-center text-gray-400 py-12">
                <FileText className="w-12 h-12 mx-auto mb-2 opacity-30" />
                <p className="text-sm">Upload a label photo and click Run to test</p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Fullscreen Image Preview Modal */}
      {activePhoto && (
        <div className="fixed inset-0 bg-black/90 flex items-center justify-center z-[100] p-4" onClick={() => setActivePhoto(null)}>
          <div className="max-w-4xl max-h-[85vh] relative">
            <img src={activePhoto} alt="Fullscreen report details" className="max-w-full max-h-full rounded-lg object-contain shadow-2xl" />
            <button
              onClick={() => setActivePhoto(null)}
              className="absolute top-4 right-4 bg-black/60 hover:bg-black/80 text-white rounded-full p-2.5 transition-colors"
            >
              ✕
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
