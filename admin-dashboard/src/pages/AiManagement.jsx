import { useState, useEffect } from 'react'
import {
  Brain, Scan, MessageSquare, BookOpen, Settings2,
  CheckCircle, XCircle, Trash2, Eye, Plus, Edit,
  Search, ChevronRight, BarChart3, Zap, AlertTriangle
} from 'lucide-react'

const API = (path) => `/api/admin${path}`
const tok = () => localStorage.getItem('admin_token')
const get = (path) => fetch(API(path), { headers: { Authorization: `Bearer ${tok()}` } }).then(r => r.json())
const del = (path) => fetch(API(path), { method: 'DELETE', headers: { Authorization: `Bearer ${tok()}` } }).then(r => r.json())
const post = (path, body) => fetch(API(path), {
  method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${tok()}` },
  body: JSON.stringify(body)
}).then(r => r.json())
const put = (path, body) => fetch(API(path), {
  method: 'PUT', headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${tok()}` },
  body: JSON.stringify(body)
}).then(r => r.json())

const TABS = [
  { id: 'overview',  label: 'Overview',        icon: BarChart3 },
  { id: 'scans',     label: 'Disease Scans',   icon: Scan },
  { id: 'bot',       label: 'AI Chatbot Logs', icon: MessageSquare },
  { id: 'kb',        label: 'Knowledge Base',  icon: BookOpen },
  { id: 'config',    label: 'AI Config',       icon: Settings2 },
]

const KB_CATEGORIES = ['crop_disease','pest_control','soil_health','irrigation','fertilization','market_prices','weather','general']
const KB_LANGS = ['sw','en','lg','rw','fr']

// ─── SUB-PAGES ────────────────────────────────────────────────────────────────

