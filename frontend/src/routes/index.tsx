import { Routes, Route, Navigate } from 'react-router-dom'
import { Suspense, lazy } from 'react'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import MainLayout from '@/components/layouts/MainLayout'
import AuthLayout from '@/components/layouts/AuthLayout'

// Lazy load pages
const LoginPage = lazy(() => import('@/features/auth/pages/LoginPage'))
const DashboardPage = lazy(() => import('@/features/dashboard/pages/DashboardPage'))

// Placeholder pages (will be implemented later)
const NotFoundPage = () => <div>404 - Page Not Found</div>

function AppRoutes() {
  return (
    <Suspense fallback={<LoadingSpinner />}>
      <Routes>
        {/* Auth routes */}
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
        </Route>

        {/* Protected routes */}
        <Route element={<MainLayout />}>
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard" element={<DashboardPage />} />
          
          {/* Will be implemented in later todos */}
          {/* <Route path="/assets/*" element={<AssetRoutes />} /> */}
          {/* <Route path="/work-orders/*" element={<WorkOrderRoutes />} /> */}
          {/* <Route path="/maintenance/*" element={<MaintenanceRoutes />} /> */}
          {/* <Route path="/inventory/*" element={<InventoryRoutes />} /> */}
        </Route>

        {/* 404 */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  )
}

export default AppRoutes
