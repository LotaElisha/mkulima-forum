import { useState, useEffect, useCallback } from 'react'
import { Flag, CheckCircle, XCircle, EyeOff } from 'lucide-react'

const REASON_LABELS = {
  spam: 'Spam',
  misleading: 'Misleading info',
  fraud: 'Fraud',
  abuse: 'Abuse',
  counterfeit: 'Counterfeit product',
  other: 'Other',
}

const TYPE_LABELS = {
  forum_thread: 'Forum thread',
  forum_reply: 'Forum reply',
  product: 'Product',
  user: 'User',
}

export default function ModerationPage() {
  const [reports, setReports] = useState([])
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [filterStatus, setFilterStatus] = useState('pending')

  const fetchReports = useCallback(async () => {
    setLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      let url = '/api/admin/reports'
      if (filterStatus) url += `?status=${filterStatus}`
      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } })
      if (res.ok) {
        const data = await res.json()
        setReports(data.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }, [filterStatus])

  useEffect(() => {
    fetchReports()
  }, [fetchReports])

  const act = async (uuid, endpoint, body) => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`/api/admin/reports/${uuid}/${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(body),
      })
      const data = await res.json()
      setMessage(data.message || 'Done')
      fetchReports()
    } catch {
      setMessage('Action failed')
    }
  }

  const handleHide = (report) => {
    if (!confirm(`Hide this ${TYPE_LABELS[report.type] || report.type}? It will disappear from public feeds.`)) return
    const notes = prompt('Resolution notes (optional):') || ''
    act(report.uuid, 'resolve', { action: 'content_hidden', notes })
  }

  const handleResolveNoAction = (report) => {
    const notes = prompt('Resolution notes (optional):') || ''
    act(report.uuid, 'resolve', { action: 'none', notes })
  }

  const handleDismiss = (report) => {
    const notes = prompt('Why is this report dismissed? (optional)') || ''
    act(report.uuid, 'dismiss', { notes })
  }

  return (
    <div className="p-6">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold flex items-center gap-2">
          <Flag className="text-red-600" /> Content Moderation
        </h1>
        <select
          value={filterStatus}
          onChange={(e) => setFilterStatus(e.target.value)}
          className="border rounded-lg px-3 py-2"
        >
          <option value="pending">Pending</option>
          <option value="resolved">Resolved</option>
          <option value="dismissed">Dismissed</option>
          <option value="">All</option>
        </select>
      </div>

      {message && (
        <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800">
          {message}
        </div>
      )}

      {loading ? (
        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600 mx-auto mt-16" />
      ) : reports.length === 0 ? (
        <div className="text-center text-gray-500 mt-16">No reports in this queue.</div>
      ) : (
        <div className="space-y-4">
          {reports.map((report) => (
            <div key={report.uuid} className="bg-white rounded-xl shadow p-4">
              <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                  <div className="flex items-center gap-2 flex-wrap">
                    <span className="px-2 py-0.5 rounded-full text-xs bg-gray-100">
                      {TYPE_LABELS[report.type] || report.type}
                    </span>
                    <span className="px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-700">
                      {REASON_LABELS[report.reason] || report.reason}
                    </span>
                    <span className="text-xs text-gray-400">
                      {new Date(report.created_at).toLocaleString()}
                    </span>
                  </div>
                  {report.target_preview ? (
                    <p className="mt-2 text-sm text-gray-800 truncate">
                      <span className="font-medium">Target:</span>{' '}
                      {report.target_preview.title || report.target_preview.body || report.target_preview.name}
                      {report.target_preview.status && (
                        <span className="ml-2 text-xs text-gray-500">({report.target_preview.status})</span>
                      )}
                    </p>
                  ) : (
                    <p className="mt-2 text-sm text-gray-400 italic">Target content no longer exists.</p>
                  )}
                  {report.details && (
                    <p className="mt-1 text-sm text-gray-600">“{report.details}”</p>
                  )}
                  <p className="mt-1 text-xs text-gray-500">
                    Reported by {report.reporter?.name || 'unknown'} ({report.reporter?.role})
                  </p>
                  {report.status !== 'pending' && (
                    <p className="mt-1 text-xs text-gray-500">
                      Outcome: {report.resolution_action || '—'}
                      {report.resolution_notes ? ` — ${report.resolution_notes}` : ''}
                    </p>
                  )}
                </div>
                {report.status === 'pending' && (
                  <div className="flex flex-col gap-2 shrink-0">
                    <button
                      onClick={() => handleHide(report)}
                      className="flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700"
                    >
                      <EyeOff size={14} /> Hide content
                    </button>
                    <button
                      onClick={() => handleResolveNoAction(report)}
                      className="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"
                    >
                      <CheckCircle size={14} /> Resolve
                    </button>
                    <button
                      onClick={() => handleDismiss(report)}
                      className="flex items-center gap-1 px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300"
                    >
                      <XCircle size={14} /> Dismiss
                    </button>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
