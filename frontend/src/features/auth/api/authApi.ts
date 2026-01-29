import { apiClient } from '@/lib/api-client'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  email: string
  password: string
  firstName: string
  lastName: string
  phone?: string
}

export interface AuthResponse {
  token: string
  user: {
    id: string
    email: string
    firstName: string
    lastName: string
    fullName: string
    roles: string[]
  }
}

export const authApi = {
  login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const response = await apiClient.post('/auth/login', credentials)
    return response.data
  },

  register: async (data: RegisterData): Promise<{ userId: string; message: string }> => {
    const response = await apiClient.post('/auth/register', data)
    return response.data
  },

  me: async (): Promise<AuthResponse['user']> => {
    const response = await apiClient.get('/auth/me')
    return response.data
  },
}
