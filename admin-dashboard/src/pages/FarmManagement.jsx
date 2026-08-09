import { useState, useEffect } from 'react'
import { Tractor, Search, Plus, MapPin, Calendar, Sprout, DollarSign, Activity, FileText, CheckCircle2, Trash2, Eye, X } from 'lucide-react'

const STATUS_BADGES = {
  active:     'bg-emerald-100 text-emerald-800 border-emerald-200',
  harvesting: 'bg-amber-100 text-amber-800 border-amber-200',
  fallow:     'bg-blue-100 text-blue-800 border-blue-200',
  archived:   'bg-gray-100 text-gray-700 border-gray-200',
}

export default function FarmManagement() {
  const [farms, setFarms] = useState([])
  const [summary, setSummary] = useState({})
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [message, setMessage] = useState({ text: '', type: '' })

  // Modal states
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [createForm, setCreateForm] = useState({
    name: '',
    location: '',
    size_acres: '',
    crop_type: 'Mahindi',
    soil_type: 'Loam',
    planting_date: '',
    status: 'active',
    notes: '',
  })
  const [creating, setCreating] = useState(false)

  // Farm Details / Activity Modal
  const [activeFarm, setActiveFarm] = useState(null)
  const [activityForm, setActivityForm] = useState({
    activity_type: 'Kupanda',
    activity_date: new Date().toISOString().split('T')[0],
    cost_tzs: '',
    notes: '',
  })
  const [loggingActivity, setLoggingActivity] = useState(false)

  useEffect(() => {
    fetchFarms()
  }, [search, statusFilter])

  const fetchFarms = async () => {
    setLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      const params = new URLSearchParams()
      if (search) params.append('search', search)
      if (statusFilter) params.append('status', statusFilter)

      const res = await fetch(`/api/admin/farms?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      })
      if (res.ok) {
        const data = await res.json()
        setFarms(data.farms?.data || [])
        setSummary(data.summary || {})
      }
    } catch (err) {
      console.error('Failed to fetch farms:', err)
    } finally {
      setLoading(false)
    }
  }

  const notify = (text, type = 'success') => {
    setMessage({ text, type })
    setTimeout(() => setMessage({ text: '', type: '' }), 4000)
  }

  const handleCreateFarm = async (e) => {
    e.preventDefault()
    setCreating(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/farms', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...createForm,
          user_id: 1, // Default admin user ID for admin-created farm
        }),
      })
      const data = await res.json()
      if (res.ok) {
        notify('Farm registered successfully!')
        setShowCreateModal(false)
        setCreateForm({
          name: '',
          location: '',
          size_acres: '',
          crop_type: 'Mahindi',
          soil_type: 'Loam',
          planting_date: '',
          status: 'active',
          notes: '',
        })
        fetchFarms()
      } else {
        notify(data.message || 'Failed to create farm', 'error')
      }
    } catch (err) {
      notify('Network error registering farm', 'error')
    } finally {
      setCreating(false)
    }
  }

  const handleLogActivity = async (e) => {
    e.preventDefault()
    if (!activeFarm) return
    setLoggingActivity(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/farms/${activeFarm.uuid}/activities`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(activityForm),
      })
      const data = await res.json()
      if (res.ok) {
        notify('Activity log entry recorded!')
        setActiveFarm(data.farm)
        setActivityForm({
          activity_type: 'Kupanda',
          activity_date: new Date().toISOString().split('T')[0],
          cost_tzs: '',
          notes: '',
        })
        fetchFarms()
      } else {
        notify(data.message || 'Failed to record activity', 'error')
      }
    } catch (err) {
      notify('Network error logging activity', 'error')
    } finally {
      setLoggingActivity(false)
    }
  }

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <Tractor className="w-7 h-7 text-emerald-600" /> Farm Management & Crop Tracking
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            Monitor registered farms, crop types, planting dates, and agricultural operations across East Africa
          </p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-md transition-all"
        >
          <Plus className="w-4 h-4" /> Register New Farm
        </button>
      </div>

      {message.text && (
        <div className={`p-4 rounded-xl text-sm font-medium ${message.type === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`}>
          {message.text}
        </div>
      )}

      {/* Analytics KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center gap-4">
          <div className="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
            <Tractor className="w-6 h-6" />
          </div>
          <div>
            <div className="text-xs text-gray-500 font-medium">Registered Farms</div>
            <div className="text-2xl font-bold text-gray-900">{summary.total_farms || 0}</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center gap-4">
          <div className="p-3 bg-blue-50 text-blue-600 rounded-xl border border-blue-100">
            <Sprout className="w-6 h-6" />
          </div>
          <div>
            <div className="text-xs text-gray-500 font-medium">Total Land Managed</div>
            <div className="text-2xl font-bold text-gray-900">{Number(summary.total_acres || 0).toFixed(1)} Acres</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center gap-4">
          <div className="p-3 bg-amber-50 text-amber-600 rounded-xl border border-amber-100">
            <Activity className="w-6 h-6" />
          </div>
          <div>
            <div className="text-xs text-gray-500 font-medium">Active Farms</div>
            <div className="text-2xl font-bold text-gray-900">{summary.active_farms || 0}</div>
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center gap-4">
          <div className="p-3 bg-purple-50 text-purple-600 rounded-xl border border-purple-100">
            <Calendar className="w-6 h-6" />
          </div>
          <div>
            <div className="text-xs text-gray-500 font-medium">Primary Crop</div>
            <div className="text-lg font-bold text-gray-900">{summary.primary_crops?.[0]?.crop_type || 'Mahindi'}</div>
          </div>
        </div>
      </div>

      {/* Search & Filters */}
      <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-wrap gap-3 items-center justify-between">
        <div className="relative flex-1 min-w-[240px]">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Search farm by name, farmer, crop, location..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="harvesting">Harvesting</option>
          <option value="fallow">Fallow</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      {/* Farms Table */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-gray-500">Loading registered farms...</div>
        ) : farms.length === 0 ? (
          <div className="p-8 text-center text-gray-500">No farms registered yet.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm text-gray-600">
              <thead className="bg-gray-50 text-gray-700 uppercase font-semibold text-xs border-b border-gray-100">
                <tr>
                  <th className="px-6 py-4">Farm & Location</th>
                  <th className="px-6 py-4">Farmer</th>
                  <th className="px-6 py-4">Crop & Size</th>
                  <th className="px-6 py-4">Planting Date</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {farms.map((farm) => (
                  <tr key={farm.uuid} className="hover:bg-gray-50/50 transition-colors">
                    <td className="px-6 py-4 font-medium text-gray-900">
                      <div className="font-semibold text-gray-900">{farm.name}</div>
                      <div className="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                        <MapPin className="w-3 h-3 text-emerald-600" /> {farm.location}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-xs">
                        <div className="font-medium text-gray-900">{farm.user?.name || 'Mkulima'}</div>
                        <div className="text-gray-400">{farm.user?.phone || 'N/A'}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-xs">
                        <span className="font-semibold text-emerald-700">{farm.crop_type}</span>
                        <div className="text-gray-500">{farm.size_acres} Acres ({farm.soil_type || 'Loam'})</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-xs text-gray-600">
                      {farm.planting_date ? new Date(farm.planting_date).toLocaleDateString() : 'N/A'}
                    </td>
                    <td className="px-6 py-4">
                      <span className={`px-2.5 py-1 rounded-full text-xs font-semibold border ${STATUS_BADGES[farm.status] || 'bg-gray-100'}`}>
                        {farm.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => setActiveFarm(farm)}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium text-xs rounded-xl border border-emerald-200 transition-colors"
                      >
                        <Eye className="w-3.5 h-3.5" /> View Activity Log ({farm.activities_count || 0})
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Register Farm Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
              <h3 className="text-lg font-bold text-gray-900 flex items-center gap-2">
                <Tractor className="w-5 h-5 text-emerald-600" /> Register New Farm
              </h3>
              <button onClick={() => setShowCreateModal(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreateFarm} className="space-y-4 text-sm">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Farm Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Shamba la Mahindi Kilosa"
                  value={createForm.name}
                  onChange={(e) => setCreateForm({ ...createForm, name: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Location / Region *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Mbeya Vijijini"
                    value={createForm.location}
                    onChange={(e) => setCreateForm({ ...createForm, location: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Size (Acres) *</label>
                  <input
                    type="number"
                    step="0.1"
                    required
                    placeholder="e.g. 5.0"
                    value={createForm.size_acres}
                    onChange={(e) => setCreateForm({ ...createForm, size_acres: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Crop Type *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Mahindi, Mpunga, Avocado"
                    value={createForm.crop_type}
                    onChange={(e) => setCreateForm({ ...createForm, crop_type: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Soil Type</label>
                  <select
                    value={createForm.soil_type}
                    onChange={(e) => setCreateForm({ ...createForm, soil_type: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="Loam">Loam (Udongo Mwekundu)</option>
                    <option value="Clay">Clay (Tofali)</option>
                    <option value="Sandy">Sandy (Kichanga)</option>
                    <option value="Black Cotton">Black Cotton (Mbuga)</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Planting Date</label>
                  <input
                    type="date"
                    value={createForm.planting_date}
                    onChange={(e) => setCreateForm({ ...createForm, planting_date: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                  <select
                    value={createForm.status}
                    onChange={(e) => setCreateForm({ ...createForm, status: e.target.value })}
                    className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="active">Active (Linaoteshwa)</option>
                    <option value="harvesting">Harvesting (Uvunaji)</option>
                    <option value="fallow">Fallow (Kupumzisha)</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Notes / Farming Plan</label>
                <textarea
                  rows="2"
                  placeholder="Additional notes about irrigation, fertilizers, seed variety..."
                  value={createForm.notes}
                  onChange={(e) => setCreateForm({ ...createForm, notes: e.target.value })}
                  className="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                ></textarea>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 text-sm hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={creating}
                  className="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md disabled:opacity-50"
                >
                  {creating ? 'Registering...' : 'Register Farm'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Farm Activity Log Modal */}
      {activeFarm && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-start pb-4 border-b border-gray-100 mb-4">
              <div>
                <h3 className="text-xl font-bold text-gray-900">{activeFarm.name}</h3>
                <p className="text-xs text-gray-500 mt-0.5">
                  {activeFarm.crop_type} • {activeFarm.size_acres} Acres • {activeFarm.location}
                </p>
              </div>
              <button onClick={() => setActiveFarm(null)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Log New Activity Form */}
            <form onSubmit={handleLogActivity} className="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100 space-y-3 mb-6">
              <h4 className="text-xs font-bold text-emerald-900 uppercase tracking-wider">Log Agricultural Activity</h4>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Activity Type *</label>
                  <select
                    value={activityForm.activity_type}
                    onChange={(e) => setActivityForm({ ...activityForm, activity_type: e.target.value })}
                    className="w-full px-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="Kupanda">Kupanda (Planting)</option>
                    <option value="Kupalilia">Kupalilia (Weeding)</option>
                    <option value="Kupiga Dawa">Kupiga Dawa (Pest Spray)</option>
                    <option value="Kuweka Mbolea">Kuweka Mbolea (Fertilizing)</option>
                    <option value="Kuvuna">Kuvuna (Harvesting)</option>
                    <option value="Uwagiliaji">Uwagiliaji (Irrigation)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Date *</label>
                  <input
                    type="date"
                    required
                    value={activityForm.activity_date}
                    onChange={(e) => setActivityForm({ ...activityForm, activity_date: e.target.value })}
                    className="w-full px-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Cost (TZS)</label>
                  <input
                    type="number"
                    placeholder="e.g. 50000"
                    value={activityForm.cost_tzs}
                    onChange={(e) => setActivityForm({ ...activityForm, cost_tzs: e.target.value })}
                    className="w-full px-3 py-1.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500"
                  />
                </div>
              </div>
              <div className="flex gap-2">
                <input
                  type="text"
                  placeholder="Notes (e.g. Applied DAP 50kg, sprayed Karate 500ml...)"
                  value={activityForm.notes}
                  onChange={(e) => setActivityForm({ ...activityForm, notes: e.target.value })}
                  className="flex-1 px-3 py-1.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500"
                />
                <button
                  type="submit"
                  disabled={loggingActivity}
                  className="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-xs disabled:opacity-50"
                >
                  {loggingActivity ? 'Saving...' : 'Add Log'}
                </button>
              </div>
            </form>

            {/* Activity Timeline List */}
            <h4 className="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Activity History Log</h4>
            {activeFarm.activities?.length === 0 ? (
              <div className="p-6 text-center text-xs text-gray-400 border border-dashed border-gray-200 rounded-2xl">
                No activity logs recorded yet. Add the first activity above!
              </div>
            ) : (
              <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                {activeFarm.activities?.map((act) => (
                  <div key={act.uuid} className="bg-gray-50 p-3 rounded-xl border border-gray-100 flex justify-between items-center text-xs">
                    <div>
                      <div className="font-semibold text-gray-900 flex items-center gap-2">
                        <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {act.activity_type}
                        <span className="text-[10px] text-gray-400 font-normal">
                          {new Date(act.activity_date).toLocaleDateString()}
                        </span>
                      </div>
                      {act.notes && <div className="text-gray-500 mt-0.5 ml-4">{act.notes}</div>}
                    </div>
                    {Number(act.cost_tzs) > 0 && (
                      <div className="font-semibold text-emerald-800 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                        {Number(act.cost_tzs).toLocaleString()} TZS
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}

            <div className="mt-6 flex justify-end">
              <button onClick={() => setActiveFarm(null)} className="px-5 py-2 bg-gray-900 text-white rounded-xl font-medium text-xs">
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
