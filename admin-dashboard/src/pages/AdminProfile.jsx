import { useState, useEffect } from 'react'
import { User, Mail, Phone, Shield, Activity, Camera, Lock } from 'lucide-react'

export default function AdminProfile() {
  const [profile, setProfile] = useState(null)
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(false)
  const [formData, setFormData] = useState({})
  const [passwordData, setPasswordData] = useState({})
  const [message, setMessage] = useState('')

  useEffect(() => {
    fetchProfile()
  }, [])

  const fetchProfile = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/profile', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setProfile(data.user)
        setFormData({
          name: data.user.name,
          email: data.user.email,
          phone: data.user.phone,
          preferred_language: data.user.preferred_language,
        })
      }
    } catch (err) {
      console.error('Failed to fetch profile:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleUpdate = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/profile', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      })
      if (res.ok) {
        setMessage('Profile updated successfully!')
        setEditing(false)
        fetchProfile()
      }
    } catch (err) {
      setMessage('Failed to update profile')
    }
  }

  const handleChangePassword = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('http://76.13.56.180:8000/api/admin/profile/change-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(passwordData)
      })
      const data = await res.json()
      if (res.ok) {
        setMessage('Password changed successfully!')
        setPasswordData({})
      } else {
        setMessage(data.message || 'Failed to change password')
      }
    } catch (err) {
      setMessage('Network error')
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Admin Profile</h1>
        <p className="text-gray-500 mt-1">Manage your account and security settings</p>
      </div>

      {message && (
        <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
          {message}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Profile Info */}
        <div className="card">
          <div className="flex items-center gap-4 mb-6">
            <div className="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
              {profile?.avatar ? (
                <img src={profile.avatar} alt="" className="w-full h-full rounded-full object-cover" />
              ) : (
                <User className="w-10 h-10 text-green-600" />
              )}
            </div>
            <div>
              <h2 className="text-xl font-bold">{profile?.name}</h2>
              <p className="text-gray-500">{profile?.email}</p>
              <span className="inline-block mt-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                {profile?.role}
              </span>
            </div>
          </div>

          {editing ? (
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({...formData, email: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  type="text"
                  value={formData.phone}
                  onChange={(e) => setFormData({...formData, phone: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Language</label>
                <select
                  value={formData.preferred_language}
                  onChange={(e) => setFormData({...formData, preferred_language: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                >
                  <option value="sw">Swahili</option>
                  <option value="en">English</option>
                  <option value="lg">Luganda</option>
                  <option value="rw">Kinyarwanda</option>
                  <option value="fr">French</option>
                </select>
              </div>
              <div className="flex gap-3">
                <button onClick={handleUpdate} className="btn-primary">Save</button>
                <button onClick={() => setEditing(false)} className="btn-secondary">Cancel</button>
              </div>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <Mail className="w-5 h-5 text-gray-400" />
                <div>
                  <p className="text-sm text-gray-500">Email</p>
                  <p className="font-medium">{profile?.email}</p>
                </div>
              </div>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <Phone className="w-5 h-5 text-gray-400" />
                <div>
                  <p className="text-sm text-gray-500">Phone</p>
                  <p className="font-medium">{profile?.phone}</p>
                </div>
              </div>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <Shield className="w-5 h-5 text-gray-400" />
                <div>
                  <p className="text-sm text-gray-500">Status</p>
                  <p className="font-medium">{profile?.status}</p>
                </div>
              </div>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <Activity className="w-5 h-5 text-gray-400" />
                <div>
                  <p className="text-sm text-gray-500">KYC Status</p>
                  <p className="font-medium">{profile?.kyc_status}</p>
                </div>
              </div>
              <button onClick={() => setEditing(true)} className="btn-primary w-full">Edit Profile</button>
            </div>
          )}
        </div>

        {/* Change Password */}
        <div className="card">
          <h2 className="text-lg font-semibold mb-4 flex items-center gap-2">
            <Lock className="w-5 h-5" />
            Change Password
          </h2>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <input
                type="password"
                value={passwordData.current_password || ''}
                onChange={(e) => setPasswordData({...passwordData, current_password: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input
                type="password"
                value={passwordData.new_password || ''}
                onChange={(e) => setPasswordData({...passwordData, new_password: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <input
                type="password"
                value={passwordData.new_password_confirmation || ''}
                onChange={(e) => setPasswordData({...passwordData, new_password_confirmation: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
            </div>
            <button onClick={handleChangePassword} className="btn-primary w-full">Change Password</button>
          </div>
        </div>
      </div>

      {/* Permissions */}
      <div className="card">
        <h2 className="text-lg font-semibold mb-4">Your Permissions</h2>
        <div className="flex flex-wrap gap-2">
          {profile?.permissions?.map((perm) => (
            <span key={perm} className="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm">
              {perm.replace(/_/g, ' ')}
            </span>
          ))}
        </div>
      </div>
    </div>
  )
}
