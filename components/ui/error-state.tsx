import { GlassCard } from "@/components/ui/glass-card";

export const ErrorState = ({ message, onRetry }: { message: string; onRetry?: () => void }) => {
  return (
    <GlassCard className="text-center py-10">
      <h3 className="text-lg font-medium text-red-300">Request failed</h3>
      <p className="mt-2 text-sm text-zinc-400">{message}</p>
      {onRetry ? (
        <button
          onClick={onRetry}
          className="mt-4 rounded-xl border border-white/20 px-4 py-2 text-sm text-white hover:bg-white/10"
        >
          Retry
        </button>
      ) : null}
    </GlassCard>
  );
};
