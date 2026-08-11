import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ProtectedRoute } from './App'

describe('ProtectedRoute', () => {
  beforeEach(() => { global.fetch = vi.fn() })

  it('renders admin content after cookie authentication', async () => {
    fetch.mockResolvedValue({ ok: true, json: async () => ({ user: { role: 'admin', name: 'Admin' } }) })
    render(<MemoryRouter initialEntries={['/']}><Routes><Route path="/" element={<ProtectedRoute><div>Protected dashboard</div></ProtectedRoute>} /></Routes></MemoryRouter>)
    expect(await screen.findByText('Protected dashboard')).toBeInTheDocument()
  })

  it('redirects unauthenticated users to login', async () => {
    fetch.mockResolvedValue({ ok: false, json: async () => ({}) })
    render(<MemoryRouter initialEntries={['/']}><Routes><Route path="/" element={<ProtectedRoute><div>Protected dashboard</div></ProtectedRoute>} /><Route path="/login" element={<div>Login route</div>} /></Routes></MemoryRouter>)
    expect(await screen.findByText('Login route')).toBeInTheDocument()
  })
})
