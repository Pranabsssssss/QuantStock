"use client";

import { Bell, Search } from "lucide-react";
import { useAuth } from "@/contexts/auth-context";
import { useOnlineStatus } from "@/hooks/use-online-status";

export const Topbar = ({ onOpenCommand }: { onOpenCommand: () => void }) => {
  const { user, logout } = useAuth();
  const isOnline = useOnlineStatus();

  return (
    <header className="mb-6 flex items-center justify-between gap-4 rounded-3xl border border-white/10 bg-white/[0.03] px-4 py-3 backdrop-blur-2xl">
      <button
        onClick={onOpenCommand}
        className="flex min-w-72 items-center gap-2 rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-zinc-400"
      >
        <Search className="h-4 w-4" />
        Search anything...
        <span className="ml-auto rounded-md border border-white/10 px-2 py-0.5 text-[10px]">⌘K</span>
      </button>
      <div className="flex items-center gap-3">
        <span
          className={`rounded-full border px-3 py-1 text-xs ${
            isOnline ? "border-emerald-400/40 text-emerald-300" : "border-red-400/40 text-red-300"
          }`}
        >
          {isOnline ? "Online" : "Offline"}
        </span>
        <Bell className="h-4 w-4 text-zinc-300" />
        <div className="text-right text-sm">
          <p className="text-white">{user?.name ?? "Authenticated User"}</p>
          <button onClick={logout} className="text-xs text-zinc-400 hover:text-zinc-200">
            Logout
          </button>
        </div>
      </div>
    </header>
  );
};
