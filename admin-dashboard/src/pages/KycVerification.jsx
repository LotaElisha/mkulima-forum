import { useState, useEffect } from 'react'
import { UserCheck, CheckCircle, XCircle, Eye, FileText } from 'lucide-react'

export default function KycVerificationPage() {
  const [kycList, setKycList] = useState([])
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [selectedUser, setSelectedUser] = useState(null)

  useEffect(() => {
    fetchKyc()
  }, [])

  const fetchKyc = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/kyc/pending', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setKycList(data.kyc?.data || [])
      }
    } catch (err) {
      console.error('Failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleApprove = async (uuid) => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`http://76.13.56.180:8000/api/admin/kyc/${uuid}/verify`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      const data = await res.json()
      setMessage(data.message || 'KYC approved')
      fetchKyc()
    } catch (err) {
      setMessage('Failed to approve')
    }
  }

  const handleReject = async (uuid) => {
    const reason = prompt('Rejection reason:')
    if (!reason) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch(`http://76.13.56.180:8000/api/admin/kyc/${uuid}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ reason })
      })
      const data = await res.json()
      setMessage(data.message || 'KYC rejected')
      fetchKyc()
    } catch (err) {
      setMessage('Failed to reject')
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">KYC Verification</h1>
        <p className="text-gray-500 mt-1">Review and verify farmer identities</p>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{message}</div>
      )}

      <div className="card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Applicant</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Phone</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Role</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Submitted</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Documents</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="py-8 text-center"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto" /></td></tr>
              ) : kycList.length === 0 ? (
                <tr><td colSpan="6" className="py-8 text-center text-gray-500">No pending KYC applications</td></tr>
              ) : (
                kycList.map(user => (
                  <tr key={user.uuid} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                          <UserCheck className="w-5 h-5 text-yellow-700" />
                        </div>
                        <div>
                          <p className="font-medium">{user.name}</p>
                          <p className="text-sm text-gray-500">{user.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">{user.phone}</td>
                    <td className="py-3 px-4">
                      <span className="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{user.role}</span>
                    </td>
                    <td className="py-3 px-4 text-sm text-gray-500">{new Date(user.created_at).toLocaleDateString()}</td>
                    <td className="py-3 px-4">
                      {user.kyc_documents && (
                        <button className="text-blue-600 hover:underline text-sm flex items-center gap-1">
                          <FileText className="w-4 h-4" />
                          View
                        </button>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <button onClick={() => handleApprove(user.uuid)} className="px-3 py-1 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200 flex items-center gap-1">
                          <CheckCircle className="w-4 h-4" />
                          Approve
                        </button>
                        <button onClick={() => handleReject(user.uuid)} className="px-3 py-1 bg-red-100 text-red-800 rounded text-sm hover:bg-red-200 flex items-center gap-1">
                          <XCircle className="w-4 h-4" />
                          Reject
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
