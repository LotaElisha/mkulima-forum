import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import Login from './Login'

const navigate = vi.fn()
vi.mock('react-router-dom', async () => ({
  ...await vi.importActual('react-router-dom'),
  useNavigate: () => navigate,
}))

describe('admin login', () => {
  beforeEach(() => {
    navigate.mockReset()
    global.fetch = vi.fn()
  })

  it('submits credentials and navigates after cookie login', async () => {
    fetch.mockResolvedValue({ ok: true, json: async () => ({ user: { role: 'admin' } }) })
    render(<MemoryRouter><Login /></MemoryRouter>)

    fireEvent.change(screen.getByLabelText('Email Address'), { target: { value: 'admin@example.test' } })
    fireEvent.change(screen.getByLabelText('Password'), { target: { value: 'strong-password' } })
    fireEvent.click(screen.getByRole('button', { name: 'Sign In' }))

    await waitFor(() => expect(navigate).toHaveBeenCalledWith('/'))
    expect(fetch).toHaveBeenCalledWith('/api/auth/login', expect.objectContaining({ method: 'POST' }))
  })

  it('shows an authentication error without navigating', async () => {
    fetch.mockResolvedValue({ ok: false, json: async () => ({ message: 'Invalid credentials.' }) })
    render(<MemoryRouter><Login /></MemoryRouter>)
    fireEvent.change(screen.getByLabelText('Email Address'), { target: { value: 'bad@example.test' } })
    fireEvent.change(screen.getByLabelText('Password'), { target: { value: 'wrong' } })
    fireEvent.click(screen.getByRole('button', { name: 'Sign In' }))

    expect(await screen.findByText('Invalid credentials.')).toBeInTheDocument()
    expect(navigate).not.toHaveBeenCalled()
  })
})
