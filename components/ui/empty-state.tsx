import { GlassCard } from "@/components/ui/glass-card";

export const EmptyState = ({ title, description }: { title: string; description: string }) => {
  return (
    <GlassCard className="text-center py-10">
      <h3 className="text-lg font-medium text-white">{title}</h3>
      <p className="mt-2 text-sm text-zinc-400">{description}</p>
    </GlassCard>
  );
};
