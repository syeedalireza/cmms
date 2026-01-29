import LoginForm from '../components/LoginForm'

export default function LoginPage() {
  return (
    <div className="w-full max-w-md space-y-8 rounded-lg bg-white p-8 shadow-xl">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-gray-900">Zagros CMMS</h1>
        <p className="mt-2 text-gray-600">Sign in to your account</p>
      </div>

      <LoginForm />
    </div>
  )
}
