import { createContext, useContext } from 'react'

/** Carries the verified admin user (from /api/auth/me) down the tree. */
export const AuthContext = createContext(null)
export const useAuthUser = () => useContext(AuthContext)

/**
 * Gates a route to specific roles. Renders an access notice (not a redirect)
 * so admins understand why a page is unavailable.
 */
export function RequireRole({ roles, children }) {
  const user = useAuthUser()
  if (!user || !roles.includes(user.role)) {
    return (
      <div className="min-h-[50vh] flex flex-col items-center justify-center text-center p-8">
        <h2 className="text-lg font-semibold text-gray-800 mb-2">Huna ruhusa / Access restricted</h2>
        <p className="text-sm text-gray-500 max-w-md">
          This section requires one of the following roles: {roles.join(', ')}.
        </p>
      </div>
    )
  }
  return children
}
