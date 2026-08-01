interface ProgressRingProps {
  value: number;
}

export const ProgressRing = ({ value }: ProgressRingProps) => {
  const clamped = Math.max(0, Math.min(100, value));

  return (
    <div className="relative h-24 w-24">
      <svg className="h-24 w-24 -rotate-90" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="42" stroke="rgba(255,255,255,0.2)" strokeWidth="8" fill="none" />
        <circle
          cx="50"
          cy="50"
          r="42"
          stroke="#ccff00"
          strokeWidth="8"
          fill="none"
          strokeDasharray={264}
          strokeDashoffset={264 - (264 * clamped) / 100}
          strokeLinecap="round"
        />
      </svg>
      <span className="absolute inset-0 grid place-items-center text-sm font-medium">{clamped}%</span>
    </div>
  );
};
