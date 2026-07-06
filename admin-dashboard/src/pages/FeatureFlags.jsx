import { useEffect, useState } from 'react'
import { ToggleLeft, ToggleRight, Loader2, RefreshCw, Layers, Brain, Users, Rocket } from 'lucide-react'
import { API_BASE } from '../utils/api'

const phaseConfig = {
  phase1: { name: 'Phase 1 - Core Services', icon: Layers, color: 'green', bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200' },
  phase2: { name: 'Phase 2 - AI Intelligence', icon: Brain, color: 'blue', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200' },
  phase3: { name: 'Phase 3 - Community & Trust', icon: Users, color: 'purple', bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200' },
  phase4: { name: 'Phase 4 - Future Tech', icon: Rocket, color: 'orange', bg: 'bg-orange-50', text: 'text-orange-700', border: 'border-orange-200' },
}

const featureMeta = {
  weather: { name: 'Weather Alerts', desc: 'Real-time weather updates and farming tips', icon: 'Sun' },
  sms_ussd: { name: 'SMS/USSD', desc: 'SMS commands for market prices and weather', icon: 'MessageSquare' },
  wallet: { name: 'Mkulima Pay', desc: 'Digital wallet for payments', icon: 'Wallet' },
  ivr: { name: 'IVR Voice', desc: 'Voice call services', icon: 'Phone' },
  ai_agronomist: { name: 'AI Agronomist', desc: 'AI-powered farming advice', icon: 'Brain' },
  crop_scanner: { name: 'Crop Scanner', desc: 'AI crop health diagnosis', icon: 'Camera' },
  price_prediction: { name: 'Price Prediction', desc: 'AI market price forecasting', icon: 'TrendingUp' },
  notifications: { name: 'Smart Notifications', desc: 'Push notifications for weather, prices, orders', icon: 'Bell' },
  offline_mode: { name: 'Offline Mode', desc: 'Browse without internet connection', icon: 'WifiOff' },
  community_groups: { name: 'Community Groups', desc: 'Farmer groups and cooperatives', icon: 'Users' },
  live_streaming: { name: 'Live Streaming', desc: 'Live broadcasts for farmers', icon: 'Video' },
  blockchain_certificates: { name: 'Blockchain Certificates', desc: 'Tamper-proof organic certificates', icon: 'Award' },
  drone_services: { name: 'Drone Services', desc: 'Drone spraying and mapping', icon: 'Plane' },
  iot_sensors: { name: 'IoT Sensors', desc: 'Soil moisture and weather sensors', icon: 'Cpu' },
  yield_estimation: { name: 'Yield Estimation', desc: 'AI harvest prediction', icon: 'Calculator' },
  escrow: { name: 'Mkulima Escrow', desc: 'Secure payment escrow', icon: 'Shield' },
}

const featureCategories = {
  weather: 'phase1', sms_ussd: 'phase1', wallet: 'phase1', ivr: 'phase1',
  ai_agronomist: 'phase2', crop_scanner: 'phase2', price_prediction: 'phase2', notifications: 'phase2',
  offline_mode: 'phase3', community_groups: 'phase3', live_streaming: 'phase3', blockchain_certificates: 'phase3',
  drone_services: 'phase4', iot_sensors: 'phase4', yield_estimation: 'phase4', escrow: 'phase4',
}

export default function FeatureFlags() {
  const [features, setFeatures] = useState({})
  const [loading, setLoading] = useState(true)
  const [toggling, setToggling] = useState(null)
  const [error, setError] = useState(null)

  const token = localStorage.getItem('admin_token')

  const fetchFeatures = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await fetch(`${API_BASE}/admin/features`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (!res.ok) throw new Error('Failed to fetch features')
      const data = await res.json()
      setFeatures(data.features || {})
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const toggleFeature = async (key) => {
    setToggling(key)
    try {
      const res = await fetch(`${API_BASE}/admin/features/${key}/toggle`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      if (!res.ok) throw new Error('Failed to toggle')
      const data = await res.json()
      setFeatures(prev => ({ ...prev, [key]: { ...prev[key], enabled: data.feature.enabled } }))
    } catch (err) {
      alert(err.message)
    } finally {
      setToggling(null)
    }
  }

  useEffect(() => {
    fetchFeatures()
  }, [])

  const values = Object.values(features)
  const enabledCount = values.filter(f => f.enabled).length
  const totalCount = values.length

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <Loader2 className="w-10 h-10 text-green-600 animate-spin" />
      </div>
    )
  }

  if (error) {
    return (
      <div className="text-center py-20">
        <p className="text-red-600 mb-4">{error}</p>
        <button onClick={fetchFeatures} className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
          Retry
        </button>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Feature Flags</h1>
          <p className="text-gray-500 mt-1">Control all Phase 1-4 features from one place</p>
        </div>
        <button
          onClick={fetchFeatures}
          className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700"
        >
          <RefreshCw className="w-4 h-4" />
          Refresh
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-white p-6 rounded-xl border border-gray-200">
          <div className="text-3xl font-bold text-gray-900">{totalCount}</div>
          <div className="text-gray-500">Total Features</div>
        </div>
        <div className="bg-white p-6 rounded-xl border border-gray-200">
          <div className="text-3xl font-bold text-green-600">{enabledCount}</div>
          <div className="text-gray-500">Enabled</div>
        </div>
        <div className="bg-white p-6 rounded-xl border border-gray-200">
          <div className="text-3xl font-bold text-red-600">{totalCount - enabledCount}</div>
          <div className="text-gray-500">Disabled</div>
        </div>
      </div>

      {Object.entries(phaseConfig).map(([phaseKey, phase]) => {
        const phaseFeatures = Object.entries(features).filter(([key]) => featureCategories[key] === phaseKey)
        if (phaseFeatures.length === 0) return null
        const PhaseIcon = phase.icon
        const activeCount = phaseFeatures.filter(([_, f]) => f.enabled).length

        return (
          <div key={phaseKey} className={`bg-white rounded-xl border ${phase.border} overflow-hidden`}>
            <div className={`${phase.bg} px-6 py-4 border-b ${phase.border} flex items-center justify-between`}>
              <div className="flex items-center gap-3">
                <PhaseIcon className={`w-6 h-6 ${phase.text}`} />
                <h2 className={`text-lg font-bold ${phase.text}`}>{phase.name}</h2>
              </div>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${phase.bg} ${phase.text} border ${phase.border}`}>
                {activeCount}/{phaseFeatures.length} Active
              </span>
            </div>
            <div className="divide-y divide-gray-100">
              {phaseFeatures.map(([key, feature]) => {
                const meta = featureMeta[key] || { name: key, desc: '', icon: 'Circle' }
                return (
                  <div key={key} className="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors">
                    <div className="flex items-center gap-4">
                      <div className={`w-12 h-12 rounded-xl ${phase.bg} ${phase.text} flex items-center justify-center`}>
                        <span className="text-xl font-bold">{meta.name.charAt(0)}</span>
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-900">{meta.name}</h3>
                        <p className="text-sm text-gray-500">{meta.desc}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-4">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${feature.enabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                        {feature.enabled ? 'Enabled' : 'Disabled'}
                      </span>
                      <button
                        onClick={() => toggleFeature(key)}
                        disabled={toggling === key}
                        className="text-gray-400 hover:text-gray-600 disabled:opacity-50"
                      >
                        {toggling === key ? (
                          <Loader2 className="w-8 h-8 animate-spin text-green-600" />
                        ) : feature.enabled ? (
                          <ToggleRight className="w-10 h-10 text-green-600" />
                        ) : (
                          <ToggleLeft className="w-10 h-10 text-gray-400" />
                        )}
                      </button>
                    </div>
                  </div>
                )
              })}
            </div>
          </div>
        )
      })}
    </div>
  )
}
