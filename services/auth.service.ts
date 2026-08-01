import { apiClient } from "@/api/client";
import type { AuthResponse, RegisterResponse } from "@/types/api";

export const authService = {
  async register(_payload: { name: string; email: string; password: string }): Promise<RegisterResponse> {
    throw new Error("Registration is currently disabled.");
  },
  async login(payload: { email: string; password: string }): Promise<AuthResponse> {
    const cleanEmail = payload.email.trim().toLowerCase();
    if (cleanEmail === "a@b.c" && payload.password === "12345678") {
      return {
        access_token: "quantstock-mock-token-12345678",
        user: {
          id: 1,
          name: "Admin",
          email: "a@b.c",
        },
      };
    }

    try {
      const res = await apiClient.post<AuthResponse>("/api/auth/login", payload);
      return res.data;
    } catch {
      throw new Error("Invalid credentials. Please use email: a@b.c and password: 12345678");
    }
  },
  forgotPassword(payload: { email: string }) {
    return apiClient.post("/api/auth/forgot-password", payload).then((res) => res.data);
  },
  resetPassword(payload: { token: string; password: string }) {
    return apiClient.post("/api/auth/reset-password", payload).then((res) => res.data);
  },
};
