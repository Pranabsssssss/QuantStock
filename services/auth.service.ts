import { apiClient } from "@/api/client";
import type { AuthResponse, RegisterResponse } from "@/types/api";

export const authService = {
  register(payload: { name: string; email: string; password: string }) {
    return apiClient.post<RegisterResponse>("/api/auth/register", payload).then((res) => res.data);
  },
  login(payload: { email: string; password: string }) {
    return apiClient.post<AuthResponse>("/api/auth/login", payload).then((res) => res.data);
  },
  forgotPassword(payload: { email: string }) {
    return apiClient.post("/api/auth/forgot-password", payload).then((res) => res.data);
  },
  resetPassword(payload: { token: string; password: string }) {
    return apiClient.post("/api/auth/reset-password", payload).then((res) => res.data);
  },
};
