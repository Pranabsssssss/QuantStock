"use client";

import Link from "next/link";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";

export default function RegisterPage() {
  return (
    <AuthCard title="Registration Disabled" subtitle="New user registration is disabled on this system.">
      <div className="space-y-4 text-center">
        <p className="text-sm text-zinc-400">
          Please log in using your account credentials.
        </p>
        <Link href="/login" className="block w-full">
          <GradientButton type="button" className="w-full">
            Go to Login
          </GradientButton>
        </Link>
      </div>
    </AuthCard>
  );
}
