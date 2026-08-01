"use client";

import { useEffect } from "react";
import { usePathname, useRouter } from "next/navigation";
import { Sidebar } from "@/components/layout/sidebar";
import { Topbar } from "@/components/layout/topbar";
import { CommandPalette } from "@/components/layout/command-palette";
import { useAuth } from "@/contexts/auth-context";
import { useCommandPalette } from "@/hooks/use-command-palette";

export const AppShell = ({ children }: { children: React.ReactNode }) => {
  const { isAuthenticated } = useAuth();
  const { open, setOpen } = useCommandPalette();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!isAuthenticated) {
      router.push("/login");
    }
  }, [isAuthenticated, router]);

  if (!isAuthenticated) return null;

  return (
    <div className="min-h-screen bg-black px-4 py-4 text-white">
      <div className="mx-auto flex max-w-[1600px] gap-4">
        <Sidebar />
        <div className="flex-1">
          <Topbar onOpenCommand={() => setOpen(true)} />
          <main key={pathname}>{children}</main>
        </div>
      </div>
      <CommandPalette open={open} setOpen={setOpen} />
    </div>
  );
};
