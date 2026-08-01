import { cn } from "@/lib/cn";

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  loading?: boolean;
}

export const GradientButton = ({ className, loading, children, ...props }: ButtonProps) => {
  return (
    <button
      className={cn(
        "inline-flex items-center justify-center rounded-2xl border border-lime-300/30 bg-gradient-to-r from-lime-300/90 to-emerald-400/90 px-4 py-2 text-sm font-semibold text-black transition hover:scale-[1.02] hover:shadow-[0_0_30px_rgba(204,255,0,0.25)] disabled:cursor-not-allowed disabled:opacity-70",
        className,
      )}
      disabled={loading || props.disabled}
      {...props}
    >
      {loading ? "Please wait..." : children}
    </button>
  );
};
