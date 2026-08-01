import { GlassCard } from "@/components/ui/glass-card";

interface MetricCardProps {
  label: string;
  value: string | number;
  trend?: string;
}

export const MetricCard = ({ label, value, trend }: MetricCardProps) => {
  return (
    <GlassCard>
      <p className="text-xs uppercase tracking-[0.2em] text-zinc-500">{label}</p>
      <p className="mt-2 text-2xl font-semibold tracking-[-0.04em] text-white">{value}</p>
      {trend ? <p className="mt-2 text-sm text-emerald-300">{trend}</p> : null}
    </GlassCard>
  );
};
