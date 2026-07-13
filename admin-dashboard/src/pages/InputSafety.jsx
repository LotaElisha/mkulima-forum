import { useState, useEffect, useCallback } from 'react'
import { ShieldAlert, CheckCircle, XCircle, Plus, Trash2 } from 'lucide-react'

const EMPTY_INPUT = {
  name: '',
  type: 'pesticide',
  registration_number: '',
  manufacturer: '',
  status: 'registered',
  source: '',
}

const TYPES = ['pesticide', 'herbicide', 'fungicide', 'insecticide', 'fertilizer', 'vet_product', 'seed']

export default function InputSafetyPage() {
  const [tab, setTab] = useState('alerts')
  const [alerts, setAlerts] = useState([])
  const [registry, setRegistry] = useState([])
  const [registryQuery, setRegistryQuery] = useState('')
  const [alertStatus, setAlertStatus] = useState('pending')
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState(EMPTY_INPUT)

  const token = () => localStorage.getItem('admin_token')

  const fetchAlerts = useCallback(async () => {
    setLoading(true)
    try {
      const res = await fetch(`/api/admin/input-alerts?status=${alertStatus}`, {
        headers: { Authorization: `Bearer ${token()}` },
      })
      if (res.ok) setAlerts((await res.json()).data || [])
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
    } finally {
      setLoading(false)
    }
  }, [registryQuery])

  useEffect(() => {
    if (tab === 'alerts') fetchAlerts()
    else fetchRegistry()
  }, [tab, fetchAlerts, fetchRegistry])

  const review = async (uuid, decision) => {
    const notes = prompt(decision === 'confirmed'
      ? 'Umehakikije? (maelezo ya uthibitisho)'
      : 'Kwa nini inakataliwa?') || ''
    const res = await fetch(`/api/admin/input-alerts/${uuid}/review`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token()}` },
      body: JSON.stringify({ decision, notes }),
    })
    const data = await res.json()
    setMessage(data.message || 'Done')
    fetchAlerts()
  }

  const saveInput = async (e) => {
    e.preventDefault()
    const res = await fetch('/api/admin/inputs', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token()}` },
      body: JSON.stringify(form),
    })
    const data = await res.json()
    if (res.ok) {
      setMessage(data.message)
      setForm(EMPTY_INPUT)
      setShowForm(false)
      fetchRegistry()
    } else {
      setMessage(data.message || 'Validation failed')
    }
  }

  const deleteInput = async (uuid) => {
    if (!confirm('Ondoa bidhaa hii kwenye orodha ya usajili?')) return
    await fetch(`/api/admin/inputs/${uuid}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token()}` },
    })
    fetchRegistry()
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold flex items-center gap-2 mb-4">
        <ShieldAlert className="text-red-600" /> Input Safety (Kagua Dawa)
      </h1>

      <div className="flex gap-2 mb-4">
        <button
          onClick={() => setTab('alerts')}
          className={`px-4 py-2 rounded-lg text-sm font-medium ${tab === 'alerts' ? 'bg-red-600 text-white' : 'bg-gray-100'}`}
        >
          Counterfeit Alerts
        </button>
        <button
          onClick={() => setTab('registry')}
          className={`px-4 py-2 rounded-lg text-sm font-medium ${tab === 'registry' ? 'bg-green-700 text-white' : 'bg-gray-100'}`}
        >
          Official Registry
        </button>
      </div>

      {message && (
        <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800">{message}</div>
      )}

      {tab === 'alerts' && (
        <>
          <select value={alertStatus} onChange={(e) => setAlertStatus(e.target.value)} className="border rounded-lg px-3 py-2 mb-4">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="dismissed">Dismissed</option>
          </select>
          {loading ? (
            <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600 mx-auto mt-12" />
          ) : alerts.length === 0 ? (
            <div className="text-center text-gray-500 mt-12">No alerts in this queue.</div>
          ) : (
            <div className="space-y-3">
              {alerts.map((a) => (
                <div key={a.uuid} className="bg-white rounded-xl shadow p-4 flex items-start justify-between gap-4">
                  <div className="min-w-0">
                    <p className="font-semibold">{a.product_name}</p>
                    <p className="text-sm text-gray-500">
                      {a.region}{a.district ? `, ${a.district}` : ''}{a.dealer_name ? ` · Duka: ${a.dealer_name}` : ''}
                    </p>
                    <p className="text-sm mt-1">{a.description}</p>
                    <p className="text-xs text-gray-400 mt-1">
                      Reporter: {a.reporter?.name || '—'} ({a.reporter?.role})
                      {a.admin_notes ? ` · Notes: ${a.admin_notes}` : ''}
                    </p>
                  </div>
                  {a.status === 'pending' && (
                    <div className="flex flex-col gap-2 shrink-0">
                      <button onClick={() => review(a.uuid, 'confirmed')} className="flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                        <CheckCircle size={14} /> Confirm alert
                      </button>
                      <button onClick={() => review(a.uuid, 'dismissed')} className="flex items-center gap-1 px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                        <XCircle size={14} /> Dismiss
                      </button>
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </>
      )}

      {tab === 'registry' && (
        <>
          <div className="flex gap-3 mb-4">
            <input
              value={registryQuery}
              onChange={(e) => setRegistryQuery(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && fetchRegistry()}
              placeholder="Search name or registration number…"
              className="border rounded-lg px-3 py-2 flex-1"
            />
            <button onClick={() => setShowForm(!showForm)} className="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800">
              <Plus size={16} /> Add entry
            </button>
          </div>

          {showForm && (
            <form onSubmit={saveInput} className="bg-white rounded-xl shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-3 gap-3">
              <label className="text-sm"><span className="text-gray-600">Name</span>
                <input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="mt-1 w-full border rounded-lg px-3 py-2" />
              </label>
              <label className="text-sm"><span className="text-gray-600">Type</span>
                <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })} className="mt-1 w-full border rounded-lg px-3 py-2">
                  {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                </select>
              </label>
              <label className="text-sm"><span className="text-gray-600">Registration no.</span>
                <input value={form.registration_number} onChange={(e) => setForm({ ...form, registration_number: e.target.value })} className="mt-1 w-full border rounded-lg px-3 py-2" />
              </label>
              <label className="text-sm"><span className="text-gray-600">Manufacturer</span>
                <input value={form.manufacturer} onChange={(e) => setForm({ ...form, manufacturer: e.target.value })} className="mt-1 w-full border rounded-lg px-3 py-2" />
              </label>
              <label className="text-sm"><span className="text-gray-600">Status</span>
                <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className="mt-1 w-full border rounded-lg px-3 py-2">
                  <option value="registered">registered</option>
                  <option value="banned">banned</option>
                  <option value="withdrawn">withdrawn</option>
                </select>
              </label>
              <label className="text-sm"><span className="text-gray-600">Source (official list)</span>
                <input required value={form.source} onChange={(e) => setForm({ ...form, source: e.target.value })} placeholder="e.g. TPRI Registered Pesticides 2026" className="mt-1 w-full border rounded-lg px-3 py-2" />
              </label>
              <div className="col-span-full">
                <button type="submit" className="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800">Save</button>
              </div>
            </form>
          )}

          {loading ? (
            <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600 mx-auto mt-12" />
          ) : registry.length === 0 ? (
            <div className="text-center text-gray-500 mt-12">
              Registry is empty. Load entries from the official TPRI/TFRA lists — farmers see an honest
              “registry still being populated” note until then.
            </div>
          ) : (
            <div className="bg-white rounded-xl shadow overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 text-left text-gray-600">
                  <tr>
                    <th className="p-3">Name</th><th className="p-3">Type</th><th className="p-3">Reg. no.</th>
                    <th className="p-3">Manufacturer</th><th className="p-3">Status</th><th className="p-3">Source</th><th className="p-3"></th>
                  </tr>
                </thead>
                <tbody>
                  {registry.map((i) => (
                    <tr key={i.uuid} className="border-t">
                      <td className="p-3 font-medium">{i.name}</td>
                      <td className="p-3">{i.type}</td>
                      <td className="p-3">{i.registration_number || '—'}</td>
                      <td className="p-3">{i.manufacturer || '—'}</td>
                      <td className="p-3">
                        <span className={`px-2 py-0.5 rounded-full text-xs ${
                          i.status === 'registered' ? 'bg-green-50 text-green-700'
                            : i.status === 'banned' ? 'bg-red-50 text-red-700'
                            : 'bg-amber-50 text-amber-700'
                        }`}>{i.status}</span>
                      </td>
                      <td className="p-3 text-gray-500">{i.source}</td>
                      <td className="p-3">
                        <button onClick={() => deleteInput(i.uuid)} className="text-red-500 hover:text-red-700">
                          <Trash2 size={15} />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </>
      )}
    </div>
  )
}
