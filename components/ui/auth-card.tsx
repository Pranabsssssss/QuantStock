import { GlassCard } from "@/components/ui/glass-card";

export const AuthCard = ({ title, subtitle, children }: { title: string; subtitle: string; children: React.ReactNode }) => {
  return (
    <GlassCard className="w-full">
      <h1 className="text-2xl font-semibold tracking-[-0.05em] text-white">{title}</h1>
      <p className="mt-1 text-sm text-zinc-400">{subtitle}</p>
      <div className="mt-6 space-y-4">{children}</div>
    </GlassCard>
  );
};
