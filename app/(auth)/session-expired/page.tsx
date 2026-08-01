import Link from "next/link";
import { AuthCard } from "@/components/ui/auth-card";

export default function SessionExpiredPage() {
  return (
    <AuthCard title="Session expired" subtitle="Please sign in again to continue.">
      <Link
        href="/login"
        className="inline-flex w-full items-center justify-center rounded-2xl border border-lime-300/40 bg-lime-300/10 px-4 py-2 text-sm text-lime-200"
      >
        Back to login
      </Link>
    </AuthCard>
  );
}
