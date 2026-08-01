"use client";

import Link from "next/link";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";

export default function RegisterPage() {
  return (
    <AuthCard title="Registration Disabled" subtitle="New user registration is disabled on this system.">
      <div className="space-y-4 text-center">
        <p className="text-sm text-zinc-400">
          Please log in using the administrator credentials.
        </p>
        <div className="rounded-xl border border-white/10 bg-black/40 p-4 text-xs text-zinc-300">
          <div>Email: <span className="font-mono text-emerald-400">a@b.c</span></div>
          <div className="mt-1">Password: <span className="font-mono text-emerald-400">12345678</span></div>
        </div>
        <Link href="/login" className="block w-full">
          <GradientButton type="button" className="w-full">
            Go to Login
          </GradientButton>
        </Link>
      </div>
    </AuthCard>
  );
}
