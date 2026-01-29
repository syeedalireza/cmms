import { Outlet, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/stores/authStore'

export default function MainLayout() {
  const user = useAuthStore((state) => state.user)

  if (!user) {
    return <Navigate to="/login" replace />
  }

  return (
    <div className="min-h-screen bg-background">
      {/* TODO: Add Sidebar/Header */}
      <main className="p-6">
        <Outlet />
      </main>
    </div>
  )
}
