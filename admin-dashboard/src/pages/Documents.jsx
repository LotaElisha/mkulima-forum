import { useEffect, useState } from 'react'
import { FileText, RefreshCw, Loader2, Search, ExternalLink } from 'lucide-react'
import { documentsApi, API_BASE } from '../utils/api'

const formatSize = (bytes) => {
  if (!bytes) return '—'
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unit = 0
  while (size >= 1024 && unit < units.length - 1) {
    size /= 1024
    unit++
  }
  return `${size.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`
}

const formatDate = (value) => (value ? new Date(value).toLocaleString() : '—')

export default function Documents() {
  const [documents, setDocuments] = useState([])
  const [integration, setIntegration] = useState(null)
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(true)
  const [syncing, setSyncing] = useState(false)
  const [message, setMessage] = useState(null)
  const [error, setError] = useState(null)

  const load = async (query = search) => {
    setLoading(true)
    setError(null)
    try {
      const { data } = await documentsApi.list(query ? { search: query } : {})
      setDocuments(data.documents?.data || [])
      setIntegration(data.integration || null)
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load documents')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load('')
  }, [])

  const sync = async () => {
    setSyncing(true)
    setMessage(null)
    setError(null)
    try {
      const { data } = await documentsApi.sync()
      setMessage(data.message)
      await load()
    } catch (err) {
      setError(err.response?.data?.message || 'Sync failed')
    } finally {
      setSyncing(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Documents</h1>
          <p className="text-sm text-gray-500">
            Files synchronized from the Google Drive folder.
            {integration?.last_synced_at && ` Last synced ${formatDate(integration.last_synced_at)}.`}
          </p>
        </div>
        <button
          onClick={sync}
          disabled={syncing || integration?.configured === false}
          className="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800 disabled:opacity-50"
        >
          {syncing ? <Loader2 className="h-4 w-4 animate-spin" /> : <RefreshCw className="h-4 w-4" />}
          Sync from Drive
        </button>
      </div>

      {integration?.configured === false && (
        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
          Google Drive is not configured. Add the credentials under System → Configuration to enable syncing.
        </div>
      )}
      {message && <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{message}</div>}
      {error && <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div>}

      <form
        onSubmit={(e) => {
          e.preventDefault()
          load()
        }}
        className="relative max-w-md"
      >
        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search documents"
          className="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm focus:border-green-600 focus:outline-none"
        />
      </form>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
        {loading ? (
          <div className="flex justify-center p-10">
            <Loader2 className="h-6 w-6 animate-spin text-green-700" />
          </div>
        ) : documents.length === 0 ? (
          <div className="p-10 text-center text-sm text-gray-500">No documents yet.</div>
        ) : (
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-left text-gray-500">
              <tr>
                <th className="px-4 py-3 font-medium">Name</th>
                <th className="px-4 py-3 font-medium">Size</th>
                <th className="px-4 py-3 font-medium">Modified</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {documents.map((doc) => (
                <tr key={doc.id}>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2 text-gray-900">
                      <FileText className="h-4 w-4 text-green-700" />
                      {doc.name}
                    </div>
                  </td>
                  <td className="px-4 py-3 text-gray-500">{formatSize(doc.size)}</td>
                  <td className="px-4 py-3 text-gray-500">{formatDate(doc.drive_modified_at)}</td>
                  <td className="px-4 py-3 text-right">
                    <a
                      href={`${API_BASE}/admin/documents/${doc.id}/open`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-green-700 hover:underline"
                    >
                      Open <ExternalLink className="h-3.5 w-3.5" />
                    </a>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}
