"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { authStorage } from "@/lib/auth-storage";
import type { User } from "@/types/api";

interface AuthContextValue {
  token: string;
  user: User | null;
  isAuthenticated: boolean;
  login: (token: string, user: User) => void;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  const [token, setToken] = useState("");
  const [user, setUser] = useState<User | null>(null);
  const router = useRouter();

  useEffect(() => {
    setToken(authStorage.getToken());
  }, []);

  const login = useCallback((nextToken: string, nextUser: User) => {
    authStorage.setToken(nextToken);
    setToken(nextToken);
    setUser(nextUser);
  }, []);

  const logout = useCallback(() => {
    authStorage.clearToken();
    setToken("");
    setUser(null);
    router.push("/session-expired");
  }, [router]);

  useEffect(() => {
    const handler = () => logout();
    window.addEventListener("quantstock:unauthorized", handler);
    return () => window.removeEventListener("quantstock:unauthorized", handler);
  }, [logout]);

  const value = useMemo(
    () => ({
      token,
      user,
      isAuthenticated: Boolean(token),
      login,
      logout,
    }),
    [login, logout, token, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
};