function Overview({ stats }) {
  if (!stats) return <div className="flex justify-center py-12"><div className="w-8 h-8 border-2 border-green-600 border-t-transparent rounded-full animate-spin"/></div>
  const { scans, bot, knowledge_base, top_diseases, gemini_model, gemini_configured } = stats
  return (
    <div className="space-y-6">
      {/* Config status */}
      <div className={`flex items-center gap-3 p-4 rounded-xl border ${gemini_configured ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}`}>
        <Zap className={`w-5 h-5 ${gemini_configured ? 'text-green-600' : 'text-red-600'}`} />
        <div>
          <p className={`font-semibold text-sm ${gemini_configured ? 'text-green-800' : 'text-red-800'}`}>
            {gemini_configured ? `Gemini AI Connected — Model: ${gemini_model}` : 'Gemini API Key Not Configured'}
          </p>
          <p className="text-xs text-gray-500">{gemini_configured ? 'All AI features are operational' : 'Configure GEMINI_API_KEY in server .env'}</p>
        </div>
      </div>

      {/* Scan stats */}
      <div>
        <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Disease Scanner</h3>
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {[
            { label: 'Total Scans',    value: scans.total,       color: 'text-gray-900' },
            { label: 'Successful',     value: scans.successful,  color: 'text-green-600' },
            { label: 'Failed',         value: scans.failed,      color: 'text-red-600' },
            { label: 'Success Rate',   value: scans.success_rate + '%', color: 'text-blue-600' },
          ].map(s => (
            <div key={s.label} className="card text-center">
              <p className={`text-2xl font-bold ${s.color}`}>{s.value}</p>
              <p className="text-xs text-gray-500 mt-1">{s.label}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Bot & KB stats */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">AI Chatbot</h3>
          <div className="card flex items-center gap-4">
            <div className="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
              <MessageSquare className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-900">{bot.total_conversations}</p>
              <p className="text-sm text-gray-500">Total Bot Conversations</p>
            </div>
          </div>
        </div>
        <div>
          <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Knowledge Base</h3>
          <div className="card flex items-center gap-4">
            <div className="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
              <BookOpen className="w-6 h-6 text-amber-600" />
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-900">{knowledge_base.total}</p>
              <p className="text-sm text-gray-500">{knowledge_base.verified} verified documents</p>
            </div>
          </div>
        </div>
      </div>

      {/* Top diseases */}
      {top_diseases?.length > 0 && (
        <div>
          <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Most Detected Diseases</h3>
          <div className="card divide-y divide-gray-100">
            {top_diseases.map((d, i) => (
              <div key={d.disease_name} className="flex items-center justify-between py-3">
                <div className="flex items-center gap-3">
                  <span className="w-6 h-6 bg-green-100 text-green-700 rounded-full flex items-center justify-center text-xs font-bold">{i+1}</span>
                  <span className="font-medium text-sm text-gray-900">{d.disease_name}</span>
                </div>
                <span className="text-sm font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">{d.count} scans</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}

function DiseaseScans() {
  const [scans, setScans] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('')
  const [viewScan, setViewScan] = useState(null)

  const load = async () => {
    setLoading(true)
    const params = new URLSearchParams()
    if (filter) params.append('status', filter)
    const data = await get(`/ai/scans?${params}`)
    setScans(data.scans?.data || [])
    setLoading(false)
  }

  useEffect(() => { load() }, [filter])

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this scan record?')) return
    await del(`/ai/scans/${uuid}`)
    load()
  }

  const openView = async (scan) => {
    const data = await get(`/ai/scans/${scan.uuid}`)
    setViewScan(data.scan)
  }

  return (
    <div className="space-y-4">
      <div className="flex gap-3">
        <select value={filter} onChange={e => setFilter(e.target.value)}
          className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none">
          <option value="">All Status</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
        </select>
      </div>

      <div className="card overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
              <th className="px-4 py-3">User</th>
              <th className="px-4 py-3">Disease</th>
              <th className="px-4 py-3">Confidence</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Source</th>
              <th className="px-4 py-3">Date</th>
              <th className="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan="7" className="py-10 text-center"><div className="w-6 h-6 border-2 border-green-600 border-t-transparent rounded-full animate-spin mx-auto"/></td></tr>
            ) : scans.length === 0 ? (
              <tr><td colSpan="7" className="py-10 text-center text-gray-400">No scans found</td></tr>
            ) : scans.map(scan => (
              <tr key={scan.uuid} className="border-b border-gray-50 hover:bg-gray-50">
                <td className="px-4 py-3 text-sm font-medium">{scan.user?.name || 'Unknown'}</td>
                <td className="px-4 py-3 text-sm">{scan.disease_name || '—'}</td>
                <td className="px-4 py-3 text-sm">
                  {scan.confidence_score > 0
                    ? <span className="font-semibold text-green-700">{Math.round(scan.confidence_score * 100)}%</span>
                    : '—'}
                </td>
                <td className="px-4 py-3">
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${
                    scan.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  }`}>{scan.status}</span>
                </td>
                <td className="px-4 py-3 text-xs text-gray-500">{scan.scan_source}</td>
                <td className="px-4 py-3 text-xs text-gray-500">{new Date(scan.created_at).toLocaleDateString()}</td>
                <td className="px-4 py-3">
                  <div className="flex gap-1">
                    <button onClick={() => openView(scan)} className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="View"><Eye className="w-3.5 h-3.5"/></button>
                    <button onClick={() => handleDelete(scan.uuid)} className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><Trash2 className="w-3.5 h-3.5"/></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Scan detail modal */}
      {viewScan && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
            <div className="bg-green-700 text-white px-6 py-4">
              <h2 className="text-lg font-bold">Scan Details</h2>
              <p className="text-green-100 text-sm">{viewScan.user?.name} • {new Date(viewScan.created_at).toLocaleString()}</p>
            </div>
            <div className="p-6 space-y-4 max-h-96 overflow-y-auto">
              {viewScan.image_path && (
                <img src={`/storage/${viewScan.image_path}`} alt="Scan" className="w-full h-48 object-cover rounded-lg border" />
              )}
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div><p className="text-gray-500 text-xs">Disease</p><p className="font-semibold">{viewScan.disease_name || 'N/A'}</p></div>
                <div><p className="text-gray-500 text-xs">Confidence</p><p className="font-semibold text-green-700">{Math.round((viewScan.confidence_score||0)*100)}%</p></div>
                <div><p className="text-gray-500 text-xs">Status</p><p className="font-semibold">{viewScan.status}</p></div>
                <div><p className="text-gray-500 text-xs">Source</p><p className="font-semibold">{viewScan.scan_source}</p></div>
              </div>
              {viewScan.description && <div><p className="text-xs text-gray-500 mb-1">Description</p><p className="text-sm bg-gray-50 rounded p-3">{viewScan.description}</p></div>}
              {viewScan.treatment_recommendation && <div><p className="text-xs text-gray-500 mb-1">Treatment</p><p className="text-sm bg-green-50 rounded p-3">{viewScan.treatment_recommendation}</p></div>}
            </div>
            <div className="bg-gray-50 px-6 py-3 flex justify-end border-t">
              <button onClick={() => setViewScan(null)} className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100">Close</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

function BotLogs() {
  const [conversations, setConversations] = useState([])
  const [loading, setLoading] = useState(true)
  const [viewConv, setViewConv] = useState(null)

  const load = async () => {
    setLoading(true)
    const data = await get('/ai/conversations')
    setConversations(data.conversations?.data || [])
    setLoading(false)
  }

  useEffect(() => { load() }, [])

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this conversation and all messages?')) return
    await del(`/ai/conversations/${uuid}`)
    load()
  }

  const openView = async (conv) => {
    const data = await get(`/ai/conversations/${conv.uuid}`)
    setViewConv(data.conversation)
  }

  return (
    <div className="space-y-4">
      <div className="card overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
              <th className="px-4 py-3">User</th>
              <th className="px-4 py-3">Title</th>
              <th className="px-4 py-3">Language</th>
              <th className="px-4 py-3">Messages</th>
              <th className="px-4 py-3">Date</th>
              <th className="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan="6" className="py-10 text-center"><div className="w-6 h-6 border-2 border-purple-600 border-t-transparent rounded-full animate-spin mx-auto"/></td></tr>
            ) : conversations.length === 0 ? (
              <tr><td colSpan="6" className="py-10 text-center text-gray-400">No conversations found</td></tr>
            ) : conversations.map(conv => (
              <tr key={conv.uuid} className="border-b border-gray-50 hover:bg-gray-50">
                <td className="px-4 py-3 text-sm font-medium">{conv.user?.name || 'Unknown'}</td>
                <td className="px-4 py-3 text-sm max-w-48 truncate">{conv.title}</td>
                <td className="px-4 py-3"><span className="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs font-medium uppercase">{conv.language}</span></td>
                <td className="px-4 py-3 text-sm text-center font-semibold">{conv.messages?.length || 0}</td>
                <td className="px-4 py-3 text-xs text-gray-500">{new Date(conv.updated_at).toLocaleDateString()}</td>
                <td className="px-4 py-3">
                  <div className="flex gap-1">
                    <button onClick={() => openView(conv)} className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><Eye className="w-3.5 h-3.5"/></button>
                    <button onClick={() => handleDelete(conv.uuid)} className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"><Trash2 className="w-3.5 h-3.5"/></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Conversation modal */}
      {viewConv && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden">
            <div className="bg-purple-700 text-white px-6 py-4">
              <h2 className="text-lg font-bold">Conversation: {viewConv.title}</h2>
              <p className="text-purple-100 text-sm">{viewConv.user?.name} • {viewConv.language?.toUpperCase()}</p>
            </div>
            <div className="p-4 max-h-96 overflow-y-auto space-y-2">
              {(viewConv.messages || []).map(msg => (
                <div key={msg.id} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
                  <div className={`max-w-[80%] px-4 py-2.5 rounded-2xl text-sm ${
                    msg.role === 'user' ? 'bg-green-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm'
                  }`}>
                    <p className="text-xs font-semibold mb-1 opacity-70">{msg.role === 'user' ? 'User' : '🤖 Mkulima Bot'}</p>
                    <p className="whitespace-pre-wrap">{msg.content}</p>
                  </div>
                </div>
              ))}
              {!viewConv.messages?.length && <p className="text-center text-gray-400 py-8">No messages</p>}
            </div>
            <div className="bg-gray-50 px-6 py-3 flex justify-end border-t">
              <button onClick={() => setViewConv(null)} className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100">Close</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

function KnowledgeBase() {
  const [docs, setDocs] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [filterCat, setFilterCat] = useState('')
  const [filterLang, setFilterLang] = useState('')
  const [editDoc, setEditDoc] = useState(null)
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState({ title:'', content:'', category:'general', language:'sw', source:'', is_verified: false })
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')

  const load = async () => {
    setLoading(true)
    const params = new URLSearchParams()
    if (search)    params.append('search', search)
    if (filterCat) params.append('category', filterCat)
    if (filterLang) params.append('language', filterLang)
    const data = await get(`/ai/kb?${params}`)
    setDocs(data.documents?.data || [])
    setLoading(false)
  }

  useEffect(() => { load() }, [search, filterCat, filterLang])

  const handleSave = async () => {
    setSaving(true)
    try {
      let res
      if (editDoc) {
        res = await put(`/ai/kb/${editDoc.uuid}`, form)
      } else {
        res = await post('/ai/kb', form)
      }
      setMessage(res.message || 'Saved')
      setShowForm(false); setEditDoc(null)
      load()
    } catch { setMessage('Failed') }
    setSaving(false)
  }

  const handleDelete = async (uuid) => {
    if (!confirm('Delete this knowledge base document?')) return
    await del(`/ai/kb/${uuid}`)
    load()
  }

  const openEdit = (doc) => {
    setEditDoc(doc)
    setForm({ title: doc.title, content: doc.content, category: doc.category, language: doc.language, source: doc.source||'', is_verified: doc.is_verified })
    setShowForm(true)
  }

  return (
    <div className="space-y-4">
      {message && <div className="p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{message}</div>}

      <div className="flex flex-wrap gap-3 items-center justify-between">
        <div className="flex flex-wrap gap-2">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
            <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search docs..."
              className="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none w-48"/>
          </div>
          <select value={filterCat} onChange={e => setFilterCat(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            <option value="">All Categories</option>
            {KB_CATEGORIES.map(c => <option key={c} value={c}>{c.replace('_', ' ')}</option>)}
          </select>
          <select value={filterLang} onChange={e => setFilterLang(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            <option value="">All Languages</option>
            {KB_LANGS.map(l => <option key={l} value={l}>{l.toUpperCase()}</option>)}
          </select>
        </div>
        <button onClick={() => { setEditDoc(null); setForm({ title:'', content:'', category:'general', language:'sw', source:'', is_verified: false }); setShowForm(true) }}
          className="flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium">
          <Plus className="w-4 h-4"/> Add Document
        </button>
      </div>

      {/* Form */}
      {showForm && (
        <div className="card border-amber-200 border-2 space-y-4">
          <h3 className="font-semibold text-gray-800">{editDoc ? 'Edit Document' : 'New Knowledge Base Document'}</h3>
          <input value={form.title} onChange={e => setForm({...form, title: e.target.value})} placeholder="Document Title"
            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none"/>
          <div className="grid grid-cols-2 gap-3">
            <select value={form.category} onChange={e => setForm({...form, category: e.target.value})}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              {KB_CATEGORIES.map(c => <option key={c} value={c}>{c.replace('_',' ')}</option>)}
            </select>
            <select value={form.language} onChange={e => setForm({...form, language: e.target.value})}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              {KB_LANGS.map(l => <option key={l} value={l}>{l.toUpperCase()}</option>)}
            </select>
          </div>
          <input value={form.source} onChange={e => setForm({...form, source: e.target.value})} placeholder="Source / Reference (optional)"
            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none"/>
          <textarea value={form.content} onChange={e => setForm({...form, content: e.target.value})} placeholder="Document content (used by AI for answering farmer questions)..."
            rows={8} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none resize-none"/>
          <label className="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" checked={form.is_verified} onChange={e => setForm({...form, is_verified: e.target.checked})}
              className="w-4 h-4 text-amber-600 rounded"/>
            <span className="text-sm text-gray-700 font-medium">Mark as Expert Verified</span>
          </label>
          <div className="flex gap-3">
            <button onClick={handleSave} disabled={saving}
              className="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 disabled:opacity-50">
              {saving ? 'Saving...' : editDoc ? 'Update' : 'Create'}
            </button>
            <button onClick={() => { setShowForm(false); setEditDoc(null) }}
              className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100">Cancel</button>
          </div>
        </div>
      )}

      {/* Documents list */}
      <div className="space-y-2">
        {loading ? (
          <div className="text-center py-10"><div className="w-6 h-6 border-2 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto"/></div>
        ) : docs.length === 0 ? (
          <div className="text-center py-10 text-gray-400">No documents found</div>
        ) : docs.map(doc => (
          <div key={doc.uuid} className="card hover:border-amber-200 transition-colors">
            <div className="flex items-start justify-between gap-4">
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 flex-wrap mb-1">
                  <h4 className="font-semibold text-gray-900 text-sm">{doc.title}</h4>
                  {doc.is_verified && <span className="flex items-center gap-0.5 text-xs text-green-700 bg-green-100 px-1.5 py-0.5 rounded-full"><CheckCircle className="w-3 h-3"/>Verified</span>}
                </div>
                <div className="flex items-center gap-2 flex-wrap text-xs text-gray-500">
                  <span className="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-medium">{doc.category?.replace('_',' ')}</span>
                  <span className="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full uppercase font-medium">{doc.language}</span>
                  {doc.source && <span>• {doc.source}</span>}
                </div>
                <p className="text-xs text-gray-400 mt-1 line-clamp-2">{doc.content}</p>
              </div>
              <div className="flex gap-1 shrink-0">
                <button onClick={() => openEdit(doc)} className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><Edit className="w-3.5 h-3.5"/></button>
                <button onClick={() => handleDelete(doc.uuid)} className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"><Trash2 className="w-3.5 h-3.5"/></button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

function AiConfig({ config }) {
  const [providers, setProviders] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [testing, setTesting] = useState(false)
  const [showKey, setShowKey] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const [form, setForm] = useState({
    uuid: '',
    name: 'Google Gemini Production',
    provider_type: 'gemini',
    api_key: '',
    model: 'gemini-3-flash-preview',
    status: 'active',
    is_default: true,
  })

  const loadProviders = async () => {
    setLoading(true)
    try {
      const data = await get('/ai/providers')
      if (data?.providers) {
        setProviders(data.providers)
        const gemini = data.providers.find(p => p.provider_type === 'gemini') || data.providers[0]
        if (gemini) {
          setForm({
            uuid: gemini.uuid,
            name: gemini.name || 'Google Gemini Production',
            provider_type: gemini.provider_type || 'gemini',
            api_key: '',
            model: gemini.model || 'gemini-3-flash-preview',
            status: gemini.status || 'active',
            is_default: gemini.is_default ?? true,
          })
        }
      }
    } catch (err) {
      console.error('Failed to load AI providers:', err)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { loadProviders() }, [])

  const handleSave = async (e) => {
    e.preventDefault()
    setSaving(true)
    setMessage('')
    setError('')

    try {
      let res
      if (form.uuid) {
        res = await put(`/ai/providers/${form.uuid}`, form)
      } else {
        res = await post('/ai/providers', form)
      }

      if (res?.provider || res?.message?.includes('success')) {
        setMessage('AI Provider key & configuration saved and encrypted successfully!')
        if (res.provider) {
          setForm(prev => ({ ...prev, uuid: res.provider.uuid, api_key: '' }))
        }
        loadProviders()
      } else {
        setError(res?.message || 'Failed to save AI configuration.')
      }
    } catch (err) {
      setError('Network error while saving AI configuration.')
    } finally {
      setSaving(false)
    }
  }

  const handleTest = async () => {
    if (!form.uuid) {
      setError('Please save the provider credentials first before testing connection.')
      return
    }
    setTesting(true)
    setMessage('')
    setError('')
    try {
      const res = await post(`/ai/providers/${form.uuid}/test`, {})
      if (res?.status === 'success' || res?.message?.includes('successful') || res?.message?.includes('Connected')) {
        setMessage(`Connectivity test PASSED! ${res.message}`)
      } else {
        setError(res?.message || 'Connection test failed.')
      }
    } catch (err) {
      setError('Failed to test connectivity.')
    } finally {
      setTesting(false)
    }
  }

  const activeProvider = providers.find(p => p.uuid === form.uuid) || providers[0]

  return (
    <div className="space-y-6 max-w-3xl">
      {/* Live Status Header */}
      <div className="card space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <h3 className="font-bold text-gray-900 text-lg">Gemini & AI Provider Management</h3>
            <p className="text-xs text-gray-500 mt-0.5">Configure API credentials, choose AI models, and test live connectivity.</p>
          </div>
          {activeProvider?.status === 'active' || config?.configured ? (
            <span className="flex items-center gap-1.5 text-green-700 bg-green-100 px-3 py-1 rounded-full text-xs font-bold border border-green-200 shadow-sm">
              <CheckCircle className="w-4 h-4 text-green-600"/> Live & Connected
            </span>
          ) : (
            <span className="flex items-center gap-1.5 text-red-700 bg-red-100 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
              <XCircle className="w-4 h-4 text-red-600"/> Not Configured
            </span>
          )}
        </div>

        {message && (
          <div className="p-3 bg-green-50 border border-green-200 text-green-800 text-xs rounded-xl font-medium flex items-center gap-2">
            <CheckCircle className="w-4 h-4 shrink-0 text-green-600"/>
            <span>{message}</span>
          </div>
        )}

        {error && (
          <div className="p-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium flex items-center gap-2">
            <AlertTriangle className="w-4 h-4 shrink-0 text-red-600"/>
            <span>{error}</span>
          </div>
        )}

        {/* Configuration Form */}
        <form onSubmit={handleSave} className="space-y-4 pt-2">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">Provider Name</label>
              <input
                type="text"
                value={form.name}
                onChange={e => setForm({...form, name: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none"
                placeholder="Google Gemini Production"
                required
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-1">AI Provider Type</label>
              <select
                value={form.provider_type}
                onChange={e => setForm({...form, provider_type: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none"
              >
                <option value="gemini">Google Gemini (Recommended)</option>
                <option value="openai">OpenAI (GPT-4o / GPT-4o-mini)</option>
                <option value="deepseek">DeepSeek AI</option>
                <option value="claude">Anthropic Claude</option>
                <option value="groq">Groq Llama 3</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">
              AI Model Selection
            </label>
            <select
              value={form.model}
              onChange={e => setForm({...form, model: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm font-mono text-green-900 bg-green-50/50 border-green-200 focus:ring-2 focus:ring-green-500 outline-none font-semibold"
            >
              <option value="gemini-3-flash-preview">gemini-3-flash-preview (Gemini 3 Flash — Fast & Multimodal — Recommended)</option>
              <option value="gemini-3-pro-preview">gemini-3-pro-preview (Gemini 3 Pro — Advanced Weather & Advisory)</option>
              <option value="gemini-2.5-flash">gemini-2.5-flash (Gemini 2.5 Flash)</option>
              <option value="gemini-2.0-flash">gemini-2.0-flash (Gemini 2.0 Flash)</option>
              <option value="gpt-4o">gpt-4o (OpenAI)</option>
              <option value="gpt-4o-mini">gpt-4o-mini (OpenAI)</option>
            </select>
            <p className="text-[11px] text-gray-500 mt-1">
              Gemini 3 series powers disease diagnostics, Mkulima Bot Q&A, and market ranking in Kiswahili and English.
            </p>
          </div>

          <div>
            <label className="block text-xs font-semibold text-gray-700 mb-1">
              API Key (AES-256 Encrypted at Rest)
            </label>
            <div className="relative">
              <input
                type={showKey ? "text" : "password"}
                value={form.api_key}
                onChange={e => setForm({...form, api_key: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm font-mono pr-10 focus:ring-2 focus:ring-green-500 outline-none"
                placeholder={activeProvider ? "•••••••••••••••••••••••••••••••• (Leave blank to keep existing key)" : "Paste your AI API Key here (e.g. AIzaSy...)"}
                required={!activeProvider}
              />
              <button
                type="button"
                onClick={() => setShowKey(!showKey)}
                className="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
              >
                <Eye className="w-4 h-4" />
              </button>
            </div>
            {activeProvider?.masked_key && (
              <p className="text-[11px] text-gray-500 mt-1 font-mono">
                Currently stored key: <span className="font-semibold text-gray-700">{activeProvider.masked_key}</span>
              </p>
            )}
          </div>

          <div className="flex items-center gap-6 pt-2">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={form.is_default}
                onChange={e => setForm({...form, is_default: e.target.checked})}
                className="w-4 h-4 text-green-600 rounded focus:ring-green-500"
              />
              <span className="text-xs font-medium text-gray-700">Set as Primary Default AI Provider</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={form.status === 'active'}
                onChange={e => setForm({...form, status: e.target.checked ? 'active' : 'inactive'})}
                className="w-4 h-4 text-green-600 rounded focus:ring-green-500"
              />
              <span className="text-xs font-medium text-gray-700">Active Provider</span>
            </label>
          </div>

          <div className="flex items-center gap-3 pt-4 border-t border-gray-100">
            <button
              type="submit"
              disabled={saving}
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50"
            >
              <Zap className="w-4 h-4" />
              {saving ? 'Encrypting & Saving…' : 'Save & Encrypt API Key'}
            </button>

            {form.uuid && (
              <button
                type="button"
                onClick={handleTest}
                disabled={testing}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl transition-all disabled:opacity-50"
              >
                <CheckCircle className="w-4 h-4 text-green-600" />
                {testing ? 'Testing Connectivity…' : 'Test Live API Connection'}
              </button>
            )}
          </div>
        </form>
      </div>

      <div className="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-5 text-sm">
        <div className="flex gap-3 items-start">
          <div className="w-8 h-8 rounded-xl bg-green-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
            <Brain className="w-4 h-4" />
          </div>
          <div>
            <p className="font-bold text-green-900 mb-1">Zero-Downtime Key Management Enabled</p>
            <p className="text-xs text-green-800 leading-relaxed">
              API credentials are encrypted in your database using AES-256-CBC and decrypted securely in memory only when calling the Gemini API. You do not need to SSH into the server or restart PHP-FPM when changing keys or models.
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}

// ─── MAIN PAGE ────────────────────────────────────────────────────────────────

export default function AiManagement() {
  const [activeTab, setActiveTab] = useState('overview')
  const [stats, setStats] = useState(null)
  const [config, setConfig] = useState(null)

  useEffect(() => {
    get('/ai/stats').then(d => setStats(d)).catch(() => {})
    get('/ai/config').then(d => setConfig(d)).catch(() => {})
  }, [])

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-4">
        <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-green-500 rounded-xl flex items-center justify-center shadow-lg">
          <Brain className="w-6 h-6 text-white"/>
        </div>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">AI Management</h1>
          <p className="text-gray-500 text-sm">Monitor and manage all AI features — disease scanner, chatbot, and knowledge base</p>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 border-b border-gray-200 overflow-x-auto">
        {TABS.map(tab => (
          <button key={tab.id} onClick={() => setActiveTab(tab.id)}
            className={`flex items-center gap-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors ${
              activeTab === tab.id
                ? 'border-green-600 text-green-700'
                : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
            }`}>
            <tab.icon className="w-4 h-4"/>
            {tab.label}
          </button>
        ))}
      </div>

      {/* Tab content */}
      {activeTab === 'overview'  && <Overview stats={stats} />}
      {activeTab === 'scans'     && <DiseaseScans />}
      {activeTab === 'bot'       && <BotLogs />}
      {activeTab === 'kb'        && <KnowledgeBase />}
      {activeTab === 'config'    && <AiConfig config={config} />}
    </div>
  )
}
