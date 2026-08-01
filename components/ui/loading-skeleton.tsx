export const LoadingSkeleton = ({ className = "h-24 w-full" }: { className?: string }) => {
  return <div className={`animate-pulse rounded-2xl bg-white/5 ${className}`} />;
};
