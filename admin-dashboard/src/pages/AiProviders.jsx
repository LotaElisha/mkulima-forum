import { useState, useEffect } from 'react'
import {
  Brain,
  Zap,
  CheckCircle2,
  XCircle,
  RefreshCw,
  Plus,
  Edit3,
  Trash2,
  Key,
  Sliders,
  ShieldCheck,
  Activity,
  Layers,
  Sparkles,
  Server,
  AlertTriangle,
  Clock,
  ArrowRight
} from 'lucide-react'

export default function AiProviders() {
  const [providers, setProviders] = useState([])
  const [defaultProvider, setDefaultProvider] = useState(null)
  const [featureRoutes, setFeatureRoutes] = useState([])
  const [stats, setStats] = useState(null)
  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)
  const [activeTab, setActiveTab] = useState('providers') // 'providers' | 'routes' | 'analytics'

  // Modal State
  const [showModal, setShowModal] = useState(false)
  const [editingProvider, setEditingProvider] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    provider_type: 'gemini',
    api_key: '',
    replace_api_key: false,
    base_url: '',
    model: 'gemini-2.0-flash',
    temperature: 0.7,
    max_tokens: 2048,
    timeout: 30,
    is_default: false,
    status: 'active'
  })

  // Testing & Loading States
  const [testingId, setTestingId] = useState(null)
  const [testResult, setTestResult] = useState(null)
  const [fetchedModels, setFetchedModels] = useState([])
  const [fetchingModels, setFetchingModels] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [toast, setToast] = useState(null)


  const fetchProviders = async () => {
    try {
      const res = await fetch('/api/admin/ai/providers', {
        headers: { }
      })
      if (res.ok) {
        const data = await res.json()
        setProviders(data.providers || [])
        setDefaultProvider(data.default_provider || null)
      }
    } catch (e) {
      console.error(e)
    }
  }

  const fetchRoutes = async () => {
    try {
      const res = await fetch('/api/admin/ai/providers/routes', {
        headers: { }
      })
      if (res.ok) {
        const data = await res.json()
        setFeatureRoutes(data.feature_routes || [])
      }
    } catch (e) {
      console.error(e)
    }
  }

  const fetchStats = async () => {
    try {
      const res = await fetch('/api/admin/ai/providers/usage-stats', {
        headers: { }
      })
      if (res.ok) {
        const data = await res.json()
        setStats(data.stats || null)
        setLogs(data.logs || [])
      }
    } catch (e) {
      console.error(e)
    }
  }

  useEffect(() => {
    const loadAll = async () => {
      setLoading(true)
      await Promise.all([fetchProviders(), fetchRoutes(), fetchStats()])
      setLoading(false)
    }
    loadAll()
  }, [])

  const showNotification = (message, type = 'success') => {
    setToast({ message, type })
    setTimeout(() => setToast(null), 4000)
  }

  const handleOpenAddModal = () => {
    setEditingProvider(null)
    setFormData({
      name: 'Google Gemini',
      provider_type: 'gemini',
      api_key: '',
      replace_api_key: true,
      base_url: '',
      model: 'gemini-2.0-flash',
      temperature: 0.7,
      max_tokens: 2048,
      timeout: 30,
      is_default: providers.length === 0,
      status: 'active'
    })
    setFetchedModels([])
    setShowModal(true)
  }

  const handleOpenEditModal = (p) => {
    setEditingProvider(p)
    setFormData({
      name: p.name,
      provider_type: p.provider_type,
      api_key: '',
      replace_api_key: false,
      base_url: p.base_url || '',
      model: p.model || 'gemini-2.0-flash',
      temperature: p.temperature ?? 0.7,
      max_tokens: p.max_tokens ?? 2048,
      timeout: p.timeout ?? 30,
      is_default: p.is_default,
      status: p.status
    })
    setFetchedModels([])
    setShowModal(true)
  }

  const handleSaveProvider = async (e) => {
    e.preventDefault()
    setSubmitting(true)

    const payload = { ...formData }
    if (editingProvider && !formData.replace_api_key) {
      delete payload.api_key
    }

    try {
      const url = editingProvider
        ? `/api/admin/ai/providers/${editingProvider.uuid}`
        : '/api/admin/ai/providers'
      const method = editingProvider ? 'PUT' : 'POST'

      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',

        },
        body: JSON.stringify(payload)
      })

      const data = await res.json()
      if (res.ok) {
        showNotification(data.message || 'Provider saved successfully')
        setShowModal(false)
        fetchProviders()
      } else {
        showNotification(data.message || 'Failed to save provider', 'error')
      }
    } catch (err) {
      showNotification('An error occurred while saving', 'error')
    }
    setSubmitting(false)
  }

  const handleTestConnection = async (uuid) => {
    setTestingId(uuid)
    setTestResult(null)
    try {
      const res = await fetch(`/api/admin/ai/providers/${uuid}/test`, {
        method: 'POST',
        headers: { }
      })
      const data = await res.json()
      setTestResult(data)
      if (data.success) {
        showNotification(`Connected to ${data.provider} (${data.latency_ms}ms)`)
      } else {
        showNotification(`Connection failed: ${data.message}`, 'error')
      }
      fetchProviders()
    } catch (e) {
      showNotification('Connection test failed', 'error')
    }
    setTestingId(null)
  }

  const handleSetDefault = async (uuid) => {
    try {
      const res = await fetch(`/api/admin/ai/providers/${uuid}/set-default`, {
        method: 'POST',
        headers: { }
      })
      const data = await res.json()
      if (res.ok) {
        showNotification(data.message)
        fetchProviders()
      }
    } catch (e) {
      showNotification('Failed to set default provider', 'error')
    }
  }

  const handleToggleStatus = async (uuid) => {
    try {
      const res = await fetch(`/api/admin/ai/providers/${uuid}/toggle`, {
        method: 'POST',
        headers: { }
      })
      const data = await res.json()
      if (res.ok) {
        showNotification(data.message)
        fetchProviders()
      }
    } catch (e) {
      showNotification('Failed to update status', 'error')
    }
  }

  const handleDeleteProvider = async (uuid, name) => {
    if (!window.confirm(`Are you sure you want to remove ${name}?`)) return
    try {
      const res = await fetch(`/api/admin/ai/providers/${uuid}`, {
        method: 'DELETE',
        headers: { }
      })
      const data = await res.json()
      if (res.ok) {
        showNotification(data.message)
        fetchProviders()
      }
    } catch (e) {
      showNotification('Failed to delete provider', 'error')
    }
  }

  const handleFetchModels = async (uuid) => {
    if (!uuid) return
    setFetchingModels(true)
    try {
      const res = await fetch(`/api/admin/ai/providers/${uuid}/models`, {
        headers: { }
      })
      if (res.ok) {
        const data = await res.json()
        setFetchedModels(data.models || [])
      }
    } catch (e) {
      console.error(e)
    }
    setFetchingModels(false)
  }

  const handleUpdateFeatureRoute = async (featureKey, providerId) => {
    try {
      const res = await fetch('/api/admin/ai/providers/routes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',

        },
        body: JSON.stringify({
          feature_key: featureKey,
          ai_provider_id: providerId ? parseInt(providerId) : null
        })
      })
      if (res.ok) {
        showNotification('Feature routing updated')
        fetchRoutes()
      }
    } catch (e) {
      showNotification('Failed to update feature route', 'error')
    }
  }

  const getProviderBadge = (type) => {
    switch (type) {
      case 'gemini':
        return { name: 'Google Gemini', color: 'bg-blue-100 text-blue-800 border-blue-200' }
      case 'openai':
        return { name: 'OpenAI', color: 'bg-emerald-100 text-emerald-800 border-emerald-200' }
      case 'claude':
        return { name: 'Anthropic Claude', color: 'bg-amber-100 text-amber-800 border-amber-200' }
      case 'deepseek':
        return { name: 'DeepSeek', color: 'bg-indigo-100 text-indigo-800 border-indigo-200' }
      case 'groq':
        return { name: 'Groq', color: 'bg-orange-100 text-orange-800 border-orange-200' }
      case 'openrouter':
        return { name: 'OpenRouter', color: 'bg-purple-100 text-purple-800 border-purple-200' }
      default:
        return { name: type.toUpperCase(), color: 'bg-gray-100 text-gray-800 border-gray-200' }
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Toast Notification */}
      {toast && (
        <div
          className={`fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg border flex items-center gap-3 transition-all ${
            toast.type === 'error'
              ? 'bg-red-900 text-white border-red-700'
              : 'bg-gray-900 text-white border-gray-700'
          }`}
        >
          {toast.type === 'error' ? (
            <XCircle className="w-5 h-5 text-red-400" />
          ) : (
            <CheckCircle2 className="w-5 h-5 text-green-400" />
          )}
          <span className="text-sm font-medium">{toast.message}</span>
        </div>
      )}

      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <Brain className="w-7 h-7 text-green-600" />
            AI & API Configuration
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            Configure secure API credentials, active models, feature routing, and AI performance.
          </p>
        </div>
        <button
          onClick={handleOpenAddModal}
          className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-semibold text-sm rounded-xl hover:bg-green-700 transition-colors shadow-sm"
        >
          <Plus className="w-4 h-4" />
          Add AI Provider
        </button>
      </div>

      {/* Default Active Provider Banner */}
      <div className="bg-gradient-to-r from-green-900 to-emerald-900 rounded-2xl p-6 text-white shadow-sm border border-green-800">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0 border border-white/20">
              <Sparkles className="w-6 h-6 text-green-300" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="text-xs font-semibold uppercase tracking-wider text-green-300">
                  Global Default AI Provider
                </span>
                <span className="bg-green-500/20 text-green-200 text-xs px-2.5 py-0.5 rounded-full border border-green-400/30">
                  Active
                </span>
              </div>
              <h2 className="text-xl font-bold mt-1">
                {defaultProvider ? defaultProvider.name : 'Fallback (Server Environment Gemini)'}
              </h2>
              <p className="text-xs text-green-200/80 mt-1">
                Model: <code className="bg-black/30 px-2 py-0.5 rounded text-white">{defaultProvider ? defaultProvider.model : 'gemini-2.0-flash'}</code>
                {defaultProvider?.masked_api_key && (
                  <span className="ml-3">API Key: <code className="bg-black/30 px-2 py-0.5 rounded text-green-300">{defaultProvider.masked_api_key}</code></span>
                )}
              </p>
            </div>
          </div>
          {defaultProvider && (
            <div className="flex items-center gap-3">
              <button
                onClick={() => handleTestConnection(defaultProvider.uuid)}
                disabled={testingId === defaultProvider.uuid}
                className="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-medium text-xs rounded-xl border border-white/20 transition-all"
              >
                <RefreshCw className={`w-3.5 h-3.5 ${testingId === defaultProvider.uuid ? 'animate-spin' : ''}`} />
                Test Connection
              </button>
              <button
                onClick={() => handleOpenEditModal(defaultProvider)}
                className="inline-flex items-center gap-2 px-4 py-2 bg-white text-green-900 hover:bg-green-50 font-semibold text-xs rounded-xl shadow-sm transition-all"
              >
                <Sliders className="w-3.5 h-3.5" />
                Configure
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="flex space-x-8">
          <button
            onClick={() => setActiveTab('providers')}
            className={`py-4 px-1 inline-flex items-center gap-2 border-b-2 font-medium text-sm transition-colors ${
              activeTab === 'providers'
                ? 'border-green-600 text-green-600 font-semibold'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            <Server className="w-4 h-4" />
            AI Providers ({providers.length})
          </button>
          <button
            onClick={() => setActiveTab('routes')}
            className={`py-4 px-1 inline-flex items-center gap-2 border-b-2 font-medium text-sm transition-colors ${
              activeTab === 'routes'
                ? 'border-green-600 text-green-600 font-semibold'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            <Layers className="w-4 h-4" />
            Feature Routing
          </button>
          <button
            onClick={() => setActiveTab('analytics')}
            className={`py-4 px-1 inline-flex items-center gap-2 border-b-2 font-medium text-sm transition-colors ${
              activeTab === 'analytics'
                ? 'border-green-600 text-green-600 font-semibold'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            <Activity className="w-4 h-4" />
            Usage & Logs
          </button>
        </nav>
      </div>

      {/* TAB 1: PROVIDERS GRID */}
      {activeTab === 'providers' && (
        <div className="space-y-6">
          {providers.length === 0 ? (
            <div className="bg-white rounded-2xl p-12 text-center border border-gray-200">
              <div className="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mx-auto text-green-600 mb-4">
                <Brain className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-gray-900 text-lg">No AI Providers Configured</h3>
              <p className="text-gray-500 text-sm mt-1 max-w-md mx-auto">
                Mkulima Forum is currently using server-level environment key for Google Gemini. Add your first configured provider to manage keys dynamically.
              </p>
              <button
                onClick={handleOpenAddModal}
                className="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold text-sm rounded-xl hover:bg-green-700 transition-colors shadow-sm"
              >
                <Plus className="w-4 h-4" />
                Configure Google Gemini
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {providers.map((p) => {
                const badge = getProviderBadge(p.provider_type)
                return (
                  <div
                    key={p.uuid}
                    className={`bg-white rounded-2xl border p-6 flex flex-col justify-between transition-all hover:shadow-md ${
                      p.is_default ? 'border-green-500 ring-2 ring-green-500/20' : 'border-gray-200'
                    }`}
                  >
                    <div>
                      {/* Top Bar */}
                      <div className="flex items-start justify-between gap-4">
                        <div className="flex items-center gap-3">
                          <span className={`px-3 py-1 rounded-lg text-xs font-semibold border ${badge.color}`}>
                            {badge.name}
                          </span>
                          {p.is_default && (
                            <span className="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-green-200 flex items-center gap-1">
                              <Zap className="w-3 h-3 text-green-600" /> Default
                            </span>
                          )}
                        </div>

                        {/* Status Toggle Button */}
                        <button
                          onClick={() => handleToggleStatus(p.uuid)}
                          className={`text-xs font-semibold px-3 py-1 rounded-full border transition-colors ${
                            p.status === 'active'
                              ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                              : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'
                          }`}
                        >
                          {p.status === 'active' ? '● Active' : '○ Inactive'}
                        </button>
                      </div>

                      {/* Title & Info */}
                      <h3 className="font-bold text-gray-900 text-lg mt-4">{p.name}</h3>

                      <div className="mt-4 space-y-2 text-xs">
                        <div className="flex items-center justify-between text-gray-600 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                          <span className="font-medium text-gray-500">Selected Model</span>
                          <code className="font-mono text-gray-900 font-semibold bg-white px-2 py-0.5 rounded border border-gray-200">
                            {p.model}
                          </code>
                        </div>

                        <div className="flex items-center justify-between text-gray-600 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                          <span className="font-medium text-gray-500">API Key</span>
                          <span className="font-mono text-gray-800 font-semibold flex items-center gap-1.5">
                            <Key className="w-3 h-3 text-gray-400" />
                            {p.masked_api_key || 'No Key Stored'}
                          </span>
                        </div>

                        <div className="flex items-center justify-between text-gray-600 p-2.5">
                          <span className="text-gray-500">Last Connection Check</span>
                          {p.last_tested_at ? (
                            <span
                              className={`font-medium flex items-center gap-1.5 ${
                                p.last_connection_status === 'success' ? 'text-green-700' : 'text-red-600'
                              }`}
                            >
                              {p.last_connection_status === 'success' ? (
                                <CheckCircle2 className="w-3.5 h-3.5 text-green-600" />
                              ) : (
                                <XCircle className="w-3.5 h-3.5 text-red-500" />
                              )}
                              {new Date(p.last_tested_at).toLocaleDateString()}
                            </span>
                          ) : (
                            <span className="text-gray-400">Not Tested Yet</span>
                          )}
                        </div>
                      </div>
                    </div>

                    {/* Bottom Action Controls */}
                    <div className="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleTestConnection(p.uuid)}
                          disabled={testingId === p.uuid}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-xl transition-colors"
                        >
                          <RefreshCw className={`w-3.5 h-3.5 ${testingId === p.uuid ? 'animate-spin' : ''}`} />
                          Test API
                        </button>
                        {!p.is_default && p.status === 'active' && (
                          <button
                            onClick={() => handleSetDefault(p.uuid)}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 font-semibold text-xs rounded-xl transition-colors border border-green-200"
                          >
                            Set Default
                          </button>
                        )}
                      </div>

                      <div className="flex items-center gap-1">
                        <button
                          onClick={() => handleOpenEditModal(p)}
                          className="p-2 text-gray-500 hover:text-green-700 hover:bg-gray-100 rounded-lg transition-colors"
                          title="Edit Configuration"
                        >
                          <Edit3 className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => handleDeleteProvider(p.uuid, p.name)}
                          className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                          title="Remove Provider"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>
      )}

      {/* TAB 2: FEATURE ROUTING */}
      {activeTab === 'routes' && (
        <div className="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
          <div className="p-6 border-b border-gray-200">
            <h3 className="font-bold text-gray-900 text-lg">AI Feature Provider Routing</h3>
            <p className="text-xs text-gray-500 mt-1">
              Assign specific AI providers or models to individual features in Mkulima Forum (e.g. Gemini for Plant Diagnosis, OpenAI for Agronomist Bot).
            </p>
          </div>
          <div className="divide-y divide-gray-100">
            {featureRoutes.map((r) => (
              <div key={r.feature_key} className="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/50">
                <div>
                  <h4 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
                    <Sparkles className="w-4 h-4 text-green-600" />
                    {r.title}
                  </h4>
                  <code className="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded font-mono mt-1 inline-block">
                    {r.feature_key}
                  </code>
                </div>

                <div className="flex items-center gap-4">
                  <select
                    value={r.ai_provider_id || ''}
                    onChange={(e) => handleUpdateFeatureRoute(r.feature_key, e.target.value)}
                    className="text-xs font-medium border border-gray-300 rounded-xl px-3 py-2 bg-white text-gray-800 focus:ring-2 focus:ring-green-500 focus:outline-none"
                  >
                    <option value="">Use Default Provider ({defaultProvider ? defaultProvider.name : 'Gemini'})</option>
                    {providers.map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.name} ({p.model})
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* TAB 3: USAGE LOGS & ANALYTICS */}
      {activeTab === 'analytics' && (
        <div className="space-y-6">
          {/* Stats Header */}
          {stats && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="bg-white p-5 rounded-2xl border border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase">Total Requests</span>
                <p className="text-2xl font-bold text-gray-900 mt-1">{stats.total_requests}</p>
              </div>
              <div className="bg-white p-5 rounded-2xl border border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase">Success Requests</span>
                <p className="text-2xl font-bold text-green-600 mt-1">{stats.successful_requests}</p>
              </div>
              <div className="bg-white p-5 rounded-2xl border border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase">Avg Response Latency</span>
                <p className="text-2xl font-bold text-blue-600 mt-1">{stats.average_latency_ms} ms</p>
              </div>
              <div className="bg-white p-5 rounded-2xl border border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase">Total Tokens Processed</span>
                <p className="text-2xl font-bold text-purple-600 mt-1">{stats.total_tokens_consumed}</p>
              </div>
            </div>
          )}

          {/* Logs Table */}
          <div className="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <h3 className="font-bold text-gray-900 text-lg">Recent AI Request Audit Logs</h3>
              <button
                onClick={fetchStats}
                className="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1.5 rounded-lg"
              >
                <RefreshCw className="w-3.5 h-3.5" /> Refresh Logs
              </button>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase font-semibold">
                    <th className="p-4">Timestamp</th>
                    <th className="p-4">Feature</th>
                    <th className="p-4">Provider / Model</th>
                    <th className="p-4">Latency</th>
                    <th className="p-4">Tokens</th>
                    <th className="p-4">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {logs.length === 0 ? (
                    <tr>
                      <td colSpan="6" className="p-8 text-center text-gray-400">
                        No AI requests recorded yet.
                      </td>
                    </tr>
                  ) : (
                    logs.map((log) => (
                      <tr key={log.id} className="hover:bg-gray-50">
                        <td className="p-4 text-gray-600">{new Date(log.created_at).toLocaleString()}</td>
                        <td className="p-4 font-semibold text-gray-800">{log.feature}</td>
                        <td className="p-4 text-gray-600">
                          <span className="font-medium text-gray-900">{log.provider?.name || log.provider_type}</span>{' '}
                          <span className="text-gray-400">({log.model})</span>
                        </td>
                        <td className="p-4 font-mono text-gray-600">{log.latency_ms} ms</td>
                        <td className="p-4 font-mono text-gray-600">{log.total_tokens ?? 'N/A'}</td>
                        <td className="p-4">
                          <span
                            className={`px-2.5 py-0.5 rounded-full font-semibold ${
                              log.status === 'success'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'
                            }`}
                          >
                            {log.status}
                          </span>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* ADD / EDIT PROVIDER MODAL */}
      {showModal && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-xl border border-gray-200">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <h3 className="font-bold text-gray-900 text-lg flex items-center gap-2">
                <Brain className="w-5 h-5 text-green-600" />
                {editingProvider ? 'Configure AI Provider' : 'Add AI Provider'}
              </h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-gray-400 hover:text-gray-600 p-1 rounded-lg"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSaveProvider} className="p-6 space-y-4 text-xs">
              {/* Provider Name & Type */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Provider Display Name</label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    placeholder="e.g. Google Gemini Pro"
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Provider Type</label>
                  <select
                    value={formData.provider_type}
                    onChange={(e) => setFormData({ ...formData, provider_type: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none bg-white"
                  >
                    <option value="gemini">Google Gemini</option>
                    <option value="openai">OpenAI</option>
                    <option value="claude">Anthropic Claude</option>
                    <option value="deepseek">DeepSeek</option>
                    <option value="groq">Groq</option>
                    <option value="openrouter">OpenRouter</option>
                    <option value="custom">Custom OpenAI-Compatible</option>
                  </select>
                </div>
              </div>

              {/* API Key */}
              <div>
                <label className="block font-semibold text-gray-700 mb-1 flex items-center justify-between">
                  <span>API Key</span>
                  {editingProvider && (
                    <button
                      type="button"
                      onClick={() => setFormData({ ...formData, replace_api_key: !formData.replace_api_key })}
                      className="text-green-700 hover:underline font-normal text-xs"
                    >
                      {formData.replace_api_key ? 'Keep Existing Key' : 'Replace Key'}
                    </button>
                  )}
                </label>

                {(!editingProvider || formData.replace_api_key) ? (
                  <input
                    type="password"
                    required={!editingProvider}
                    value={formData.api_key}
                    onChange={(e) => setFormData({ ...formData, api_key: e.target.value })}
                    placeholder="Enter Secret API Key (e.g. AIzaSy...)"
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none font-mono"
                  />
                ) : (
                  <div className="px-3 py-2 border border-gray-200 bg-gray-50 rounded-xl text-gray-600 font-mono flex items-center justify-between">
                    <span>Key Saved Encrypted: {editingProvider.masked_api_key}</span>
                    <span className="text-green-600 text-xs font-semibold">✓ Encrypted</span>
                  </div>
                )}
              </div>

              {/* Model & Fetch Models */}
              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="block font-semibold text-gray-700">Model Name</label>
                  {editingProvider && (
                    <button
                      type="button"
                      onClick={() => handleFetchModels(editingProvider.uuid)}
                      disabled={fetchingModels}
                      className="text-xs text-green-700 hover:underline flex items-center gap-1"
                    >
                      <RefreshCw className={`w-3 h-3 ${fetchingModels ? 'animate-spin' : ''}`} />
                      Fetch Available Models
                    </button>
                  )}
                </div>

                {fetchedModels.length > 0 ? (
                  <select
                    value={formData.model}
                    onChange={(e) => setFormData({ ...formData, model: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none bg-white"
                  >
                    {fetchedModels.map((m) => (
                      <option key={m.id} value={m.id}>
                        {m.name} ({m.id})
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    type="text"
                    required
                    value={formData.model}
                    onChange={(e) => setFormData({ ...formData, model: e.target.value })}
                    placeholder="e.g. gemini-2.0-flash"
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none font-mono"
                  />
                )}
              </div>

              {/* Base URL (Optional) */}
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Base API URL (Optional)</label>
                <input
                  type="url"
                  value={formData.base_url}
                  onChange={(e) => setFormData({ ...formData, base_url: e.target.value })}
                  placeholder="Leave empty for default endpoint"
                  className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none font-mono"
                />
              </div>

              {/* Parameters: Temp, Max Tokens, Timeout */}
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Temperature</label>
                  <input
                    type="number"
                    step="0.1"
                    min="0"
                    max="2"
                    value={formData.temperature}
                    onChange={(e) => setFormData({ ...formData, temperature: parseFloat(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Max Tokens</label>
                  <input
                    type="number"
                    value={formData.max_tokens}
                    onChange={(e) => setFormData({ ...formData, max_tokens: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Timeout (s)</label>
                  <input
                    type="number"
                    value={formData.timeout}
                    onChange={(e) => setFormData({ ...formData, timeout: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none"
                  />
                </div>
              </div>

              {/* Checkboxes */}
              <div className="space-y-2 pt-2">
                <label className="flex items-center gap-2 text-gray-700 font-semibold cursor-pointer">
                  <input
                    type="checkbox"
                    checked={formData.is_default}
                    onChange={(e) => setFormData({ ...formData, is_default: e.target.checked })}
                    className="rounded text-green-600 focus:ring-green-500 h-4 w-4"
                  />
                  Set as Global Default AI Provider
                </label>

                <label className="flex items-center gap-2 text-gray-700 font-semibold cursor-pointer">
                  <input
                    type="checkbox"
                    checked={formData.status === 'active'}
                    onChange={(e) => setFormData({ ...formData, status: e.target.checked ? 'active' : 'inactive' })}
                    className="rounded text-green-600 focus:ring-green-500 h-4 w-4"
                  />
                  Provider Active & Operational
                </label>
              </div>

              {/* Buttons */}
              <div className="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 text-gray-600 hover:bg-gray-100 font-semibold rounded-xl transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2"
                >
                  {submitting && <RefreshCw className="w-3.5 h-3.5 animate-spin" />}
                  {editingProvider ? 'Update Configuration' : 'Save AI Provider'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
