import { cn } from "@/lib/cn";

export const GlassCard = ({ className, children }: { className?: string; children: React.ReactNode }) => {
  return (
    <section
      className={cn(
        "rounded-[24px] border border-white/10 bg-white/[0.03] p-5 shadow-[0_20px_80px_rgba(0,0,0,0.5)] backdrop-blur-xl",
        className,
      )}
    >
      {children}
    </section>
  );
};
