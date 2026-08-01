"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { BarChart3, Bot, Boxes, LayoutDashboard, Mic, Settings, UploadCloud, UserRound, Bell } from "lucide-react";
import { cn } from "@/lib/cn";

const links = [
  { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { href: "/inventory", label: "Inventory", icon: Boxes },
  { href: "/upload", label: "Upload CSV", icon: UploadCloud },
  { href: "/chat", label: "AI Chat", icon: Bot },
  { href: "/voice", label: "Voice", icon: Mic },
  { href: "/business-profile", label: "Business Profile", icon: UserRound },
  { href: "/notifications", label: "Notifications", icon: Bell },
  { href: "/analytics", label: "Analytics", icon: BarChart3 },
  { href: "/settings", label: "Settings", icon: Settings },
];

export const Sidebar = () => {
  const pathname = usePathname();

  return (
    <aside className="hidden h-[calc(100vh-32px)] w-72 shrink-0 rounded-[28px] border border-white/10 bg-white/[0.03] p-4 backdrop-blur-2xl lg:block">
      <h1 className="px-3 pt-2 text-xl font-semibold tracking-[-0.04em] text-lime-300">QuantStock</h1>
      <p className="px-3 text-xs uppercase tracking-[0.2em] text-zinc-500">by Quant AI</p>
      <nav className="mt-6 space-y-1">
        {links.map((link) => {
          const Icon = link.icon;
          const active = pathname === link.href;
          return (
            <Link
              key={link.href}
              href={link.href}
              className={cn(
                "flex items-center gap-3 rounded-2xl px-3 py-2 text-sm transition",
                active
                  ? "border border-lime-300/40 bg-lime-300/10 text-lime-200"
                  : "text-zinc-300 hover:bg-white/5 hover:text-white",
              )}
            >
              <Icon className="h-4 w-4" />
              {link.label}
            </Link>
          );
        })}
      </nav>
    </aside>
  );
};
